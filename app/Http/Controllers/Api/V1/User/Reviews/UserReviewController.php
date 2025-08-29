<?php

namespace App\Http\Controllers\Api\V1\User\Reviews;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserReviewResource;
use App\Http\Requests\User\Review\StoreReviewRequest;
use App\Models\Review;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserReviewController extends Controller
{
    use ApiResponseTrait;

    /**
     * Obtener reseñas de un usuario
     */
    public function index(Request $request, $userId = null): JsonResponse
    {
        try {
            $userId = $userId ?? auth()->id();
            
            // Validar que el usuario existe
            $user = User::findOrFail($userId);
            
            $query = Review::with(['reviewer.profile', 'reviewed.profile'])
                ->where('reviewed_id', $userId)
                ->latest();
            
            // Filtro básico por calificación
            if ($request->has('rating')) {
                $query->where('rating', $request->get('rating'));
            }
            
            // Paginación
            $perPage = min($request->get('per_page', 15), 50);
            $reviews = $query->paginate($perPage);
            
            // Estadísticas básicas
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
            ], $reviews->total() > 0 ? 'Reseñas obtenidas exitosamente' : 'No hay reseñas disponibles');
            
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
            $query = Review::with(['reviewer.profile', 'reviewed.profile'])
                ->where('reviewer_id', auth()->id())
                ->latest();
            
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
            ], $reviews->total() > 0 ? 'Reseñas dadas obtenidas exitosamente' : 'No has dado ninguna reseña aún');
            
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
            $review->load(['reviewer.profile', 'reviewed.profile']);
            
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
            $review = Review::with(['reviewer.profile', 'reviewed.profile'])
                ->findOrFail($id);
            
            // Verificar permisos básicos (solo el reviewer o reviewed pueden ver)
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
     * Actualizar una reseña (solo el autor)
     */
    public function update(StoreReviewRequest $request, $id): JsonResponse
    {
        try {
            $review = Review::findOrFail($id);
            
            // Verificar permisos (solo el autor puede actualizar)
            if (!$this->canUpdateReview($review)) {
                return $this->errorResponse('No tienes permisos para actualizar esta reseña', 403);
            }
            
            DB::beginTransaction();
            
            $review->update($request->validated());
            
            // Cargar relaciones
            $review->load(['reviewer.profile', 'reviewed.profile']);
            
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
     * Eliminar una reseña (solo el autor)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $review = Review::findOrFail($id);
            
            // Verificar permisos (solo el autor puede eliminar)
            if (!$this->canDeleteReview($review)) {
                return $this->errorResponse('No tienes permisos para eliminar esta reseña', 403);
            }
            
            DB::beginTransaction();
            
            $review->delete();
            
            DB::commit();
            
            return $this->successResponse(null, 'Reseña eliminada exitosamente');
            
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al eliminar la reseña: ' . $e->getMessage(), 500);
        }
    }



    /**
     * Obtener estadísticas básicas de reseñas
     */
    public function stats(Request $request, $userId = null): JsonResponse
    {
        try {
            $userId = $userId ?? auth()->id();
            $stats = $this->getUserReviewStats($userId);
            
            return $this->successResponse($stats, 'Estadísticas obtenidas exitosamente');
            
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }







    /**
     * Obtener estadísticas básicas de reseñas de un usuario
     */
    private function getUserReviewStats($userId): array
    {
        $reviews = Review::where('reviewed_id', $userId);
        
        $totalReviews = $reviews->count();
        
        if ($totalReviews === 0) {
            return [
                'total_reviews' => 0,
                'average_rating' => 0,
                'recommendation_percentage' => 0,
                'rating_distribution' => [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                    5 => 0,
                ],
            ];
        }
        
        return [
            'total_reviews' => $totalReviews,
            'average_rating' => round($reviews->avg('rating'), 2),
            'recommendation_percentage' => round(($reviews->where('would_recommend', true)->count() / $totalReviews) * 100, 2),
            'rating_distribution' => Review::getRatingDistribution($userId),
        ];
    }



    /**
     * Verificar si el usuario puede ver la reseña
     */
    private function canViewReview(Review $review): bool
    {
        $user = auth()->user();
        
        return $user->id === $review->reviewer_id ||
               $user->id === $review->reviewed_id;
    }

    /**
     * Verificar si el usuario puede actualizar la reseña
     */
    private function canUpdateReview(Review $review): bool
    {
        $user = auth()->user();
        
        return $user->id === $review->reviewer_id;
    }

    /**
     * Verificar si el usuario puede eliminar la reseña
     */
    private function canDeleteReview(Review $review): bool
    {
        $user = auth()->user();
        
        return $user->id === $review->reviewer_id;
    }


}
