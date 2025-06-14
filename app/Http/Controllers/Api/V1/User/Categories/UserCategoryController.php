<?php

namespace App\Http\Controllers\Api\V1\User\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserCategoryResource;
use App\Models\Category;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserCategoryController extends Controller
{
    use ApiResponseTrait;

    /**
     * List categories with filtering, sorting, searching, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        // Validar y aplicar filtro por estado
        $status = $request->get('status', 'active');
        if (!in_array($status, ['active', 'inactive', 'all'])) {
            return $this->validationErrorResponse([
                'status' => [__('validation.in', ['attribute' => 'status'])]
            ]);
        }
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Buscar por nombre o descripción
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        // Validar campos de ordenamiento
        $allowedSortFields = ['id', 'name', 'created_at', 'updated_at'];
        $sortBy = $request->get('sort_by', 'name');
        if (!in_array($sortBy, $allowedSortFields)) {
            return $this->validationErrorResponse([
                'sort_by' => [__('validation.in', ['attribute' => 'sort_by'])]
            ]);
        }

        $sortDirection = $request->get('sort_direction', 'asc');
        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortDirection);

        // Validar cantidad de resultados por página
        $perPage = (int) $request->get('per_page', 10);
        $perPage = min(max($perPage, 1), 100); // mínimo 1, máximo 100

        $categories = $query->paginate($perPage);

        return $this->successResponse(
            [
                'data' => UserCategoryResource::collection($categories->items()),
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                ]
            ],
            __('messages.categories.list_success')
        );
    }

    /**
     * Show a single category.
     */
    public function show(Request $request, Category $category): JsonResponse
    {
        if ($category->status !== 'active') {
            return $this->notFoundResponse(__('messages.categories.not_found'));
        }

        return $this->successResponse(
            new UserCategoryResource($category),
            __('messages.categories.show_success')
        );
    }
}
