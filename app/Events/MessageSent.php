<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    /**
     * Crear una nueva instancia del evento.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Definir el canal privado dinámico: chat.oferta.{offerId}
     */
    public function broadcastOn(): Channel
    {
        $offerId = $this->message->chat->service_offer_id;
        return new PrivateChannel("chat.offer.{$offerId}");
    }

    /**
     * Datos que el frontend recibirá en tiempo real.
     */
    public function broadcastWith(): array
    {
        return [
            'id'          => $this->message->id,
            'chat_id'     => $this->message->chat_id,
            'sender_id'   => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'message'     => $this->message->message,
            'type'        => $this->message->type,
            'media_url'   => $this->message->media_url,
            'media_type'  => $this->message->media_type,
            'media_name'  => $this->message->media_name,
            'metadata'    => $this->message->metadata,
            'seen_at'     => $this->message->seen_at?->toDateTimeString(),
            'created_at'  => $this->message->created_at->toDateTimeString(),
        ];
    }
}
