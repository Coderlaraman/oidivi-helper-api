<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The chat ID where the user is typing.
     *
     * @var int
     */
    public $chatId;

    /**
     * The user ID who is typing.
     *
     * @var int
     */
    public $userId;

    /**
     * Whether the user is typing or not.
     *
     * @var bool
     */
    public $isTyping;

    /**
     * Create a new event instance.
     */
    public function __construct(int $chatId, int $userId, bool $isTyping = true)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->isTyping = $isTyping;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Canal privado para el chat específico
        return [
            new PrivateChannel('chat.' . $this->chatId),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        // Obtener información básica del usuario
        $user = User::select('id', 'name', 'profile_photo_url')->find($this->userId);
        
        return [
            'chat_id' => $this->chatId,
            'user' => $user,
            'is_typing' => $this->isTyping,
            'timestamp' => now(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.typing';
    }
}