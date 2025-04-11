<?php

namespace App\Http\Controllers\Api\V1\Admin\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminCategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::orderBy('name')->get();
        return response()->json([
            'data' => AdminCategoryResource::collection($categories)
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $category = Category::create($request->only(['name', 'description']));

        return response()->json([
            'data' => new AdminCategoryResource($category),
            'message' => 'Categoría creada correctamente'
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'data' => new AdminCategoryResource($category)
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $category->update($request->only(['name', 'description']));

        return response()->json([
            'data' => new AdminCategoryResource($category),
            'message' => 'Categoría actualizada correctamente'
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json([
            'message' => 'Categoría eliminada correctamente'
        ]);
    }
}
