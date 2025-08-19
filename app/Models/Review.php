<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewer_id',
        'reviewed_id',
        'service_request_id',
        'rating',
        'comment',
        'aspects',
        'aspects_ratings',
        'would_recommend',
        'status',
        'admin_notes',
        'moderated_at',
        'moderated_by',
        'is_featured',
        'helpful_votes'
    ];

    protected $casts = [
        'aspects' => 'array',
        'aspects_ratings' => 'array',
        'would_recommend' => 'boolean',
        'is_featured' => 'boolean',
        'moderated_at' => 'datetime',
        'helpful_votes' => 'integer'
    ];

    // Constantes para aspectos evaluables
    public const ASPECTS = [
        'punctuality' => 'Puntualidad',
        'professionalism' => 'Profesionalismo',
        'quality' => 'Calidad',
        'communication' => 'Comunicación'
    ];

    // Constantes para estados
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    // Relaciones
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewed(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_id');
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    // Scopes
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByRating(Builder $query, int $rating): Builder
    {
        return $query->where('rating', $rating);
    }

    public function scopeMinRating(Builder $query, int $minRating): Builder
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function scopeRecommended(Builder $query): Builder
    {
        return $query->where('would_recommend', true);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors y Mutators
    protected function averageAspectRating(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->aspects_ratings || empty($this->aspects_ratings)) {
                    return null;
                }
                
                $ratings = array_values($this->aspects_ratings);
                return round(array_sum($ratings) / count($ratings), 2);
            }
        );
    }

    protected function aspectsCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->aspects ? count($this->aspects) : 0
        );
    }

    // Métodos útiles
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    public function isModerated(): bool
    {
        return !is_null($this->moderated_at);
    }

    public function getAspectRating(string $aspect): ?int
    {
        return $this->aspects_ratings[$aspect] ?? null;
    }

    public function hasAspect(string $aspect): bool
    {
        return in_array($aspect, $this->aspects ?? []);
    }

    public function incrementHelpfulVotes(): void
    {
        $this->increment('helpful_votes');
    }

    public function decrementHelpfulVotes(): void
    {
        $this->decrement('helpful_votes');
    }

    public function approve(?int $moderatorId = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'moderated_at' => now(),
            'moderated_by' => $moderatorId
        ]);
    }

    public function reject(?int $moderatorId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'moderated_at' => now(),
            'moderated_by' => $moderatorId,
            'admin_notes' => $notes
        ]);
    }

    public function feature(): void
    {
        $this->update(['is_featured' => true]);
    }

    public function unfeature(): void
    {
        $this->update(['is_featured' => false]);
    }

    // Métodos estáticos para estadísticas
    public static function getAverageRating(?int $userId = null): float
    {
        $query = self::approved();
        
        if ($userId) {
            $query->where('reviewed_id', $userId);
        }
        
        return round($query->avg('rating') ?? 0, 2);
    }

    public static function getRatingDistribution(?int $userId = null): array
    {
        $query = self::approved();
        
        if ($userId) {
            $query->where('reviewed_id', $userId);
        }
        
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $query->clone()->where('rating', $i)->count();
        }
        
        return $distribution;
    }

    public static function getRecommendationPercentage(?int $userId = null): float
    {
        $query = self::approved();
        
        if ($userId) {
            $query->where('reviewed_id', $userId);
        }
        
        $total = $query->count();
        if ($total === 0) return 0;
        
        $recommended = $query->clone()->where('would_recommend', true)->count();
        
        return round(($recommended / $total) * 100, 2);
    }
}
