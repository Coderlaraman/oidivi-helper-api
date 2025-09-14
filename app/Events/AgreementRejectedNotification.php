<?php

namespace App\Events;

use App\Models\Agreement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AgreementRejectedNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Agreement
     */
    private Agreement $agreement;

    /**
     * @var int
     */
    private int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(Agreement $agreement, int $userId)
    {
        $this->agreement = $agreement;
        $this->userId = $userId;

        Log::info('AgreementRejectedNotification event created', [
            'agreement_id' => $agreement->id,
            'user_id' => $userId
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'type' => 'agreement_rejected',
            'agreement' => [
                'id' => $this->agreement->id,
                'service_request_id' => $this->agreement->service_request_id,
                'service_offer_id' => $this->agreement->service_offer_id,
                'status' => $this->agreement->status,
                'rejection_reason' => $this->agreement->rejection_reason,
                'created_at' => $this->agreement->created_at?->toIso8601String()
            ],
            'notification' => [
                'title' => __('notifications.types.agreement_rejected'),
                'message' => __('notifications.messages.agreement_rejected', [
                    'title' => $this->agreement->serviceRequest->title ?? ''
                ]),
                'action_url' => "/agreements/{$this->agreement->id}"
            ]
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'agreement.rejected';
    }
}