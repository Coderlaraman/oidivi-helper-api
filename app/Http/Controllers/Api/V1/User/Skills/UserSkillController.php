<?php

namespace App\Http\Controllers\Api\V1\User\Skills;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserSkillResource;
use App\Models\Skill;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;

class UserSkillController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all skills with their categories.
     */
    public function index(): JsonResponse
    {
        try {
            $skills = Skill::with('categories')
                ->orderBy('name')
                ->get();

            return $this->successResponse(
                UserSkillResource::collection($skills),
                'Skills retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error retrieving skills',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get a specific skill with its categories.
     */
    public function show(Skill $skill): JsonResponse
    {
        try {
            return $this->successResponse(
                new UserSkillResource($skill->load('categories')),
                'Skill details retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error retrieving skill details',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function getByCategory(int $categoryId): JsonResponse
    {
        try {
            $skills = Skill::whereHas('categories', function($query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            })
            ->with('categories')
            ->get();

            return $this->successResponse(
                UserSkillResource::collection($skills),
                'Skills by category retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error retrieving skills by category',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
