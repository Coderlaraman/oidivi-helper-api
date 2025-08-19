<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Review;

class UserReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            // Información básica
            'id' => $this->id,
            'reviewer' => new UserProfileResource($this->whenLoaded('reviewer')),
            'reviewed' => new UserProfileResource($this->whenLoaded('reviewed')),
            'service_request' => new UserServiceRequestResource($this->whenLoaded('serviceRequest')),
            
            // Calificación y comentario
            'rating' => $this->rating,
            'comment' => $this->comment,
            
            // Nuevos campos de aspectos
            'aspects' => $this->aspects,
            'aspects_ratings' => $this->aspects_ratings,
            'aspects_average' => $this->when($this->aspects_ratings, function () {
                return $this->averageAspectRating;
            }),
            'aspects_count' => $this->when($this->aspects, function () {
                return $this->aspectsCount;
            }),
            
            // Recomendación
            'would_recommend' => $this->would_recommend,
            
            // Estado y moderación
            'status' => $this->status,
            'status_label' => $this->when($this->status, function () {
                return [
                    Review::STATUS_PENDING => 'Pendiente',
                    Review::STATUS_APPROVED => 'Aprobada',
                    Review::STATUS_REJECTED => 'Rechazada'
                ][$this->status] ?? 'Desconocido';
            }),
            
            // Información de moderación (solo para admins o involucrados)
            'admin_notes' => $this->when(
                $this->shouldShowModerationInfo($request),
                $this->admin_notes
            ),
            'moderated_at' => $this->when(
                $this->shouldShowModerationInfo($request),
                $this->moderated_at
            ),
            'moderator' => $this->when(
                $this->shouldShowModerationInfo($request) && $this->relationLoaded('moderator'),
                new UserProfileResource($this->moderator)
            ),
            
            // Características especiales
            'is_featured' => $this->is_featured,
            'helpful_votes' => $this->helpful_votes,
            
            // Información de aspectos detallada
            'aspects_details' => $this->when($this->aspects && $this->aspects_ratings, function () {
                $details = [];
                foreach ($this->aspects as $aspect) {
                    $details[] = [
                        'aspect' => $aspect,
                        'aspect_label' => Review::ASPECTS[$aspect] ?? $aspect,
                        'rating' => $this->aspects_ratings[$aspect] ?? null
                    ];
                }
                return $details;
            }),
            
            // Metadatos útiles
            'can_edit' => $this->when(auth()->check(), function () {
                return $this->canBeEditedBy(auth()->user());
            }),
            'can_delete' => $this->when(auth()->check(), function () {
                return $this->canBeDeletedBy(auth()->user());
            }),
            'can_moderate' => $this->when(auth()->check(), function () {
                return auth()->user()->hasRole('admin');
            }),
            
            // Fechas
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_human' => $this->created_at?->diffForHumans(),
            'updated_at_human' => $this->updated_at?->diffForHumans(),
            
            // Información adicional contextual
            'is_recent' => $this->created_at?->isAfter(now()->subDays(7)),
            'is_high_rating' => $this->rating >= 4,
            'is_low_rating' => $this->rating <= 2,
            'has_aspects' => !empty($this->aspects),
            'is_recommended' => $this->would_recommend === true,
            'is_helpful' => $this->helpful_votes > 0,
            
            // Estadísticas del reviewer (opcional, para contexto)
            'reviewer_stats' => $this->when(
                $this->relationLoaded('reviewer') && $request->get('include_reviewer_stats'),
                function () {
                    return [
                        'total_reviews_given' => $this->reviewer->reviewsGiven()->count(),
                        'average_rating_given' => round($this->reviewer->reviewsGiven()->avg('rating'), 2),
                        'is_verified_reviewer' => $this->reviewer->reviewsGiven()->count() >= 5
                    ];
                }
            )
        ];
    }

    /**
     * Determinar si se debe mostrar información de moderación
     */
    private function shouldShowModerationInfo($request): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();
        
        // Admins siempre pueden ver
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // El reviewer y reviewed pueden ver si hay moderación
        return ($user->id === $this->reviewer_id || $user->id === $this->reviewed_id) 
               && ($this->moderated_at || $this->admin_notes);
    }
}
