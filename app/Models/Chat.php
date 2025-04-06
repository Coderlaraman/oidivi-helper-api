<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one',
        'user_two',
        'service_request_id',
        'last_message_at',
        'is_group',
        'name',
        'description'
    ];

    protected $casts = [
        'is_group' => 'boolean',
        'last_message_at' => 'datetime',
    ];

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
        if ($this->is_group) {
            return null;
        }
        
        return $this->user_one == $user->id ? $this->userTwo : $this->userOne;
    }

    /**
     * Get unread messages count for a specific user
     */
    public function getUnreadCount(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('seen', false)
            ->count();
    }

    /**
     * Mark all messages as read for a specific user
     */
    public function markAsRead(User $user): void
    {
        $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('seen', false)
            ->update(['seen' => true]);
    }
}
