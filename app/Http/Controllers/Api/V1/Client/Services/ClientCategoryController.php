<?php

namespace App\Http\Controllers\Api\V1\Client\Services;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ClientCategoryController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all categories with related skills.
     */
    public function index(): JsonResponse
    {
        $categories = Category::with('skills')->get();
        return $this->successResponse($categories, 'Categories retrieved successfully.');
    }

    /**
     * Get a specific category along with its skills.
     */
    public function show(Category $category): JsonResponse
    {
        return $this->successResponse($category->load('skills'), 'Category retrieved successfully.');
    }
}
