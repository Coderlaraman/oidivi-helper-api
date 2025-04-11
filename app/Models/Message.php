<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chat_id',
        'sender_id',
        'receiver_id',
        'message',
        'type',
        'media_url',
        'media_type',
        'seen',
        'metadata'
    ];

    protected $casts = [
        'seen' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * The types of messages that can be sent
     */
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_FILE = 'file';
    const TYPE_LOCATION = 'location';
    const TYPE_SYSTEM = 'system';

    /**
     * Get the chat this message belongs to
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the user who sent this message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who should receive this message
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the service request associated with this message
     */
    public function serviceRequest(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if the message has been seen
     */
    public function isSeen(): bool
    {
        return $this->seen;
    }

    /**
     * Mark the message as seen
     */
    public function markAsSeen(): void
    {
        if (!$this->seen) {
            $this->update(['seen' => true]);
            event(new \App\Events\MessageSeen($this));
        }
    }

    /**
     * Check if the message has media attached
     */
    public function hasMedia(): bool
    {
        return !empty($this->media_url);
    }

    /**
     * Get the media type of the message
     */
    public function getMediaType(): ?string
    {
        return $this->media_type;
    }

    /**
     * Get the media URL of the message
     */
    public function getMediaUrl(): ?string
    {
        return $this->media_url;
    }

    /**
     * Scope a query to only include unread messages
     */
    public function scopeUnread($query)
    {
        return $query->where('seen', false);
    }

    /**
     * Scope a query to only include messages of a specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function isSystemMessage(): bool
    {
        return $this->type === self::TYPE_SYSTEM;
    }
}
