<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceRequests(): MorphToMany
    {
        return $this->morphedByMany(ServiceRequest::class, 'notifiable');
    }

    public function serviceOffers(): MorphToMany
    {
        return $this->morphedByMany(ServiceOffer::class, 'notifiable');
    }

    public function getIsReadAttribute(): bool
    {
        return !is_null($this->read_at);
    }

    public function markAsRead(): bool
    {
        return $this->update([
            'read_at' => Carbon::now(),
        ]);
    }
}
