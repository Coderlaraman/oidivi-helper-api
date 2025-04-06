<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SomethingHappened implements ShouldBroadcast
{
    public function broadcastOn(): Channel
    {
        return new Channel('public-channel');
    }

    public function broadcastAs(): string
    {
        return 'something.happened';
    }

    public function broadcastWith(): array
    {
        return ['message' => '¡Hola desde Laravel con Reverb!'];
    }
}
