<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'reviewer_id',
        'reviewed_id',
        'service_request_id',
        'rating',
        'comment',
        'would_recommend'
    ];

    protected $casts = [
        'would_recommend' => 'boolean'
    ];

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

    // Scopes
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

    // Métodos estáticos para estadísticas
    public static function getAverageRating(?int $userId = null): float
    {
        $query = self::query();
        
        if ($userId) {
            $query->where('reviewed_id', $userId);
        }
        
        return round($query->avg('rating') ?? 0, 2);
    }

    public static function getRatingDistribution(?int $userId = null): array
    {
        $query = self::query();
        
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
        $query = self::query();
        
        if ($userId) {
            $query->where('reviewed_id', $userId);
        }
        
        $total = $query->count();
        if ($total === 0) return 0;
        
        $recommended = $query->clone()->where('would_recommend', true)->count();
        
        return round(($recommended / $total) * 100, 2);
    }
}
