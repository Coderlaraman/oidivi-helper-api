<?php

namespace App\Http\Controllers\Api\V1\Search;

use App\Http\Controllers\Controller;
use App\Http\Requests\Search\GlobalSearchRequest;
use App\Http\Requests\Search\UserSearchRequest;
use App\Http\Requests\Search\ServiceRequestSearchRequest;
use App\Http\Requests\Search\ServiceOfferSearchRequest;
use App\Models\User;
use App\Models\ServiceRequest;
use App\Models\ServiceOffer;
use App\Models\Category;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    /**
     * Búsqueda global unificada en todos los tipos de contenido
     */
    public function global(GlobalSearchRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $type = $validated['type'] ?? 'all';
        $results = [];

        try {
            if ($type === 'all' || $type === 'users') {
                $results['users'] = $this->searchUsers($validated);
            }

            if ($type === 'all' || $type === 'service_requests') {
                $results['service_requests'] = $this->searchServiceRequests($validated);
            }

            if ($type === 'all' || $type === 'service_offers') {
                $results['service_offers'] = $this->searchServiceOffers($validated);
            }

            return response()->json([
                'success' => true,
                'message' => 'Search completed successfully',
                'data' => $results,
                'meta' => [
                    'query' => $validated['query'] ?? '',
                    'type' => $type,
                    'total_results' => $this->getTotalResults($results)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Búsqueda específica de usuarios
     */
    public function users(UserSearchRequest $request): JsonResponse
    {
        try {
            $results = $this->searchUsers($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Users search completed successfully',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Users search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Búsqueda específica de solicitudes de servicio
     */
    public function serviceRequests(ServiceRequestSearchRequest $request): JsonResponse
    {
        try {
            $results = $this->searchServiceRequests($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Service requests search completed successfully',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service requests search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Búsqueda específica de ofertas de servicio
     */
    public function serviceOffers(ServiceOfferSearchRequest $request): JsonResponse
    {
        try {
            $results = $this->searchServiceOffers($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Service offers search completed successfully',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service offers search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener sugerencias de autocompletado
     */
    public function suggestions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:100',
            'type' => 'nullable|in:all,users,categories,skills,locations',
            'limit' => 'nullable|integer|min:1|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $params = $validator->validated();
        $query = $params['query'];
        $type = $params['type'] ?? 'all';
        $limit = $params['limit'] ?? 10;

        try {
            $suggestions = [];

            if ($type === 'all' || $type === 'categories') {
                $categories = Category::where('name', 'LIKE', "%{$query}%")
                    ->where('status', 'active')
                    ->limit($limit)
                    ->get(['id', 'name', 'slug'])
                    ->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'title' => $category->name,
                            'type' => 'category',
                            'slug' => $category->slug
                        ];
                    });
                $suggestions = array_merge($suggestions, $categories->toArray());
            }

            if ($type === 'all' || $type === 'skills') {
                $skills = Skill::where('name', 'LIKE', "%{$query}%")
                    ->where('is_active', true)
                    ->limit($limit)
                    ->get(['id', 'name'])
                    ->map(function ($skill) {
                        return [
                            'id' => $skill->id,
                            'title' => $skill->name,
                            'type' => 'skill'
                        ];
                    });
                $suggestions = array_merge($suggestions, $skills->toArray());
            }

            if ($type === 'all' || $type === 'users') {
                $users = User::where('name', 'LIKE', "%{$query}%")
                    ->where('is_active', true)
                    ->limit($limit)
                    ->get(['id', 'name', 'email'])
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'title' => $user->name,
                            'type' => 'user',
                            'subtitle' => $user->email
                        ];
                    });
                $suggestions = array_merge($suggestions, $users->toArray());
            }

            // Limitar el total de sugerencias
            $suggestions = array_slice($suggestions, 0, $limit);

            return response()->json([
                'success' => true,
                'message' => 'Suggestions retrieved successfully',
                'suggestions' => $suggestions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get suggestions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener filtros disponibles para búsqueda
     */
    public function filters(Request $request): JsonResponse
    {
        try {
            $filters = [
                'categories' => Category::where('status', 'active')
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']),
                'skills' => Skill::where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'service_request_statuses' => [
                    ['value' => 'pending', 'label' => 'Pendiente'],
                    ['value' => 'published', 'label' => 'Publicado'],
                    ['value' => 'in_progress', 'label' => 'En Progreso'],
                    ['value' => 'completed', 'label' => 'Completado'],
                    ['value' => 'canceled', 'label' => 'Cancelado']
                ],
                'priorities' => [
                    ['value' => 'low', 'label' => 'Baja'],
                    ['value' => 'medium', 'label' => 'Media'],
                    ['value' => 'high', 'label' => 'Alta'],
                    ['value' => 'urgent', 'label' => 'Urgente']
                ],
                'service_offer_statuses' => [
                    ['value' => 'pending', 'label' => 'Pendiente'],
                    ['value' => 'accepted', 'label' => 'Aceptado'],
                    ['value' => 'rejected', 'label' => 'Rechazado'],
                    ['value' => 'completed', 'label' => 'Completado']
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Filters retrieved successfully',
                'data' => $filters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get filters',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Búsqueda interna de usuarios
     */
    private function searchUsers(array $params): array
    {
        $query = User::query()
            ->with(['skills', 'userStats'])
            ->where('is_active', true);

        // Búsqueda por texto
        if (!empty($params['query'])) {
            $searchTerm = $params['query'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('biography', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filtro por habilidades
        if (!empty($params['skills'])) {
            $query->whereHas('skills', function ($q) use ($params) {
                $q->whereIn('skills.id', $params['skills']);
            });
        }

        // Filtro por categorías (a través de habilidades)
        if (!empty($params['categories'])) {
            $query->whereHas('skills.categories', function ($q) use ($params) {
                $q->whereIn('categories.id', $params['categories']);
            });
        }

        // Filtro por calificación
        if (!empty($params['min_rating'])) {
            $query->whereHas('userStats', function ($q) use ($params) {
                $q->where('rating', '>=', $params['min_rating']);
            });
        }
        if (!empty($params['max_rating'])) {
            $query->whereHas('userStats', function ($q) use ($params) {
                $q->where('rating', '<=', $params['max_rating']);
            });
        }

        // Filtro geográfico
        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $lat = $params['latitude'];
            $lng = $params['longitude'];
            $radius = $params['radius'] ?? 10;

            $query->whereRaw(
                "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?",
                [$lat, $lng, $lat, $radius]
            );
        }

        // Ordenamiento
        $sortBy = $params['sort_by'] ?? 'relevance';
        $sortOrder = $params['sort_order'] ?? 'desc';

        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'rating':
                $query->leftJoin('user_stats', 'users.id', '=', 'user_stats.user_id')
                      ->orderBy('user_stats.rating', $sortOrder);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortOrder);
                break;
            default: // relevance
                $query->orderBy('updated_at', 'desc');
                break;
        }

        // Paginación
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 15;

        return $query->paginate($perPage, ['*'], 'page', $page)->toArray();
    }

    /**
     * Búsqueda interna de solicitudes de servicio
     */
    private function searchServiceRequests(array $params): array
    {
        $query = ServiceRequest::query()
            ->with(['user', 'categories'])
            ->where('status', '!=', 'draft')
            ->where('visibility', 'public');

        // Búsqueda por texto
        if (!empty($params['query'])) {
            $searchTerm = $params['query'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('address', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filtro por categorías
        if (!empty($params['categories'])) {
            $query->whereHas('categories', function ($q) use ($params) {
                $q->whereIn('categories.id', $params['categories']);
            });
        }

        // Filtro por presupuesto
        if (!empty($params['min_budget'])) {
            $query->where('budget', '>=', $params['min_budget']);
        }
        if (!empty($params['max_budget'])) {
            $query->where('budget', '<=', $params['max_budget']);
        }

        // Filtro por estado
        if (!empty($params['status'])) {
            $query->whereIn('status', $params['status']);
        }

        // Filtro por prioridad
        if (!empty($params['priority'])) {
            $query->whereIn('priority', $params['priority']);
        }

        // Filtro por fechas
        if (!empty($params['date_from'])) {
            $query->where('created_at', '>=', $params['date_from']);
        }
        if (!empty($params['date_to'])) {
            $query->where('created_at', '<=', $params['date_to'] . ' 23:59:59');
        }

        // Filtro geográfico
        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $lat = $params['latitude'];
            $lng = $params['longitude'];
            $radius = $params['radius'] ?? 10;

            $query->whereRaw(
                "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?",
                [$lat, $lng, $lat, $radius]
            );
        }

        // Ordenamiento
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortOrder = $params['sort_order'] ?? 'desc';

        switch ($sortBy) {
            case 'price': // alias para el frontend
            case 'budget':
                $query->orderBy('budget', $sortOrder);
                break;
            case 'due_date':
                $query->orderBy('due_date', $sortOrder);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortOrder);
                break;
            default: // relevance
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Paginación
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 15;

        return $query->paginate($perPage, ['*'], 'page', $page)->toArray();
    }

    /**
     * Búsqueda interna de ofertas de servicio
     */
    private function searchServiceOffers(array $params): array
    {
        $query = ServiceOffer::query()
            ->with(['user', 'serviceRequest']);

        // Búsqueda por texto (en el mensaje de la oferta)
        if (!empty($params['query'])) {
            $searchTerm = $params['query'];
            $query->where('message', 'LIKE', "%{$searchTerm}%");
        }

        // Filtro por precio (acepta price_*, o min/max_budget desde búsqueda global)
        $minPrice = $params['price_min'] ?? $params['min_budget'] ?? null;
        if (!empty($minPrice)) {
            $query->where('price_proposed', '>=', $minPrice);
        }
        $maxPrice = $params['price_max'] ?? $params['max_budget'] ?? null;
        if (!empty($maxPrice)) {
            $query->where('price_proposed', '<=', $maxPrice);
        }

        // Filtro por estado
        if (!empty($params['status'])) {
            $query->whereIn('status', $params['status']);
        }

        // Filtro por fechas
        if (!empty($params['date_from'])) {
            $query->where('created_at', '>=', $params['date_from']);
        }
        if (!empty($params['date_to'])) {
            $query->where('created_at', '<=', $params['date_to'] . ' 23:59:59');
        }

        // Ordenamiento
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortOrder = $params['sort_order'] ?? 'desc';

        switch ($sortBy) {
            case 'price': // alias para el frontend
            case 'price_proposed':
                $query->orderBy('price_proposed', $sortOrder);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortOrder);
                break;
            default: // relevance
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Paginación
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 15;

        return $query->paginate($perPage, ['*'], 'page', $page)->toArray();
    }

    /**
     * Calcular el total de resultados
     */
    private function getTotalResults(array $results): int
    {
        $total = 0;
        foreach ($results as $result) {
            if (isset($result['total'])) {
                $total += $result['total'];
            }
        }
        return $total;
    }
}