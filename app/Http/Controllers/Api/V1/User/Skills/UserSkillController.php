<?php

namespace App\Http\Controllers\Api\V1\User\Skills;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Skill\StoreUserSkillRequest;
use App\Http\Resources\User\UserSkillResource;
use App\Models\Skill;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserSkillController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lista las habilidades del usuario autenticado con sus categorías.
     */
    public function index(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $skills = $user->skills()
                ->with(['categories' => function ($query) {
                    $query->select('categories.id', 'categories.name', 'categories.slug');
                }])
                ->orderBy('name')
                ->get();

            return $this->successResponse(
                UserSkillResource::collection($skills),
                'Habilidades recuperadas exitosamente'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error al recuperar las habilidades',
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

    /**
     * Get skills by category
     */
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

    /**
     * Asocia múltiples habilidades al usuario autenticado.
     */
    public function store(StoreUserSkillRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $skillIds = $request->input('skill_ids');

            // Verificar que las habilidades existan y tengan categorías
            $skills = Skill::whereIn('id', $skillIds)
                ->with(['categories' => function ($query) {
                    $query->select('categories.id', 'categories.name');
                }])
                ->get();

            if ($skills->count() !== count($skillIds)) {
                return $this->errorResponse(
                    'Una o más habilidades no existen en el sistema',
                    422
                );
            }

            // Verificar que todas las habilidades tengan categorías
            $skillsWithoutCategories = $skills->filter(function ($skill) {
                return $skill->categories->isEmpty();
            });

            if ($skillsWithoutCategories->isNotEmpty()) {
                return $this->errorResponse(
                    'Las siguientes habilidades no tienen categorías asociadas: ' . 
                    $skillsWithoutCategories->pluck('name')->implode(', '),
                    422
                );
            }

            // Asociar las habilidades al usuario
            $user->skills()->syncWithoutDetaching($skillIds);

            // Recargar las habilidades con sus categorías
            $updatedSkills = $user->skills()
                ->with(['categories' => function ($query) {
                    $query->select('categories.id', 'categories.name');
                }])
                ->orderBy('name')
                ->get();

            DB::commit();

            return $this->successResponse(
                UserSkillResource::collection($updatedSkills),
                'Skills added successfully'
            );

        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Error adding skills',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Elimina una habilidad del usuario autenticado.
     */
    public function destroy(Skill $skill): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();

            if (!$user->skills()->where('skills.id', $skill->id)->exists()) {
                return $this->errorResponse(
                    'You do not have this skill assigned',
                    404
                );
            }

            $user->skills()->detach($skill->id);

            DB::commit();

            return $this->successResponse(
                null,
                'Skill deleted successfully'
            );

        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Error deleting skill',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Lista todas las habilidades disponibles en el sistema con sus categorías.
     */
    public function available(): JsonResponse
    {
        try {
            $skills = Skill::where('is_active', true)
                ->with(['categories' => function ($query) {
                    $query->select('categories.id', 'categories.name', 'categories.slug');
                }])
                ->orderBy('name')
                ->get();

            return $this->successResponse(
                UserSkillResource::collection($skills),
                'Available skills retrieved successfully'
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                'Error retrieving available skills',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
