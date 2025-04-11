<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_one',
        'user_two',
        'service_request_id',
        'type',
        'status',
        'last_message_at',
        'metadata'
    ];

    protected $casts = [
        'is_group' => 'boolean',
        'last_message_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Tipos de chat
    const TYPE_NEGOTIATION = 'negotiation';
    const TYPE_SUPPORT = 'support';

    // Estados del chat
    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_CLOSED = 'closed';

    /**
     * Get all messages in this chat
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the first user in the chat
     */
    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one');
    }

    /**
     * Get the second user in the chat
     */
    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two');
    }

    /**
     * Get the service request associated with this chat
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Get all participants in this chat
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants', 'chat_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Check if a user is a participant in this chat
     */
    public function isParticipant(User $user): bool
    {
        if (!$this->is_group) {
            return $this->user_one == $user->id || $this->user_two == $user->id;
        }
        
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the other participant in a one-on-one chat
     */
    public function getOtherParticipant(User $user): ?User
    {
        if ($this->user_one === $user->id) {
            return $this->userTwo;
        }
        if ($this->user_two === $user->id) {
            return $this->userOne;
        }
        return null;
    }

    /**
     * Get unread messages count for a specific user
     */
    public function getUnreadCount(User $user): int
    {
        return $this->messages()
            ->where('receiver_id', $user->id)
            ->where('seen', false)
            ->count();
    }

    /**
     * Mark all messages as read for a specific user
     */
    public function markAsRead(User $user): void
    {
        $this->messages()
            ->where('receiver_id', $user->id)
            ->where('seen', false)
            ->update(['seen' => true]);
    }

    public function isNegotiationChat(): bool
    {
        return $this->type === self::TYPE_NEGOTIATION;
    }

    public function isSupportChat(): bool
    {
        return $this->type === self::TYPE_SUPPORT;
    }
}
