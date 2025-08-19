<?php

namespace App\Http\Controllers\Api\V1\User\Reviews;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserReviewResource;
use App\Http\Requests\User\Review\StoreReviewRequest;
use App\Models\Review;
use App\Models\User;
use App\Models\ServiceRequest;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UserReviewController extends Controller
{
    use ApiResponseTrait;

    /**
     * Obtener reseñas de un usuario con filtros avanzados
     */
    public function index(Request $request, $userId = null): JsonResponse
    {
        try {
            $userId = $userId ?? auth()->id();
            
            // Validar que el usuario existe
            $user = User::findOrFail($userId);
            
            $query = Review::with(['reviewer.profile', 'reviewed.profile', 'serviceRequest'])
                ->where('reviewed_id', $userId);
            
            // Aplicar filtros
            $this->applyFilters($query, $request);
            
            // Aplicar ordenamiento
            $this->applySorting($query, $request);
            
            // Paginación
            $perPage = min($request->get('per_page', 15), 50);
            $reviews = $query->paginate($perPage);
            
            // Estadísticas del usuario
            $stats = $this->getUserReviewStats($userId);
            
            return $this->successResponse([
                'reviews' => UserReviewResource::collection($reviews),
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ],
                'stats' => $stats
            ], 'Reseñas obtenidas exitosamente');
            
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener las reseñas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener reseñas dadas por un usuario
     */
    public function given(Request $request): JsonResponse
    {
        try {
            $query = Review::with(['reviewer.profile', 'reviewed.profile', 'serviceRequest'])
                ->where('reviewer_id', auth()->id());
            
            // Aplicar filtros
            $this->applyFilters($query, $request);
            
            // Aplicar ordenamiento
            $this->applySorting($query, $request);
            
            // Paginación
            $perPage = min($request->get('per_page', 15), 50);
            $reviews = $query->paginate($perPage);
            
            return $this->successResponse([
                'reviews' => UserReviewResource::collection($reviews),
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ]
            ], 'Reseñas dadas obtenidas exitosamente');
            
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener las reseñas dadas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear una nueva reseña
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $review = Review::create($request->validated());
            
            // Cargar relaciones
            $review->load(['reviewer.profile', 'reviewed.profile', 'serviceRequest']);
            
            // Limpiar cache de estadísticas
            $this->clearUserStatsCache($review->reviewed_id);
            
            DB::commit();
            
            return $this->successResponse(
                new UserReviewResource($review),
                'Reseña creada exitosamente',
                201
            );
            
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al crear la reseña: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener una reseña específica
     */
    public function show($id): JsonResponse
    {
        try {
            $review = Review::with(['reviewer.profile', 'reviewed.profile', 'serviceRequest', 'moderator'])
                ->findOrFail($id);
            
            // Verificar permisos (solo el reviewer, reviewed o admin pueden ver)
            if (!$this->canViewReview($review)) {
                return $this->errorResponse('No tienes permisos para ver esta reseña', 403);
            }
            
            return $this->successResponse(
                new UserReviewResource($review),
                'Reseña obtenida exitosamente'
            );
            
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener la reseña: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar una reseña (solo el autor o admin)
     */
    public function update(StoreReviewRequest $request, $id): JsonResponse
    {
        try {
            $review = Review::findOrFail($id);
            
            // Verificar permisos
            if (!$this->canUpdateReview($review)) {
                return $this->errorResponse('No tienes permisos para actualizar esta reseña', 403);
            }
            
            DB::beginTransaction();
            
            $review->update($request->validated());
            
            // Si es admin actualizando, registrar moderación
            if (auth()->user()->hasRole('admin') && $request->has(['status', 'admin_notes'])) {
                $review->update([
                    'moderated_at' => now(),
                    'moderated_by' => auth()->id()
                ]);
            }
            
            // Cargar relaciones
            $review->load(['reviewer.profile', 'reviewed.profile', 'serviceRequest']);
            
            // Limpiar cache
            $this->clearUserStatsCache($review->reviewed_id);
            
            DB::commit();
            
            return $this->successResponse(
                new UserReviewResource($review),
                'Reseña actualizada exitosamente'
            );
            
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al actualizar la reseña: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar una reseña (solo el autor o admin)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $review = Review::findOrFail($id);
            
            // Verificar permisos
            if (!$this->canDeleteReview($review)) {
                return $this->errorResponse('No tienes permisos para eliminar esta reseña', 403);
            }
            
            DB::beginTransaction();
            
            $reviewedId = $review->reviewed_id;
            $review->delete();
            
            // Limpiar cache
            $this->clearUserStatsCache($reviewedId);
            
            DB::commit();
            
            return $this->successResponse(null, 'Reseña eliminada exitosamente');
            
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al eliminar la reseña: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Marcar reseña como útil
     */
    public function markHelpful($id): JsonResponse
    {
        try {
            $review = Review::findOrFail($id);
            
            // No se puede marcar como útil su propia reseña
            if ($review->reviewer_id === auth()->id()) {
                return $this->errorResponse('No puedes marcar como útil tu propia reseña', 400);
            }
            
            $review->increment('helpful_votes');
            
            return $this->successResponse(
                ['helpful_votes' => $review->helpful_votes],
                'Reseña marcada como útil'
            );
            
        } catch (Exception $e) {
            return $this->errorResponse('Error al marcar la reseña como útil: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener estadísticas generales de reseñas
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $userId = $request->get('user_id', auth()->id());
            $stats = $this->getUserReviewStats($userId);
            
            return $this->successResponse($stats, 'Estadísticas obtenidas exitosamente');
            
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Buscar reseñas
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q');
            
            if (empty($query)) {
                return $this->errorResponse('Parámetro de búsqueda requerido', 400);
            }
            
            $reviews = Review::with(['reviewer.profile', 'reviewed.profile', 'serviceRequest'])
                ->where('comment', 'LIKE', "%{$query}%")
                ->approved()
                ->latest()
                ->paginate(15);
            
            return $this->successResponse([
                'reviews' => UserReviewResource::collection($reviews),
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ]
            ], 'Búsqueda completada exitosamente');
            
        } catch (Exception $e) {
            return $this->errorResponse('Error en la búsqueda: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Aplicar filtros a la consulta
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        // Filtro por calificación
        if ($request->has('rating')) {
            $query->byRating($request->get('rating'));
        }
        
        // Filtro por rango de calificación
        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->get('min_rating'));
        }
        
        if ($request->has('max_rating')) {
            $query->where('rating', '<=', $request->get('max_rating'));
        }
        
        // Filtro por estado
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        } else {
            // Por defecto, solo mostrar aprobadas
            $query->approved();
        }
        
        // Filtro por recomendación
        if ($request->has('would_recommend')) {
            $query->where('would_recommend', $request->boolean('would_recommend'));
        }
        
        // Filtro por aspectos
        if ($request->has('aspects')) {
            $aspects = is_array($request->get('aspects')) 
                ? $request->get('aspects') 
                : explode(',', $request->get('aspects'));
            
            foreach ($aspects as $aspect) {
                $query->whereJsonContains('aspects', $aspect);
            }
        }
        
        // Filtro por fecha
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->get('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->get('date_to'));
        }
        
        // Filtro por reseñas destacadas
        if ($request->boolean('featured_only')) {
            $query->featured();
        }
        
        // Filtro por reviewer
        if ($request->has('reviewer_id')) {
            $query->where('reviewer_id', $request->get('reviewer_id'));
        }
    }

    /**
     * Aplicar ordenamiento a la consulta
     */
    private function applySorting(Builder $query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        switch ($sortBy) {
            case 'rating':
                $query->orderBy('rating', $sortOrder);
                break;
            case 'helpful_votes':
                $query->orderBy('helpful_votes', $sortOrder);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }
    }

    /**
     * Obtener estadísticas de reseñas de un usuario
     */
    private function getUserReviewStats($userId): array
    {
        return Cache::remember("user_review_stats_{$userId}", 3600, function () use ($userId) {
            $reviews = Review::where('reviewed_id', $userId)->approved();
            
            $totalReviews = $reviews->count();
            
            if ($totalReviews === 0) {
                return [
                    'total_reviews' => 0,
                    'average_rating' => 0,
                    'rating_distribution' => [],
                    'recommendation_percentage' => 0,
                    'aspects_average' => [],
                    'total_helpful_votes' => 0
                ];
            }
            
            return [
                'total_reviews' => $totalReviews,
                'average_rating' => round($reviews->avg('rating'), 2),
                'rating_distribution' => Review::getRatingDistribution($userId),
                'recommendation_percentage' => Review::getRecommendationPercentage($userId),
                'aspects_average' => $this->getAspectsAverage($userId),
                'total_helpful_votes' => $reviews->sum('helpful_votes')
            ];
        });
    }

    /**
     * Obtener promedio de aspectos
     */
    private function getAspectsAverage($userId): array
    {
        $reviews = Review::where('reviewed_id', $userId)
            ->approved()
            ->whereNotNull('aspects_ratings')
            ->get();
        
        $aspectsSum = [];
        $aspectsCount = [];
        
        foreach ($reviews as $review) {
            if ($review->aspects_ratings) {
                foreach ($review->aspects_ratings as $aspect => $rating) {
                    $aspectsSum[$aspect] = ($aspectsSum[$aspect] ?? 0) + $rating;
                    $aspectsCount[$aspect] = ($aspectsCount[$aspect] ?? 0) + 1;
                }
            }
        }
        
        $aspectsAverage = [];
        foreach ($aspectsSum as $aspect => $sum) {
            $aspectsAverage[$aspect] = round($sum / $aspectsCount[$aspect], 2);
        }
        
        return $aspectsAverage;
    }

    /**
     * Verificar si el usuario puede ver la reseña
     */
    private function canViewReview(Review $review): bool
    {
        $user = auth()->user();
        
        return $user->id === $review->reviewer_id ||
               $user->id === $review->reviewed_id ||
               $user->hasRole('admin') ||
               $review->status === Review::STATUS_APPROVED;
    }

    /**
     * Verificar si el usuario puede actualizar la reseña
     */
    private function canUpdateReview(Review $review): bool
    {
        $user = auth()->user();
        
        return $user->id === $review->reviewer_id || $user->hasRole('admin');
    }

    /**
     * Verificar si el usuario puede eliminar la reseña
     */
    private function canDeleteReview(Review $review): bool
    {
        $user = auth()->user();
        
        return $user->id === $review->reviewer_id || $user->hasRole('admin');
    }

    /**
     * Limpiar cache de estadísticas del usuario
     */
    private function clearUserStatsCache($userId): void
    {
        Cache::forget("user_review_stats_{$userId}");
    }
}
