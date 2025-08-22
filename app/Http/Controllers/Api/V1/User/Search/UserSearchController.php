<?php

namespace App\Http\Controllers\Api\V1\User\Search;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServiceRequest;
use App\Models\Category;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserSearchController extends Controller
{
    public function searchUsers(Request $request): JsonResponse
    {
        $search = $request->input('search', $request->input('query'));
        $categoryIds = $this->parseIds($request->input('category_ids', $request->input('categories')));
        $skillIds = $this->parseIds($request->input('skill_ids', $request->input('skills')));
        $matchMode = in_array($request->input('match_mode', 'any'), ['any', 'all']) ? $request->input('match_mode', 'any') : 'any';
        $perPage = (int) $request->input('per_page', 10);
        $include = $request->input('include', 'skills.categories');

        $query = User::query()->where('is_active', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  // Removed professional_title because the column does not exist in users table
                  ->orWhere('biography', 'like', "%{$search}%");
            });
        }

        if (!empty($skillIds)) {
            if ($matchMode === 'all') {
                foreach ($skillIds as $sid) {
                    $query->whereHas('skills', function ($q) use ($sid) {
                        $q->where('skills.id', $sid);
                    });
                }
            } else {
                $query->whereHas('skills', function ($q) use ($skillIds) {
                    $q->whereIn('skills.id', $skillIds);
                });
            }
        }

        if (!empty($categoryIds)) {
            if ($matchMode === 'all') {
                foreach ($categoryIds as $cid) {
                    $query->whereHas('skills.categories', function ($q) use ($cid) {
                        $q->where('categories.id', $cid);
                    });
                }
            } else {
                $query->whereHas('skills.categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }
        }

        $with = array_filter(array_map('trim', explode(',', (string) $include)));
        if (!empty($with)) {
            $query->with($with);
        }

        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Users search results',
            'data' => $users->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'has_more_pages' => $users->hasMorePages(),
                ],
            ],
        ]);
    }
    public function searchServiceRequests(Request $request): JsonResponse
    {
        $search = $request->input('search', $request->input('query'));
        $categoryIds = $this->parseIds($request->input('category_ids', $request->input('categories')));
        $skillIds = $this->parseIds($request->input('skill_ids', $request->input('skills')));
        $matchMode = in_array($request->input('match_mode', 'any'), ['any', 'all']) ? $request->input('match_mode', 'any') : 'any';
        $perPage = (int) $request->input('per_page', 10);
        $include = $request->input('include', 'user,categories');

        $query = ServiceRequest::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtrado por categorías directas
        if (!empty($categoryIds)) {
            if ($matchMode === 'all') {
                foreach ($categoryIds as $cid) {
                    $query->whereHas('categories', function ($q) use ($cid) {
                        $q->where('categories.id', $cid);
                    });
                }
            } else {
                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }
        }

        // Filtrado por habilidades -> categorías de esas habilidades
        if (!empty($skillIds)) {
            $catsFromSkills = Category::whereHas('skills', function ($q) use ($skillIds) {
                $q->whereIn('skills.id', $skillIds);
            })->pluck('id')->toArray();

            if (!empty($catsFromSkills)) {
                if ($matchMode === 'all') {
                    foreach ($catsFromSkills as $cid) {
                        $query->whereHas('categories', function ($q) use ($cid) {
                            $q->where('categories.id', $cid);
                        });
                    }
                } else {
                    $query->whereHas('categories', function ($q) use ($catsFromSkills) {
                        $q->whereIn('categories.id', $catsFromSkills);
                    });
                }
            } else {
                // Si no hay categorías derivadas de las habilidades, fuerza resultado vacío
                $query->whereRaw('1 = 0');
            }
        }

        $with = array_filter(array_map('trim', explode(',', (string) $include)));
        if (!empty($with)) {
            $query->with($with);
        }

        $requests = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Service requests search results',
            'data' => $requests->items(),
            'meta' => [
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                    'has_more_pages' => $requests->hasMorePages(),
                ],
            ],
        ]);
    }
    private function parseIds($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('intval', $value), fn ($v) => $v > 0));
        }
        if (is_string($value)) {
            return array_values(array_filter(array_map('intval', preg_split('/[,\s]+/', $value)), fn ($v) => $v > 0));
        }
        return [];
    }
}