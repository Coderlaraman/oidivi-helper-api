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

class NewServiceOfferNotification implements ShouldBroadcast
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

        Log::info('NewServiceOfferNotification event created', [
            'service_offer_id' => $serviceOffer->id,
            'service_request_id' => $serviceOffer->service_request_id,
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
            $data = [
                'id' => uniqid('notification_'),
                'type' => 'new_offer',
                'timestamp' => now()->toIso8601String(),
                'service_offer' => [
                    'id' => $this->serviceOffer->id,
                    'price_proposed' => $this->serviceOffer->price_proposed,
                    'estimated_time' => $this->serviceOffer->estimated_time,
                    'status' => $this->serviceOffer->status,
                    'created_at' => $this->serviceOffer->created_at->toIso8601String(),
                    'user' => [
                        'id' => $this->serviceOffer->user->id,
                        'name' => $this->serviceOffer->user->name,
                        'profile_photo_url' => $this->serviceOffer->user->profile_photo_url
                    ],
                    'service_request' => [
                        'id' => $this->serviceOffer->serviceRequest->id,
                        'title' => $this->serviceOffer->serviceRequest->title,
                        'slug' => $this->serviceOffer->serviceRequest->slug
                    ]
                ],
                'notification' => [
                    'title' => __('messages.service_offers.notifications.new_offer_title'),
                    'message' => __('messages.service_offers.notifications.new_offer_message', [
                        'title' => $this->serviceOffer->serviceRequest->title
                    ]),
                    'action_url' => \App\Models\ServiceOffer::getNotificationActionUrl(
                        \App\Constants\NotificationType::NEW_OFFER,
                        $this->serviceOffer->serviceRequest->id,
                        $this->serviceOffer->id
                    )
                ]
            ];

            Log::debug('Broadcasting offer notification data', [
                'notification_id' => $data['id'],
                'service_offer_id' => $this->serviceOffer->id
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('Error preparing offer broadcast data', [
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
        return 'service.offer.notification';
    }
}