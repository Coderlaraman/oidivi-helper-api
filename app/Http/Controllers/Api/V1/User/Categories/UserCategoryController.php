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
     * Retrieves all available categories for user skill selection.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::where('status', true)
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            UserCategoryResource::collection($categories),
            __('messages.categories.list_success')
        );
    }

    public function show(Request $request, Category $category): JsonResponse
    {
        if (!$category->status) {
            return $this->errorResponse(__('messages.categories.not_found'), 404);
        }

        return $this->successResponse(
            new UserCategoryResource($category),
            __('messages.categories.show_success')
        );
    }
}
