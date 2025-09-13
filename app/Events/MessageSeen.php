<?php

namespace App\Events;

use App\Http\Resources\User\UserMessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSeen implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Canal: Se emite al canal privado de la oferta de servicio.
     */
    public function broadcastOn(): Channel
    {
        $this->message->loadMissing('chat');
        return new PrivateChannel("chat.offer.{$this->message->chat->service_offer_id}");
    }

    /**
     * Payload: Usamos el resource para asegurar un formato consistente.
     */
    public function broadcastWith(): array
    {
        $this->message->loadMissing('sender');
        return (new UserMessageResource($this->message))->resolve();
    }

    /**
     * Alias del evento: El frontend lo escuchará como '.message.seen'.
     */
    public function broadcastAs(): string
    {
        return 'message.seen';
    }
}