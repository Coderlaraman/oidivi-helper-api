<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_request_id',
        'service_offer_id',
        'name',
        'type',
        'last_message_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_message_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the service request associated with the chat.
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }
    
    /**
     * Get the service offer associated with the chat.
     */
    public function serviceOffer(): BelongsTo
    {
        return $this->belongsTo(ServiceOffer::class);
    }

    /**
     * Get the messages for the chat.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the participants of the chat.
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants')
            ->withPivot(['is_admin', 'last_read_at'])
            ->withTimestamps();
    }

    /**
     * Check if a user is a participant in the chat.
     * @param User|int $user User object or user ID
     */
    public function isParticipant($user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;
        return $this->participants()->where('users.id', $userId)->exists();
    }

    /**
     * Add a participant to the chat.
     * @param User|int $user User object or user ID
     */
    public function addParticipant($user, bool $isAdmin = false): void
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        if (!$this->isParticipant($userId)) {
            $this->participants()->attach($userId, [
                'is_admin' => $isAdmin,
                'last_read_at' => now(),
            ]);
        }
    }

    /**
     * Remove a participant from the chat.
     * @param User|int $user User object or user ID
     */
    public function removeParticipant($user): void
    {
        $userId = $user instanceof User ? $user->id : $user;
        $this->participants()->detach($userId);
    }

    /**
     * Get the other participant in a direct chat.
     * Only applicable for direct chats (type = 'direct').
     * @param User|int $user User object or user ID
     */
    public function getOtherParticipant($user): ?User
    {
        if ($this->type !== 'direct') {
            return null;
        }

        $userId = $user instanceof User ? $user->id : $user;
        
        return $this->participants()
            ->where('users.id', '!=', $userId)
            ->first();
    }

    /**
     * Mark all unread messages in the chat as read for a specific user.
     * @param User|int $user User object or user ID
     * @return int Number of messages marked as read
     */
    public function markAsRead($user): int
    {
        $userId = $user instanceof User ? $user->id : $user;
        $userObj = $user instanceof User ? $user : User::find($userId);
        
        if (!$userObj) {
            return 0;
        }
        
        // Actualizar el timestamp de última lectura para el usuario
        $this->participants()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);

        // Disparar evento de mensajes leídos si es necesario
        $unreadMessages = $this->getUnreadMessagesForUser($userObj);
        $markedCount = 0;
        
        foreach ($unreadMessages as $message) {
            if ($message->sender_id !== $userId && !$message->seen_at) {
                $message->markAsSeen();
                event(new \App\Events\MessageSeen($message, $userObj));
                $markedCount++;
            }
        }
        
        return $markedCount;
    }

    /**
     * Get unread messages for a specific user.
     * @param User|int $user User object or user ID
     */
    public function getUnreadMessagesForUser($user): array
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        $participant = $this->participants()->where('users.id', $userId)->first();
        if (!$participant) {
            return [];
        }

        $lastReadAt = $participant->pivot->last_read_at;
        if (!$lastReadAt) {
            return $this->messages()->where('sender_id', '!=', $userId)->get()->all();
        }

        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where(function ($query) use ($lastReadAt) {
                $query->whereNull('seen_at')
                    ->orWhere('created_at', '>', $lastReadAt);
            })
            ->get()
            ->all();
    }

    /**
     * Get the count of unread messages for a specific user.
     * @param User|int $user User object or user ID
     */
    public function getUnreadCount($user): int
    {
        return count($this->getUnreadMessagesForUser($user));
    }
}
