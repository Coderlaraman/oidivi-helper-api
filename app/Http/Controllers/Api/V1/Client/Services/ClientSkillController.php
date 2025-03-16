<?php

namespace App\Http\Controllers\Api\V1\Client\Services;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ClientSkillController extends Controller
{
    use ApiResponseTrait;

    /**
     * List all skills with their categories.
     */
    public function index(): JsonResponse
    {
        $skills = Skill::with('categories')->get();
        return $this->successResponse($skills, 'Skills retrieved successfully.');
    }

    /**
     * Get a specific skill with its categories.
     */
    public function show(Skill $skill): JsonResponse
    {
        return $this->successResponse($skill->load('categories'), 'Skill retrieved successfully.');
    }
}
