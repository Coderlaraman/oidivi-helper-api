<?php

namespace App\Events;

use App\Models\Contract;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ContractRejectedNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Contract
     */
    private Contract $contract;

    /**
     * @var int
     */
    private int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(Contract $contract, int $userId)
    {
        $this->contract = $contract;
        $this->userId = $userId;

        Log::info('ContractRejectedNotification event created', [
            'contract_id' => $contract->id,
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
            'type' => 'contract_rejected',
            'contract' => [
                'id' => $this->contract->id,
                'service_request_id' => $this->contract->service_request_id,
                'service_offer_id' => $this->contract->service_offer_id,
                'status' => $this->contract->status,
                'rejection_reason' => $this->contract->rejection_reason,
                'created_at' => $this->contract->created_at?->toIso8601String()
            ],
            'notification' => [
                'title' => __('notifications.types.contract_rejected'),
                'message' => __('notifications.messages.contract_rejected', [
                    'title' => $this->contract->serviceRequest->title ?? ''
                ]),
                'action_url' => "/contracts/{$this->contract->id}"
            ]
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'contract.rejected';
    }
}