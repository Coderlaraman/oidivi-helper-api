<?php

namespace App\Events;

use App\Models\Contract;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContractCancelledNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Contract $contract, public int $recipientUserId)
    {
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('user.' . $this->recipientUserId);
    }

    public function broadcastAs(): string
    {
        return 'contract.cancelled';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'contract_cancelled',
            'contract' => [
                'id' => $this->contract->id,
                'status' => $this->contract->status,
                'cancellation_reason' => $this->contract->cancellation_reason,
            ],
            'notification' => [
                'title' => __('notifications.types.contract_cancelled'),
                'message' => __('notifications.messages.contract_cancelled', [
                    'title' => $this->contract->serviceRequest?->title ?? ''
                ]),
                'action_url' => url("/contracts/{$this->contract->id}"),
            ],
        ];
    }
}