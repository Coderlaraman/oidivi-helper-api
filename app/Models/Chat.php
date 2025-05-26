<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Chat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_one',
        'user_two',
        'last_message_at',
    ];

    /**
     * Get the user one associated with the chat.
     */
    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one');
    }

    /**
     * Get the user two associated with the chat.
     */
    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two');
    }

    /**
     * Get the messages for the chat.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the service request associated with the chat.
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Check if a user is a participant in the chat.
     */
    public function isParticipant(User $user): bool
    {
        return $this->user_one === $user->id || $this->user_two === $user->id;
    }

    /**
     * Get the other participant in a one-to-one chat.
     */
    public function getOtherParticipant(User $user): ?User
    {
        if ($this->user_one === $user->id) {
            return $this->userTwo;
        } elseif ($this->user_two === $user->id) {
            return $this->userOne;
        }

        return null;
    }

    /**
     * Mark all unread messages in the chat as read for a specific user.
     */
    public function markAsRead(User $user): void
    {
        // Obtener todos los mensajes no leídos enviados al usuario
        $unreadMessages = $this->messages()
            ->where('receiver_id', $user->id)
            ->where('seen', false)
            ->get();

        // Marcar cada mensaje como leído y disparar evento
        foreach ($unreadMessages as $message) {
            $message->markAsSeen();
            event(new \App\Events\MessageSeen($message, $user));
        }
    }
}
