<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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
        'icon',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    // Appends para incluir conteos
    protected $appends = ['skills_count', 'service_requests_count'];

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
                $maxOrder = static::max('sort_order') ?? 0;
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
        });
    }

    public function serviceRequests(): MorphToMany
    {
        return $this->morphedByMany(ServiceRequest::class, 'categorizable')
            ->whereNull('service_requests.deleted_at');
    }

    public function skills(): MorphToMany
    {
        return $this->morphedByMany(Skill::class, 'categorizable')
            ->whereNull('skills.deleted_at');
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
     */
    public function hasRelatedEntities(): bool
    {
        return $this->serviceRequests()->exists() || $this->skills()->exists();
    }
    
    /**
     * Aplica filtros comunes a la consulta
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
