<?php

namespace App\Http\Controllers\Api\V1\Client\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ClientCategoryResource;
use App\Http\Requests\Client\Category\ListCategoryRequest;
use App\Http\Requests\Client\Category\StoreCategoryRequest;
use App\Http\Requests\Client\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ClientCategoryController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all categories with filtering, sorting and pagination.
     */
    public function index(ListCategoryRequest $request): JsonResponse
    {
        $cacheKey = "categories:{$request->fullUrl()}";

        $categories = Cache::remember($cacheKey, 3600, function () use ($request) {
            return Category::query()
                ->when($request->parent_only, fn($q) => $q->parentOnly())
                ->when($request->active_only, fn($q) => $q->active())
                ->when($request->with_children, fn($q) => $q->with('children'))
                ->when($request->with_skills, fn($q) => $q->with('skills'))
                ->when($request->with_service_requests, fn($q) => $q->with('serviceRequests'))
                ->search($request->search)
                ->ordered()
                ->paginate($request->per_page ?? 15);
        });

        return $this->successResponse(
            ClientCategoryResource::collection($categories)
                ->additional(['meta' => [
                    'total' => $categories->total(),
                    'page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage()
                ]]),
            __('messages.categories.list_success')
        );
    }

    /**
     * Get a specific category with its relationships.
     */
    public function show(Category $category, ListCategoryRequest $request): JsonResponse
    {
        $cacheKey = "category:{$category->id}:{$request->fullUrl()}";

        $category = Cache::remember($cacheKey, 3600, function () use ($category, $request) {
            return $category->load([
                'children' => fn($q) => $q->when($request->active_only, fn($q) => $q->active()),
                'skills' => fn($q) => $q->when($request->active_only, fn($q) => $q->active()),
                'serviceRequests' => fn($q) => $q->when($request->active_only, fn($q) => $q->active())
            ]);
        });

        return $this->successResponse(
            new ClientCategoryResource($category),
            __('messages.categories.show_success')
        );
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());
        
        Cache::tags(['categories'])->flush();

        return $this->successResponse(
            new ClientCategoryResource($category),
            __('messages.categories.create_success'),
            201
        );
    }

    /**
     * Update an existing category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());
        
        Cache::tags(['categories'])->flush();

        return $this->successResponse(
            new ClientCategoryResource($category),
            __('messages.categories.update_success')
        );
    }

    /**
     * Delete a category.
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        
        Cache::tags(['categories'])->flush();

        return $this->successResponse(
            null,
            __('messages.categories.delete_success')
        );
    }
}
