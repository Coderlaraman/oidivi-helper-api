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

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): Channel
    {
        $this->message->loadMissing('chat');
        return new PrivateChannel("chat.offer.{$this->message->chat->service_offer_id}");
    }

    public function broadcastWith(): array
    {
        // Aseguramos que la relación 'sender' esté cargada antes de pasarla al resource
        $this->message->loadMissing('sender');
        return (new UserMessageResource($this->message))->resolve();
    }
}
