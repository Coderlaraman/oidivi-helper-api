<?php

namespace App\Http\Controllers\Api\V1\Admin\Categories;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\AdminListCategoryRequest;
use App\Http\Requests\Admin\Category\AdminStoreCategoryRequest;
use App\Http\Requests\Admin\Category\AdminUpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Listar categorías
     */
    public function index(AdminListCategoryRequest $request): JsonResponse
    {
        $query = Category::query();

        // Aplicar filtros
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('slug', 'like', "%{$request->search}%");
            });
        }

        if ($request->boolean('parent_only')) {
            $query->whereNull('parent_id');
        }

        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        if (!$request->boolean('show_deleted')) {
            $query->whereNull('deleted_at');
        }

        // Cargar relaciones
        if ($request->boolean('with_children')) {
            $query->with('children');
        }

        if ($request->boolean('with_skills')) {
            $query->with('skills');
        }

        if ($request->boolean('with_service_requests')) {
            $query->with('serviceRequests');
        }

        // Ordenar
        $sortBy = $request->input('sort_by', 'sort_order');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginar
        $perPage = $request->input('per_page', 15);
        $categories = $query->paginate($perPage);

        return response()->json([
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    /**
     * Obtener una categoría específica
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'data' => $category->load(['parent', 'children', 'skills', 'serviceRequests']),
        ]);
    }

    /**
     * Crear una nueva categoría
     */
    public function store(AdminStoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create([
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
            'metadata' => $request->metadata,
        ]);

        // Limpiar caché
        Cache::tags(['categories'])->flush();

        return response()->json([
            'message' => 'Categoría creada correctamente.',
            'data' => $category,
        ], 201);
    }

    /**
     * Actualizar una categoría
     */
    public function update(AdminUpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update([
            'name' => $request->name,
            'slug' => $request->slug ?? Str::slug($request->name),
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order,
            'metadata' => $request->metadata,
        ]);

        // Limpiar caché
        Cache::tags(['categories'])->flush();

        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
            'data' => $category,
        ]);
    }

    /**
     * Eliminar una categoría
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        // Limpiar caché
        Cache::tags(['categories'])->flush();

        return response()->json([
            'message' => 'Categoría eliminada correctamente.',
        ]);
    }

    /**
     * Restaurar una categoría eliminada
     */
    public function restore(int $id): JsonResponse
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();

        // Limpiar caché
        Cache::tags(['categories'])->flush();

        return response()->json([
            'message' => 'Categoría restaurada correctamente.',
            'data' => $category,
        ]);
    }
}
