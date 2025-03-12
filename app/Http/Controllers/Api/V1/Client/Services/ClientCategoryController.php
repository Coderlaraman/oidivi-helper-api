<?php

namespace App\Http\Controllers\Api\V1\Client\Services;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Listar todas las categorías.
     */
    public function index(): JsonResponse
    {
        $categories = Category::with('skills')->get();
        return response()->json($categories);
    }

    /**
     * Obtener una categoría específica junto con sus habilidades.
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json($category->load('skills'));
    }
}
