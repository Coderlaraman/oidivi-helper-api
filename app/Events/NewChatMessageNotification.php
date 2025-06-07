<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatMessageNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;
    public int $recipientId;

    /**
     * @param Message $message     El mensaje recién creado
     * @param int     $recipientId ID del usuario receptor
     */
    public function __construct(Message $message, int $recipientId)
    {
        $this->message     = $message;
        $this->recipientId = $recipientId;
    }

    /**
     * Indica el canal privado al que se va a emitir la notificación.
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.notifications.{$this->recipientId}");
    }

    /**
     * Datos que recibirá el frontend cuando escuche este evento.
     */
    public function broadcastWith(): array
    {
        return [
            'notification_id'     => null, // si quieres incluir el ID de tu tabla custom, remplázalo luego
            'type'                => \App\Constants\NotificationType::NEW_CHAT_MESSAGE,
            'timestamp'           => now()->toIso8601String(),
            'message_id'          => $this->message->id,
            'chat_id'             => $this->message->chat_id,
            'sender_id'           => $this->message->sender_id,
            'sender_name'         => $this->message->sender->name,
            'message_content'     => $this->message->message,
            'chat_type'           => $this->message->chat->type,
            'service_request_id'  => $this->message->chat->service_request_id,
            'service_offer_id'    => $this->message->chat->service_offer_id,
            // Si hay multimedia:
            'type_message'        => $this->message->type,
            'media_url'           => $this->message->media_url,
            'media_type'          => $this->message->media_type,
            'media_name'          => $this->message->media_name,
        ];
    }

    /**
     * Nombre del evento para que el frontend lo identifique.
     */
    public function broadcastAs(): string
    {
        return 'notification.new_chat_message';
    }
}
