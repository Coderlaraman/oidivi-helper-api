<?php

namespace App\Events;

use App\Constants\NotificationType;
use App\Http\Resources\User\UserMessageResource; // <--- USO CONSISTENTE
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

    public function __construct(Message $message, int $recipientId)
    {
        $this->message = $message;
        $this->recipientId = $recipientId;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.notifications.{$this->recipientId}");
    }

    public function broadcastWith(): array
    {
        $this->message->loadMissing('sender', 'chat.serviceOffer');

        return [
            // Sección para el TOAST
            'id' => 'chat-notif-' . $this->message->id,
            'type' => NotificationType::NEW_CHAT_MESSAGE,
            'timestamp' => now()->toIso8601String(),
            'title' => __('notifications.types.new_chat_message', ['sender' => $this->message->sender->name]),
            'message' => $this->message->message ?? __('messages.file_placeholder'),
            'action_url' => "/messages?offerId=" . $this->message->chat->service_offer_id,
            'sender_name' => $this->message->sender->name,
            'sender_photo_url' => $this->message->sender->profile_photo_url,

            // Sección para la ACTUALIZACIÓN DEL ESTADO en React (ChatContext)
            'chat_message' => (new UserMessageResource($this->message))->resolve(),

            // Datos extra para el frontend
            'chat_id' => $this->message->chat_id,
            'service_offer_id' => $this->message->chat->service_offer_id,
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.message.new';
    }
}
