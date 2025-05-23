<?php

namespace App\Http\Controllers\Api\V1\Admin\Skills;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Skill\AdminListSkillRequest;
use App\Http\Requests\Admin\Skill\AdminStoreSkillRequest;
use App\Http\Requests\Admin\Skill\AdminUpdateSkillRequest;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminSkillController extends Controller
{
    /**
     * Display a listing of the skills.
     */
    public function index(AdminListSkillRequest $request): JsonResponse
    {
        $query = Skill::query();

        // Aplicar filtros
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        if ($request->boolean('show_deleted')) {
            $query->withTrashed();
        }

        // Cargar relaciones
        if ($request->boolean('with_category')) {
            $query->with('category');
        }

        // Ordenar
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Paginar
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        // Cache
        $cacheKey = "admin:skills:list:{$request->fullUrl()}";
        $skills = Cache::remember($cacheKey, 300, function () use ($query, $perPage, $page) {
            return $query->paginate($perPage, ['*'], 'page', $page);
        });

        return response()->json($skills);
    }

    /**
     * Store a newly created skill in storage.
     */
    public function store(AdminStoreSkillRequest $request): JsonResponse
    {
        $skill = Skill::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'description' => $request->input('description'),
            'category_id' => $request->input('category_id'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order'),
            'metadata' => $request->input('metadata'),
        ]);

        Cache::tags(['skills'])->flush();

        return response()->json($skill, 201);
    }

    /**
     * Display the specified skill.
     */
    public function show(Skill $skill): JsonResponse
    {
        $skill->load('category');

        return response()->json($skill);
    }

    /**
     * Update the specified skill in storage.
     */
    public function update(AdminUpdateSkillRequest $request, Skill $skill): JsonResponse
    {
        $skill->update([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'description' => $request->input('description'),
            'category_id' => $request->input('category_id'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->input('sort_order'),
            'metadata' => $request->input('metadata'),
        ]);

        Cache::tags(['skills'])->flush();

        return response()->json($skill);
    }

    /**
     * Remove the specified skill from storage.
     */
    public function destroy(Skill $skill): JsonResponse
    {
        $skill->delete();

        Cache::tags(['skills'])->flush();

        return response()->json(null, 204);
    }

    /**
     * Restore the specified skill from storage.
     */
    public function restore(Skill $skill): JsonResponse
    {
        $skill->restore();

        Cache::tags(['skills'])->flush();

        return response()->json($skill);
    }
}
