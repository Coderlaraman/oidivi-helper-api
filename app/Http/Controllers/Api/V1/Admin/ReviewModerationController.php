<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserReviewResource;
use App\Models\Review;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReviewModerationController extends Controller
{
    use ApiResponseTrait;

    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Obtener reseñas pendientes de moderación
     */
    public function pending(Request $request): JsonResponse
    {
        try {
            $query = Review::with(['reviewer.profile', 'reviewed.profile', 'serviceRequest'])
                ->pending();
            
            // Aplicar filtros adicionales
            $this->applyModerationFilters($query, $request);
            
            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'asc'); // Más antiguas primero
            
            $query->orderBy($sortBy, $sortOrder);
            
            // Paginación
            $perPage = min($request->get('per_page', 20), 100);
            $reviews = $query->paginate($perPage);
            
            return $this->successResponse([
                'reviews' => UserReviewResource::collection($reviews),
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ],
                'stats' => $this->getModerationStats()
            ], 'Reseñas pendientes obtenidas exitosamente');
            
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener reseñas pendientes: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener todas las reseñas para moderación
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Review::with(['reviewer.profile', 'reviewed.profile', 'serviceRequest', 'moderator']);
            
            // Aplicar filtros
            $this->applyModerationFilters($query, $request);
            
            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $query->orderBy($sortBy, $sortOrder);
            
            // Paginación
            $perPage = min($request->get('per_page', 20), 100);
            $reviews = $query->paginate($perPage);
            
            return $this->successResponse([
                'reviews' => UserReviewResource::collection($reviews),
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ],
                'stats' => $this->getModerationStats()
            ], 'Reseñas obtenidas exitosamente');
            
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener reseñas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Aprobar una reseña
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500'
        ]);

        try {
            $review = Review::findOrFail($id);
            
            DB::beginTransaction();
            
            $review->approve(auth()->id(), $request->get('admin_notes'));
            
            // Cargar relaciones
            $review->load(['reviewer.profile', 'reviewed.profile', 'serviceRequest', 'moderator']);
            
            DB::commit();
            
            return $this->successResponse(
                new UserReviewResource($review),
                'Reseña aprobada exitosamente'
            );
            
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al aprobar la reseña: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Rechazar una reseña
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500'
        ]);

        try {
            $review = Review::findOrFail($id);
            
            DB::beginTransaction();
            
            $review->reject(auth()->id(), $request->get('admin_notes'));
            
            // Cargar relaciones
            $review->load(['reviewer.profile', 'reviewed.profile', 'serviceRequest', 'moderator']);
            
            DB::commit();
            
            return $this->successResponse(
                new UserReviewResource($review),
                'Reseña rechazada exitosamente'
            );
            
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al rechazar la reseña: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Marcar/desmarcar reseña como destacada
     */
    public function toggleFeatured(Request $request, $id): JsonResponse
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500'
        ]);

        try {
            $review = Review::findOrFail($id);
            
            // Solo se pueden destacar reseñas aprobadas
            if ($review->status !== Review::STATUS_APPROVED) {
                return $this->errorResponse('Solo se pueden destacar reseñas aprobadas', 400);
            }
            
            DB::beginTransaction();
            
            if ($review->is_featured) {
                $review->unfeature(auth()->id(), $request->get('admin_notes'));
                $message = 'Reseña removida de destacadas exitosamente';
            } else {
                $review->feature(auth()->id(), $request->get('admin_notes'));
                $message = 'Reseña marcada como destacada exitosamente';
            }
            
            // Cargar relaciones
            $review->load(['reviewer.profile', 'reviewed.profile', 'serviceRequest', 'moderator']);
            
            DB::commit();
            
            return $this->successResponse(
                new UserReviewResource($review),
                $message
            );
            
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al cambiar estado destacado: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Moderación en lote
     */
    public function bulkModerate(Request $request): JsonResponse
    {
        $request->validate([
            'review_ids' => 'required|array|min:1|max:50',
            'review_ids.*' => 'required|exists:reviews,id',
            'action' => ['required', Rule::in(['approve', 'reject', 'feature', 'unfeature'])],
            'admin_notes' => 'nullable|string|max:500'
        ]);

        try {
            $reviewIds = $request->get('review_ids');
            $action = $request->get('action');
            $adminNotes = $request->get('admin_notes');
            
            DB::beginTransaction();
            
            $reviews = Review::whereIn('id', $reviewIds)->get();
            $processed = 0;
            $errors = [];
            
            foreach ($reviews as $review) {
                try {
                    switch ($action) {
                        case 'approve':
                            $review->approve(auth()->id(), $adminNotes);
                            break;
                        case 'reject':
                            $review->reject(auth()->id(), $adminNotes);
                            break;
                        case 'feature':
                            if ($review->status === Review::STATUS_APPROVED) {
                                $review->feature(auth()->id(), $adminNotes);
                            } else {
                                $errors[] = "Reseña {$review->id}: Solo se pueden destacar reseñas aprobadas";
                                continue 2;
                            }
                            break;
                        case 'unfeature':
                            $review->unfeature(auth()->id(), $adminNotes);
                            break;
                    }
                    $processed++;
                } catch (Exception $e) {
                    $errors[] = "Reseña {$review->id}: {$e->getMessage()}";
                }
            }
            
            DB::commit();
            
            $message = "Procesadas {$processed} reseñas exitosamente";
            if (!empty($errors)) {
                $message .= ". Errores: " . implode(', ', $errors);
            }
            
            return $this->successResponse([
                'processed' => $processed,
                'total' => count($reviewIds),
                'errors' => $errors
            ], $message);
            
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error en moderación en lote: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener estadísticas de moderación
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->getModerationStats();
            
            return $this->successResponse($stats, 'Estadísticas de moderación obtenidas exitosamente');
            
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener historial de moderación de una reseña
     */
    public function history($id): JsonResponse
    {
        try {
            $review = Review::with(['moderator'])->findOrFail($id);
            
            $history = [
                'review_id' => $review->id,
                'current_status' => $review->status,
                'is_featured' => $review->is_featured,
                'moderated_at' => $review->moderated_at,
                'moderator' => $review->moderator ? [
                    'id' => $review->moderator->id,
                    'name' => $review->moderator->name,
                    'email' => $review->moderator->email
                ] : null,
                'admin_notes' => $review->admin_notes,
                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at
            ];
            
            return $this->successResponse($history, 'Historial obtenido exitosamente');
            
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener historial: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Aplicar filtros específicos de moderación
     */
    private function applyModerationFilters(Builder $query, Request $request): void
    {
        // Filtro por estado
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }
        
        // Filtro por calificación
        if ($request->has('rating')) {
            $query->where('rating', $request->get('rating'));
        }
        
        // Filtro por rango de calificación
        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->get('min_rating'));
        }
        
        if ($request->has('max_rating')) {
            $query->where('rating', '<=', $request->get('max_rating'));
        }
        
        // Filtro por reseñas destacadas
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }
        
        // Filtro por moderador
        if ($request->has('moderated_by')) {
            $query->where('moderated_by', $request->get('moderated_by'));
        }
        
        // Filtro por fecha de moderación
        if ($request->has('moderated_from')) {
            $query->where('moderated_at', '>=', $request->get('moderated_from'));
        }
        
        if ($request->has('moderated_to')) {
            $query->where('moderated_at', '<=', $request->get('moderated_to'));
        }
        
        // Filtro por fecha de creación
        if ($request->has('created_from')) {
            $query->where('created_at', '>=', $request->get('created_from'));
        }
        
        if ($request->has('created_to')) {
            $query->where('created_at', '<=', $request->get('created_to'));
        }
        
        // Filtro por reviewer
        if ($request->has('reviewer_id')) {
            $query->where('reviewer_id', $request->get('reviewer_id'));
        }
        
        // Filtro por reviewed
        if ($request->has('reviewed_id')) {
            $query->where('reviewed_id', $request->get('reviewed_id'));
        }
        
        // Filtro por votos útiles
        if ($request->has('min_helpful_votes')) {
            $query->where('helpful_votes', '>=', $request->get('min_helpful_votes'));
        }
        
        // Búsqueda en comentarios
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('comment', 'LIKE', "%{$search}%");
        }
    }

    /**
     * Obtener estadísticas de moderación
     */
    private function getModerationStats(): array
    {
        return [
            'total_reviews' => Review::count(),
            'pending_reviews' => Review::pending()->count(),
            'approved_reviews' => Review::approved()->count(),
            'rejected_reviews' => Review::rejected()->count(),
            'featured_reviews' => Review::featured()->count(),
            'reviews_today' => Review::whereDate('created_at', today())->count(),
            'reviews_this_week' => Review::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'reviews_this_month' => Review::whereMonth('created_at', now()->month)->count(),
            'average_rating' => round(Review::approved()->avg('rating'), 2),
            'total_helpful_votes' => Review::approved()->sum('helpful_votes'),
            'moderation_queue_age' => Review::pending()->oldest()->first()?->created_at?->diffForHumans() ?? 'N/A'
        ];
    }
}