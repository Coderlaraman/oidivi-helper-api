<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'chat_id',
        'sender_id',
        'message',
        'type',
        'media_url',
        'media_type',
        'metadata',
        'seen_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'seen_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the chat that the message belongs to.
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Mark the message as seen.
     */
    public function markAsSeen(): void
    {
        if (!$this->seen_at) {
            $this->update(['seen_at' => now()]);
        }
    }

    /**
     * Check if the message has been seen.
     */
    public function isSeen(): bool
    {
        return $this->seen_at !== null;
    }

    /**
     * Scope a query to only include messages sent after a specific date.
     */
    public function scopeSentAfter($query, $date)
    {
        return $query->where('created_at', '>', $date);
    }

    /**
     * Scope a query to only include unseen messages.
     */
    public function scopeUnseen($query)
    {
        return $query->whereNull('seen_at');
    }
}
