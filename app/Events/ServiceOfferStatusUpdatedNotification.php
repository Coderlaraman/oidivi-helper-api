<?php

namespace App\Events;

use App\Models\ServiceOffer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ServiceOfferStatusUpdatedNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var ServiceOffer
     */
    private ServiceOffer $serviceOffer;

    /**
     * @var int
     */
    private int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(ServiceOffer $serviceOffer, int $userId)
    {
        $this->serviceOffer = $serviceOffer;
        $this->userId = $userId;

        Log::info('ServiceOfferStatusUpdatedNotification event created', [
            'service_offer_id' => $serviceOffer->id,
            'status' => $serviceOffer->status,
            'user_id' => $userId
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.notifications.{$this->userId}")
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        try {
            $statusMessages = [
                'accepted' => 'Your offer has been accepted',
                'rejected' => 'Your offer has been rejected',
                'pending' => 'Your offer is pending review'
            ];

            $data = [
                'id' => uniqid('notification_'),
                'type' => 'service_offer_status_updated',
                'timestamp' => now()->toIso8601String(),
                'service_offer' => [
                    'id' => $this->serviceOffer->id,
                    'status' => $this->serviceOffer->status,
                    'updated_at' => $this->serviceOffer->updated_at->toIso8601String(),
                    'service_request' => [
                        'id' => $this->serviceOffer->serviceRequest->id,
                        'title' => $this->serviceOffer->serviceRequest->title,
                        'slug' => $this->serviceOffer->serviceRequest->slug
                    ]
                ],
                'notification' => [
                    'title' => __('messages.service_offers.notifications.status_update_title'),
                    'message' => __('messages.service_offers.notifications.status_update_message', [
                        'title' => $this->serviceOffer->serviceRequest->title,
                        'status' => $this->serviceOffer->status
                    ]),
                    'action_url' => "/service-requests/{$this->serviceOffer->serviceRequest->slug}/offers/{$this->serviceOffer->id}"
                ]
            ];

            Log::debug('Broadcasting offer status update notification data', [
                'notification_id' => $data['id'],
                'service_offer_id' => $this->serviceOffer->id,
                'status' => $this->serviceOffer->status
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('Error preparing offer status update broadcast data', [
                'error' => $e->getMessage(),
                'service_offer_id' => $this->serviceOffer->id
            ]);
            return [];
        }
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'service.offer.status.notification';
    }
} 