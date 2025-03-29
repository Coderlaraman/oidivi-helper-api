<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'is_active',
        'sort_order',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
        'deleted_at' => 'datetime',
    ];

    // Appends para incluir rutas completas
    protected $appends = ['path', 'full_path'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            // Garantizar slugs únicos
            $originalSlug = Str::slug($category->name);
            $slug = $originalSlug;
            $count = 1;
            
            while (static::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }
            
            $category->slug = $slug;
            
            // Asignar un orden por defecto si no se especifica
            if (!$category->sort_order) {
                $maxOrder = static::where('parent_id', $category->parent_id)->max('sort_order') ?? 0;
                $category->sort_order = $maxOrder + 10;
            }
        });
        
        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                // Similar lógica para garantizar slugs únicos en actualización
                $originalSlug = Str::slug($category->name);
                $slug = $originalSlug;
                $count = 1;
                
                while (static::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                    $slug = "{$originalSlug}-{$count}";
                    $count++;
                }
                
                $category->slug = $slug;
            }
            
            // Evitar bucles infinitos en jerarquía
            if ($category->isDirty('parent_id') && $category->parent_id) {
                if (!$category->isValidParent($category->parent_id)) {
                    throw new \Exception('No se puede establecer un descendiente como categoría padre.');
                }
            }
        });
    }

    // Método para validar si un parent_id es válido (no es descendiente)
    public function isValidParent($parentId): bool
    {
        if ($parentId == $this->id) {
            return false;
        }
        
        $parent = self::find($parentId);
        while ($parent && $parent->parent_id) {
            if ($parent->parent_id == $this->id) {
                return false;
            }
            $parent = $parent->parent;
        }
        
        return true;
    }

    // Obtener el path completo (ancestros)
    public function getPathAttribute(): array
    {
        $path = [];
        $category = $this;
        
        while ($category->parent_id) {
            $category = $category->parent;
            array_unshift($path, [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug
            ]);
        }
        
        return $path;
    }
    
    // Obtener el path completo como string (para URLs o breadcrumbs)
    public function getFullPathAttribute(): string
    {
        $names = array_column($this->path, 'name');
        $names[] = $this->name;
        return implode(' > ', $names);
    }

    // Relaciones
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Relación recursiva para obtener todos los descendientes
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeParentOnly(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) return $query;
        
        $search = '%' . trim($search) . '%';
        return $query->where(function($query) use ($search) {
            $query->where('name', 'LIKE', $search)
                  ->orWhere('description', 'LIKE', $search)
                  ->orWhere('slug', 'LIKE', $search);
        });
    }

    /**
     * Verifica si la categoría tiene entidades relacionadas que impiden su eliminación
     *
     * @return bool
     */
    public function hasRelatedEntities(): bool
    {
        // Usa consultas Eloquent optimizadas para verificar relaciones con una sola consulta
        return $this->children()->exists() ||
               $this->skills()->exists() ||
               $this->serviceRequests()->exists();
    }
    
    /**
     * Obtiene categorías raíz (sin padre)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }
    
    /**
     * Aplica filtros comunes a la consulta
     *
     * @param Builder $query
     * @param bool|null $active Filtrar por estado activo
     * @param bool|null $withTrashed Incluir elementos eliminados
     * @return Builder
     */
    public function scopeApplyFilters(Builder $query, ?bool $active = null, ?bool $withTrashed = false): Builder
    {
        if ($active !== null) {
            $query->where('is_active', $active);
        }
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query;
    }
    
    /**
     * Obtiene la jerarquía completa de la categoría (padres e hijos)
     *
     * @param bool $activeOnly Incluir solo elementos activos
     * @return array
     */
    public function getHierarchy(bool $activeOnly = false): array
    {
        // Obtener ancestros
        $ancestors = collect($this->path);
        
        // Obtener la categoría actual
        $current = collect([[
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'is_current' => true
        ]]);
        
        // Obtener descendientes
        $query = $this->allChildren();
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        $descendants = $query->get()->map(function($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'parent_id' => $category->parent_id,
                'level' => count($category->path) + 1
            ];
        });
        
        // Combinar todo
        return [
            'ancestors' => $ancestors,
            'current' => $current->first(),
            'descendants' => $descendants
        ];
    }
    
    /**
     * Obtiene categorías relacionadas (hermanas/del mismo nivel)
     *
     * @param bool $activeOnly Incluir solo elementos activos
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiblings(bool $activeOnly = false)
    {
        $query = self::where('parent_id', $this->parent_id)
                    ->where('id', '!=', $this->id);
                    
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->ordered()->get();
    }
    
    /**
     * Comprueba si la categoría contiene otra categoría como descendiente
     *
     * @param int $categoryId
     * @return bool
     */
    public function containsCategory(int $categoryId): bool
    {
        if ($this->id === $categoryId) {
            return true;
        }
        
        $descendants = $this->allChildren()->pluck('id')->toArray();
        return in_array($categoryId, $descendants);
    }
    
    /**
     * Actualiza el orden de la categoría y reordena las categorías relacionadas
     *
     * @param int $newOrder
     * @return bool
     */
    public function updateOrder(int $newOrder): bool
    {
        if ($newOrder === $this->sort_order) {
            return true;
        }
        
        $this->sort_order = $newOrder;
        return $this->save();
    }

    /**
     * Restaura esta categoría y opcionalmente sus padres y/o hijos
     *
     * @param bool $restoreParent Restaurar también el padre si está eliminado
     * @param bool $restoreChildren Restaurar también los hijos si están eliminados
     * @return bool
     * @throws \Exception Si se solicita restaurar padre pero éste no se encuentra
     */
    public function restoreWithRelations(bool $restoreParent = false, bool $restoreChildren = false): bool
    {
        // Restaurar padre si se solicita
        if ($restoreParent && $this->parent_id) {
            $parent = self::withTrashed()->find($this->parent_id);
            if ($parent && $parent->trashed()) {
                $parent->restore();
            } elseif (!$parent) {
                throw new \Exception("No se encontró la categoría padre (ID: {$this->parent_id})");
            }
        }
        
        // Restaurar esta categoría
        $restored = $this->restore();
        
        // Restaurar hijos si se solicita
        if ($restoreChildren) {
            $this->restoreAllChildren();
        }
        
        return $restored;
    }
    
    /**
     * Restaura todos los hijos eliminados recursivamente
     *
     * @return int Número de categorías restauradas
     */
    public function restoreAllChildren(): int
    {
        $restoredCount = 0;
        
        // Obtener todos los hijos eliminados
        $deletedChildren = self::withTrashed()
            ->where('parent_id', $this->id)
            ->whereNotNull('deleted_at')
            ->get();
        
        foreach ($deletedChildren as $child) {
            // Restaurar este hijo
            $child->restore();
            $restoredCount++;
            
            // Restaurar recursivamente sus hijos
            $restoredCount += $child->restoreAllChildren();
        }
        
        return $restoredCount;
    }
    
    /**
     * Obtiene todos los IDs de categorías hijas eliminadas recursivamente
     *
     * @return array
     */
    public function getDeletedChildrenIds(): array
    {
        $childrenIds = [];
        
        // Obtener hijos directos eliminados
        $deletedChildren = self::withTrashed()
            ->where('parent_id', $this->id)
            ->whereNotNull('deleted_at')
            ->get(['id']);
        
        foreach ($deletedChildren as $child) {
            $childrenIds[] = $child->id;
            
            // Categoría temporal para llamar al método en cada hijo
            $childObj = self::withTrashed()->find($child->id);
            if ($childObj) {
                $nestedIds = $childObj->getDeletedChildrenIds();
                $childrenIds = array_merge($childrenIds, $nestedIds);
            }
        }
        
        return $childrenIds;
    }
    
    /**
     * Verifica si alguno de los padres de la categoría está eliminado
     *
     * @return bool|int Retorna false si no hay padres eliminados, o el ID del padre eliminado
     */
    public function hasDeletedParent()
    {
        if (!$this->parent_id) {
            return false;
        }
        
        $parent = self::withTrashed()->find($this->parent_id);
        
        // Si el padre inmediato está eliminado
        if ($parent && $parent->trashed()) {
            return $parent->id;
        }
        
        // Verificar recursivamente hacia arriba
        if ($parent) {
            return $parent->hasDeletedParent();
        }
        
        return false;
    }
    
    /**
     * Método seguro para eliminar una categoría, que verifica relaciones
     * 
     * @param bool $force Realizar eliminación permanente
     * @return bool|string True si se eliminó correctamente, string con mensaje de error si falla
     */
    public function safeDelete(bool $force = false)
    {
        if ($force) {
            // Verificar si tiene entidades relacionadas
            if ($this->hasRelatedEntities()) {
                return "No se puede eliminar permanentemente una categoría con subcategorías, habilidades o solicitudes de servicio";
            }
            
            $this->forceDelete();
            return true;
        }
        
        // Soft delete normal
        $this->delete();
        return true;
    }

    /**
     * Check if this category is a parent of another category.
     */
    public function isParentOf(int $categoryId): bool
    {
        return $this->children()->where('id', $categoryId)->exists();
    }

    /**
     * Check if this category has active children.
     */
    public function hasActiveChildren(): bool
    {
        return $this->children()->where('is_active', true)->exists();
    }

    /**
     * Get the number of children categories.
     */
    public function getChildrenCountAttribute(): int
    {
        return $this->children()->count();
    }

    /**
     * Get the number of skills associated with the category.
     */
    public function getSkillsCountAttribute(): int
    {
        return $this->skills()->count();
    }

    /**
     * Get the number of service requests associated with the category.
     */
    public function getServiceRequestsCountAttribute(): int
    {
        return $this->serviceRequests()->count();
    }
}
