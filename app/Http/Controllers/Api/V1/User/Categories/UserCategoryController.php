<?php

namespace App\Http\Controllers\Api\V1\User\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserCategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class UserCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $categories = Category::active()
                ->whereNull('deleted_at')
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => UserCategoryResource::collection($categories)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las categorías disponibles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
