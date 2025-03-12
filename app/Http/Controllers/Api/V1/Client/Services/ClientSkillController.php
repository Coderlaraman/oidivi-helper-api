<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;

class SkillController extends Controller
{
    /**
     * Listar todas las habilidades con sus categorías.
     */
    public function index(): JsonResponse
    {
        $skills = Skill::with('categories')->get();
        return response()->json($skills);
    }

    /**
     * Obtener una habilidad específica junto con sus categorías.
     */
    public function show(Skill $skill): JsonResponse
    {
        return response()->json($skill->load('categories'));
    }
}
