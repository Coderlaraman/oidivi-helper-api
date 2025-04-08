<?php

namespace App\Http\Controllers\Api\V1\Admin\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminCategoryResource;
use App\Http\Requests\Admin\Category\AdminListCategoryRequest;
use App\Http\Requests\Admin\Category\AdminStoreCategoryRequest;
use App\Http\Requests\Admin\Category\AdminUpdateCategoryRequest;
use App\Models\Category;
use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminCategoryController extends Controller
{
    use ApiResponseTrait;

    private const CACHE_TTL = 3600; // 1 hora
    private const CACHE_TAG = 'admin:categories';

    // Lista de parámetros relevantes para la caché
    private const CACHE_PARAMS = [
        'search', 'active_only', 'with_skills', 
        'with_service_requests', 'sort_by',
        'sort_direction', 'per_page', 'page', 
        'show_deleted', 'show_inactive'
    ];

    /**
     * List all categories with comprehensive filtering, sorting and pagination.
     * Administrators can see all categories including inactive and deleted ones.
     */
    public function index(AdminListCategoryRequest $request): JsonResponse
    {
        try {
            $cacheKey = $this->generateCacheKey($request);
            
            if (!$request->boolean('_no_cache')) {
                $cached = Cache::tags([self::CACHE_TAG])->get($cacheKey);
                if ($cached) {
                    return $this->successResponse($cached);
                }
            }

            $query = $this->initializeQuery($request->boolean('show_deleted'));
            $query = $this->applyCommonFilters($query, $request);
            $query = $this->loadRequestedRelations($query, $request);
            $query = $this->applySorting($query, $request);

            $categories = $query->paginate(
                $request->integer('per_page', 15),
                ['*'],
                'page',
                $request->integer('page', 1)
            );

            $response = [
                'data' => AdminCategoryResource::collection($categories),
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'from' => $categories->firstItem(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'to' => $categories->lastItem(),
                    'total' => $categories->total(),
                    'filters' => $this->sanitizeFilters($request)
                ]
            ];

            Cache::tags([self::CACHE_TAG])->put($cacheKey, $response, self::CACHE_TTL);

            return $this->successResponse($response);
        } catch (Exception $e) {
            Log::error('Error listing categories: ' . $e->getMessage());
            return $this->errorResponse('Error al listar las categorías', 500);
        }
    }

    /**
     * Store a newly created category.
     */
    public function store(AdminStoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = Category::create($request->validated());
            
            $this->clearCategoryCache();
            
            Log::info("Categoría creada por administrador: ID {$category->id}", [
                'admin_id' => auth()->id(),
                'category_data' => $category->toArray()
            ]);

            return $this->successResponse(
                new AdminCategoryResource($category),
                'Categoría creada correctamente'
            );
        } catch (Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());
            return $this->errorResponse('Error al crear la categoría', 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): JsonResponse
    {
        try {
            return $this->successResponse(
                new AdminCategoryResource($category)
            );
        } catch (Exception $e) {
            Log::error('Error showing category: ' . $e->getMessage());
            return $this->errorResponse('Error al mostrar la categoría', 500);
        }
    }

    /**
     * Update the specified category.
     */
    public function update(AdminUpdateCategoryRequest $request, Category $category): JsonResponse
    {
        try {
            $category->update($request->validated());
            
            $this->clearCategoryCache($category->id);
            
            Log::info("Categoría actualizada por administrador: ID {$category->id}", [
                'admin_id' => auth()->id(),
                'category_data' => $category->toArray()
            ]);

            return $this->successResponse(
                new AdminCategoryResource($category),
                'Categoría actualizada correctamente'
            );
        } catch (Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            return $this->errorResponse('Error al actualizar la categoría', 500);
        }
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            if ($category->hasRelatedEntities()) {
                return $this->errorResponse(
                    'No se puede eliminar la categoría porque tiene entidades relacionadas',
                    422
                );
            }

            $category->delete();
            
            $this->clearCategoryCache($category->id);
            
            Log::info("Categoría eliminada por administrador: ID {$category->id}", [
                'admin_id' => auth()->id()
            ]);

            return $this->successResponse(
                null,
                'Categoría eliminada correctamente'
            );
        } catch (Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return $this->errorResponse('Error al eliminar la categoría', 500);
        }
    }

    /**
     * Restore a soft-deleted category.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $category = Category::withTrashed()->findOrFail($id);
            $category->restore();
            
            $this->clearCategoryCache($category->id);
            
            Log::info("Categoría restaurada por administrador: ID {$category->id}", [
                'admin_id' => auth()->id(),
                'category_data' => $category->toArray()
            ]);

            return $this->successResponse(
                new AdminCategoryResource($category),
                'Categoría restaurada correctamente'
            );
        } catch (Exception $e) {
            Log::error('Error restoring category: ' . $e->getMessage());
            return $this->errorResponse('Error al restaurar la categoría', 500);
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
        if ($request->with_skills) {
            $query->with(['skills' => function ($q) use ($request) {
                $this->applyActiveAndDeletedFilters($q, $request);
            }]);
        }

        if ($request->with_service_requests) {
            $query->with(['serviceRequests' => function ($q) use ($request) {
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
     * Helper method to sanitize request filters for meta information
     */
    private function sanitizeFilters(AdminListCategoryRequest $request): array
    {
        return [
            'search' => $request->search,
            'active_only' => (bool)$request->active_only,
            'with_skills' => (bool)$request->with_skills,
            'with_service_requests' => (bool)$request->with_service_requests,
            'show_deleted' => (bool)$request->show_deleted,
            'show_inactive' => (bool)$request->show_inactive
        ];
    }

    /**
     * Generate cache key based on request parameters
     */
    private function generateCacheKey(AdminListCategoryRequest $request): string
    {
        $params = [];
        foreach (self::CACHE_PARAMS as $param) {
            if ($request->has($param)) {
                $params[$param] = $request->input($param);
            }
        }
        return 'admin:categories:' . md5(json_encode($params));
    }

    /**
     * Clear category cache
     */
    private function clearCategoryCache(?int $categoryId = null): void
    {
        Cache::tags([self::CACHE_TAG])->flush();
    }
}
