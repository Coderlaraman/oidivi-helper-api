<?php

namespace App\Http\Controllers\Api\V1\Client\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ClientCategoryResource;
use App\Http\Requests\Client\Category\ListCategoryRequest;
use App\Http\Requests\Client\Category\StoreCategoryRequest;
use App\Http\Requests\Client\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class ClientCategoryController extends Controller
{
    use ApiResponseTrait;

    private const CACHE_TTL = 3600; // 1 hora
    private const CACHE_TAG = 'categories';
    
    // Lista de parámetros relevantes para la caché
    private const CACHE_PARAMS = [
        'search', 'parent_only', 'active_only', 'with_children',
        'with_skills', 'with_service_requests', 'sort_by',
        'sort_direction', 'per_page', 'page', 'show_deleted'
    ];

    /**
     * List all categories with comprehensive filtering, sorting and pagination.
     */
    public function index(ListCategoryRequest $request): JsonResponse
    {
        try {
            // Generar clave de caché normalizada
            $cacheKey = $this->generateCacheKey('list', null, $request);
            
            try {
                $categories = $this->getDataWithCache(
                    $request->_no_cache, 
                    $cacheKey,
                    fn() => $this->fetchCategories($request)
                );
                
                // Verificación de seguridad adicional
                if (!$categories) {
                    return $this->errorResponse(
                        'Error al obtener listado de categorías',
                        500,
                        ['error' => 'La consulta no devolvió resultados válidos']
                    );
                }
                
                return $this->successResponse(
                    ClientCategoryResource::collection($categories)
                        ->additional([
                            'meta' => [
                                'total' => $categories->total(),
                                'current_page' => $categories->currentPage(),
                                'last_page' => $categories->lastPage(),
                                'per_page' => $categories->perPage(),
                                'from' => $categories->firstItem() ?? 0,
                                'to' => $categories->lastItem() ?? 0,
                                'filters' => $this->sanitizeFilters($request),
                                'sort' => [
                                    'by' => $request->sort_by ?? 'sort_order',
                                    'direction' => $request->sort_direction ?? 'asc'
                                ]
                            ]
                        ]),
                    'Listado de categorías obtenido correctamente'
                );
            } catch (\Exception $e) {
                // Error específico de obtención de datos
                Log::error("Error específico en index de categorías: " . $e->getMessage());
                return $this->errorResponse(
                    'Error al procesar listado de categorías',
                    500,
                    ['error' => $e->getMessage()]
                );
            }
        } catch (\Exception $e) {
            // Error general
            return $this->handleException($e, 'Error al obtener categorías');
        }
    }

    /**
     * Get a specific category with its relationships.
     */
    public function show(Category $category, ListCategoryRequest $request): JsonResponse
    {
        try {
            // Verificación adicional de seguridad
            if (!$category || !$category->id) {
                return $this->errorResponse(
                    'Categoría no encontrada',
                    404,
                    ['error' => 'La categoría solicitada no existe o ha sido eliminada.']
                );
            }
            
            // Generar clave de caché normalizada
            $cacheKey = $this->generateCacheKey('single', $category->id, $request);
            
            try {
                // Garantizar que la categoría siempre tenga las relaciones necesarias
                $categoryWithRelations = $this->getDataWithCache(
                    $request->_no_cache,
                    $cacheKey,
                    fn() => $this->fetchCategory($category, $request)
                );
                
                return $this->successResponse(
                    new ClientCategoryResource($categoryWithRelations),
                    'Categoría obtenida correctamente'
                );
            } catch (\Exception $e) {
                // Error específico de caché o relaciones
                Log::error("Error al obtener categoría específica: " . $e->getMessage());
                return $this->errorResponse(
                    'Error al procesar la categoría',
                    500,
                    ['error' => $e->getMessage()]
                );
            }
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener categoría');
        }
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = Category::create($request->validated());
            
            // Limpiar caché específica
            $this->clearCategoryCache($category->id);
            
            // Registrar información de auditoría
            Log::info("Categoría creada: ID {$category->id}, por usuario ID: " . auth()->id(), [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'user_id' => auth()->id()
            ]);

            return $this->successResponse(
                new ClientCategoryResource($category),
                'Categoría creada correctamente',
                201
            );
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al crear categoría');
        }
    }

    /**
     * Update an existing category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        try {
            $originalData = $category->toArray();
            $category->update($request->validated());
            
            // Limpiar caché específica
            $this->clearCategoryCache($category->id);
            
            // Si es una categoría con padre, limpiar también la caché del padre
            if ($category->parent_id) {
                $this->clearCategoryCache($category->parent_id);
            }
            
            // Registrar información de auditoría
            Log::info("Categoría actualizada: ID {$category->id}, por usuario ID: " . auth()->id(), [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'changes' => array_diff_assoc($category->toArray(), $originalData),
                'user_id' => auth()->id()
            ]);

            return $this->successResponse(
                new ClientCategoryResource($category),
                'Categoría actualizada correctamente'
            );
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al actualizar categoría');
        }
    }

    /**
     * Delete a category (soft delete or force delete).
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            $isForceDelete = request()->input('force', false);
            $categoryId = $category->id;
            $parentId = $category->parent_id;
            $categoryName = $category->name;
            
            if ($isForceDelete) {
                // Exigir confirmación explícita para eliminación permanente
                if (!request()->has('confirm') || request()->input('confirm') !== true) {
                    return $this->errorResponse(
                        'Confirmación requerida',
                        422,
                        ['message' => 'Debes confirmar la eliminación permanente añadiendo el parámetro "confirm=true"']
                    );
                }
                
                if ($category->hasRelatedEntities()) {
                    return $this->errorResponse(
                        'La categoría tiene elementos relacionados',
                        422,
                        ['message' => 'No se puede eliminar permanentemente una categoría con subcategorías, habilidades o solicitudes de servicio']
                    );
                }
                
                $category->forceDelete();
                $actionType = 'eliminada permanentemente';
            } else {
                $category->delete();
                $actionType = 'eliminada (soft delete)';
            }
            
            // Limpiar caché específica
            $this->clearCategoryCache($categoryId);
            
            // Si es una categoría con padre, limpiar también la caché del padre
            if ($parentId) {
                $this->clearCategoryCache($parentId);
            }
            
            // Registrar información de auditoría
            Log::info("Categoría {$actionType}: ID {$categoryId}, por usuario ID: " . auth()->id(), [
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'force_delete' => $isForceDelete,
                'user_id' => auth()->id()
            ]);

            return $this->successResponse(
                null,
                'Categoría eliminada correctamente'
            );
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al eliminar categoría');
        }
    }
    
    /**
     * Restore a soft-deleted category.
     */
    public function restore($id): JsonResponse
    {
        try {
            $category = Category::withTrashed()->findOrFail($id);
            
            // Verificar si el padre está eliminado
            if ($category->parent_id) {
                $parent = Category::withTrashed()->find($category->parent_id);
                if ($parent && $parent->trashed()) {
                    // Opciones: restaurar padre también o advertir
                    if (request()->input('restore_parent', false)) {
                        // Restaurar padre primero
                        $parent->restore();
                        Log::info("Categoría padre restaurada automáticamente: ID {$parent->id}, por usuario ID: " . auth()->id());
                        $this->clearCategoryCache($parent->id);
                    } else {
                        // Advertir sobre padre eliminado
                        return $this->errorResponse(
                            'Categoría padre eliminada',
                            422,
                            ['message' => 'La categoría padre está eliminada. Restaura primero el padre o usa el parámetro "restore_parent=true".']
                        );
                    }
                }
            }
            
            // Restaurar la categoría
            $category->restore();
            
            // Opcionalmente restaurar descendientes
            if (request()->input('restore_children', false)) {
                // Obtener IDs de todas las subcategorías eliminadas
                $childrenIds = $this->getAllDeletedChildrenIds($category->id);
                if (!empty($childrenIds)) {
                    Category::withTrashed()->whereIn('id', $childrenIds)->restore();
                    Log::info("Subcategorías restauradas automáticamente: " . count($childrenIds) . " categorías, por usuario ID: " . auth()->id(), [
                        'children_ids' => $childrenIds
                    ]);
                }
            }
            
            // Limpiar caché específica
            $this->clearCategoryCache($category->id);
            
            // Si es una categoría con padre, limpiar también la caché del padre
            if ($category->parent_id) {
                $this->clearCategoryCache($category->parent_id);
            }
            
            // Registrar información de auditoría
            Log::info("Categoría restaurada: ID {$category->id}, por usuario ID: " . auth()->id(), [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'user_id' => auth()->id()
            ]);
            
            return $this->successResponse(
                new ClientCategoryResource($category),
                'Categoría restaurada correctamente'
            );
        } catch (Exception $e) {
            return $this->handleException($e, 'Error al restaurar categoría', 404);
        }
    }
    
    /**
     * Helper method to clear specific category cache.
     */
    private function clearCategoryCache(int $categoryId): bool
    {
        try {
            // Limpiar caché específica de la categoría (usando el prefijo)
            Cache::tags([self::CACHE_TAG])->forget("categories:single:{$categoryId}:*");
            
            // Limpiar caché de listados que podrían contener la categoría
            Cache::tags([self::CACHE_TAG])->forget("categories:list:*");
            
            return true;
        } catch (Exception $e) {
            Log::warning("Error al limpiar caché de categoría ID {$categoryId}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate a normalized cache key based on relevant request parameters
     * 
     * @param string $type Type of request (list, single, etc)
     * @param int|null $id ID of the category (for single requests)
     * @param \Illuminate\Http\Request $request The request object
     * @return string Normalized cache key
     */
    private function generateCacheKey(string $type, ?int $id = null, $request): string
    {
        // Base de la clave
        $base = "categories:{$type}:";
        
        // Añadir ID si existe
        if ($id !== null) {
            $base .= "{$id}:";
        }
        
        // Extraer solo los parámetros relevantes
        $params = array_filter($request->only(self::CACHE_PARAMS));
        
        // Ordenar por clave para asegurar consistencia
        ksort($params);
        
        // Añadir ID de usuario autenticado para seguridad
        $params['user_id'] = auth()->id() ?? 'guest';
        
        // Generar hash de los parámetros serializados
        $paramsHash = md5(json_encode($params));
        
        return $base . $paramsHash;
    }
    
    /**
     * Helper method to get data with or without cache
     */
    private function getDataWithCache(?bool $skipCache, string $cacheKey, callable $dataCallback)
    {
        // Asegurar que skipCache es booleano
        $skipCache = $skipCache === null ? false : (bool)$skipCache;
        
        // Omitir caché si se solicita explícitamente
        if ($skipCache) {
            Log::debug("Omitiendo caché para clave: {$cacheKey}");
            return $dataCallback();
        }
        
        try {
            return Cache::tags([self::CACHE_TAG])->remember($cacheKey, self::CACHE_TTL, function() use ($dataCallback, $cacheKey) {
                try {
                    $result = $dataCallback();
                    
                    // Verificación adicional para prevenir almacenar nulos
                    if ($result === null) {
                        Log::warning("Callback devolvió null para clave de caché: {$cacheKey}");
                        throw new \Exception("El origen de datos devolvió un valor nulo");
                    }
                    
                    return $result;
                } catch (\Exception $e) {
                    Log::error("Error en callback de caché: " . $e->getMessage());
                    throw $e;
                }
            });
        } catch (\Exception $e) {
            // Si hay un error de caché, intentar obtener los datos sin caché
            Log::warning("Error de caché ({$cacheKey}): " . $e->getMessage());
            
            // Si es un error específico de la función callback, propagarlo
            if ($e->getMessage() !== "CACHE_ERROR") {
                try {
                    return $dataCallback();
                } catch (\Exception $callbackError) {
                    Log::error("Error en callback (intento sin caché): " . $callbackError->getMessage());
                    throw $callbackError;
                }
            }
            
            // Otros errores relacionados con la caché
            throw $e;
        }
    }
    
    /**
     * Helper method to handle exceptions consistently
     */
    private function handleException(Exception $e, string $message, int $statusCode = 500): JsonResponse
    {
        Log::error("$message: " . $e->getMessage());
        return $this->errorResponse(
            $message,
            $statusCode,
            ['error' => $e->getMessage()]  // Convertir string a array asociativo
        );
    }
    
    /**
     * Helper method to fetch categories with filters and pagination
     */
    private function fetchCategories(ListCategoryRequest $request)
    {
        try {
            // Consulta base con manejo de borrados
            $query = $this->initializeQuery($request->show_deleted);
            
            // Aplicar todos los filtros
            $this->applyCommonFilters($query, $request);
            
            // Búsqueda por texto - verificar que el scope existe
            if ($request->search && method_exists(Category::class, 'scopeSearch')) {
                $query->search($request->search);
            } elseif ($request->search) {
                // Fallback básico de búsqueda si no existe el scope
                $search = '%' . $request->search . '%';
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', $search)
                      ->orWhere('description', 'like', $search);
                });
            }
            
            // Cargar relaciones si se solicita con manejo de errores
            try {
                $this->loadRequestedRelations($query, $request);
            } catch (\Exception $e) {
                Log::warning("Error al cargar relaciones: " . $e->getMessage());
                // Continuar sin relaciones para evitar errores fatales
            }
            
            // Ordenamiento
            $this->applySorting($query, $request);
            
            // Paginación con límites razonables
            $perPage = max(min($request->per_page ?? 15, 100), 1);
            
            // Capturar cualquier error durante la paginación
            try {
                return $query->paginate($perPage)->withQueryString();
            } catch (\Exception $e) {
                Log::error("Error en la paginación: " . $e->getMessage());
                throw new \Exception("Error al procesar los resultados de categorías: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error("Error general en fetchCategories: " . $e->getMessage());
            throw $e; // Re-lanzar para manejo superior
        }
    }
    
    /**
     * Initialize query with or without trashed records
     */
    private function initializeQuery(bool $withTrashed = false)
    {
        $query = Category::query();
        
        if ($withTrashed) {
            $query->withTrashed();
        }
        
        return $query;
    }
    
    /**
     * Helper method to apply common filters to the query
     */
    private function applyCommonFilters($query, $request)
    {
        // Aplicar filtros básicos de forma eficiente
        if ($request->parent_only) {
            $query->whereNull('parent_id');
        }
        
        if ($request->active_only) {
            $query->where('is_active', true);
        }
        
        return $query;
    }
    
    /**
     * Apply active and deleted filters to a relation query
     */
    private function applyActiveAndDeletedFilters($query, $request)
    {
        if ($request->active_only) {
            $query->where('is_active', true);
        }
        
        if (!$request->show_deleted) {
            $query->whereNull('deleted_at');
        }
        
        return $query;
    }
    
    /**
     * Helper method to load requested relations
     */
    private function loadRequestedRelations($query, $request)
    {
        if ($request->with_children) {
            $query->with(['children' => function($q) use ($request) {
                $this->applyActiveAndDeletedFilters($q, $request);
            }]);
        }
        
        if ($request->with_skills) {
            $query->with(['skills' => function($q) use ($request) {
                $this->applyActiveAndDeletedFilters($q, $request);
            }]);
        }
        
        if ($request->with_service_requests) {
            $query->with(['serviceRequests' => function($q) use ($request) {
                $this->applyActiveAndDeletedFilters($q, $request);
            }]);
        }
        
        return $query;
    }
    
    /**
     * Helper method to apply sorting to the query
     */
    private function applySorting($query, $request)
    {
        $sortBy = in_array($request->sort_by, ['name', 'created_at', 'updated_at', 'sort_order']) 
                ? $request->sort_by 
                : 'name';
        $direction = $request->sort_direction === 'desc' ? 'desc' : 'asc';

        // Si es sort_order, agregamos un orden secundario por nombre
        if ($sortBy === 'sort_order') {
            $query->orderBy('sort_order', $direction)->orderBy('name', 'asc');
        } else {
            $query->orderBy($sortBy, $direction);
        }
        
        return $query;
    }
    
    /**
     * Helper method to fetch a single category with relationships
     */
    private function fetchCategory(Category $category, ListCategoryRequest $request)
    {
        // Verificar que la categoría existe
        if (!$category || !$category->id) {
            throw new \Exception("La categoría solicitada no existe o ya ha sido eliminada");
        }
        
        // Siempre cargar la relación padre
        $relations = ['parent'];
        
        // Determinar qué relaciones adicionales cargar
        if ($request->with_children) {
            $relations[] = 'children';
        }
        
        if ($request->with_skills) {
            $relations[] = 'skills';
        }
        
        if ($request->with_service_requests) {
            $relations[] = 'serviceRequests';
        }
        
        try {
            // Asegurar que las relaciones estén cargadas
            $category->load($relations);
            
            // Aplicar filtros a las relaciones cargadas si es necesario
            if ($request->active_only || !$request->show_deleted) {
                foreach (['children', 'skills', 'serviceRequests'] as $relation) {
                    if ($category->relationLoaded($relation)) {
                        // Filtrar colecciones cargadas
                        $filtered = $category->{$relation}->filter(function ($item) use ($request) {
                            if (!$item) return false;
                            
                            $activeCondition = !$request->active_only || $item->is_active;
                            $deletedCondition = $request->show_deleted || $item->deleted_at === null;
                            
                            return $activeCondition && $deletedCondition;
                        });
                        
                        // Reemplazar la colección con la filtrada
                        $category->setRelation($relation, $filtered);
                    }
                }
            }
            
            return $category;
        } catch (\Exception $e) {
            // Capturar errores específicos y proporcionar un mensaje más descriptivo
            Log::error("Error al cargar relaciones para la categoría {$category->id}: " . $e->getMessage());
            throw new \Exception("Error al cargar relaciones para la categoría: " . $e->getMessage());
        }
    }
    
    /**
     * Helper method to sanitize request filters for meta information
     */
    private function sanitizeFilters(ListCategoryRequest $request): array
    {
        return [
            'search' => $request->search,
            'parent_only' => (bool)$request->parent_only,
            'active_only' => (bool)$request->active_only,
            'with_children' => (bool)$request->with_children,
            'with_skills' => (bool)$request->with_skills,
            'with_service_requests' => (bool)$request->with_service_requests,
            'show_deleted' => (bool)$request->show_deleted
        ];
    }

    /**
     * Get all deleted children IDs recursively
     */
    private function getAllDeletedChildrenIds(int $parentId): array
    {
        $childrenIds = [];
        
        // Obtener hijos directos eliminados
        $directChildren = Category::withTrashed()
            ->where('parent_id', $parentId)
            ->whereNotNull('deleted_at')
            ->get(['id']);
        
        foreach ($directChildren as $child) {
            $childrenIds[] = $child->id;
            // Obtener recursivamente los hijos de este hijo
            $nestedChildrenIds = $this->getAllDeletedChildrenIds($child->id);
            $childrenIds = array_merge($childrenIds, $nestedChildrenIds);
        }
        
        return $childrenIds;
    }
}