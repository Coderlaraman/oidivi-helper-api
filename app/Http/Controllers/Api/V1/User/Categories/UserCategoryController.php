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
        $categories = Category::orderBy('name')->get();
        return response()->json([
            'data' => UserCategoryResource::collection($categories)
        ]);
    }
}
