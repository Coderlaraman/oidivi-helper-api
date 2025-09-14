<?php

namespace App\Events;

use App\Models\Agreement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgreementCancelledNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Agreement $agreement, public int $recipientUserId)
    {
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('user.' . $this->recipientUserId);
    }

    public function broadcastAs(): string
    {
        return 'agreement.cancelled';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'agreement_cancelled',
            'agreement' => [
                'id' => $this->agreement->id,
                'status' => $this->agreement->status,
                'cancellation_reason' => $this->agreement->cancellation_reason,
            ],
            'notification' => [
                'title' => __('notifications.types.agreement_cancelled'),
                'message' => __('notifications.messages.agreement_cancelled', [
                    'title' => $this->agreement->serviceRequest?->title ?? ''
                ]),
                'action_url' => url("/agreements/{$this->agreement->id}"),
            ],
        ];
    }
}