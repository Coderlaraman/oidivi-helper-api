<?php
// app/Models/UserStat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class UserStat extends Model
{
    protected $fillable = [
        'user_id',
        'completed_tasks',
        'active_services',
        'total_earnings',
        'rating',
    ];

    protected $casts = [
        'completed_tasks' => 'integer',
        'active_services' => 'integer',
        'total_earnings' => 'decimal:2',
        'rating' => 'decimal:2',
    ];

    /**
     * Relación inversa con el usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
