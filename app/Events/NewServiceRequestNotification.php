<?php

namespace App\Events;

use App\Models\ServiceRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Constants\NotificationType;
use App\Models\ServiceOffer;

class NewServiceRequestNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var ServiceRequest
     */
    private ServiceRequest $serviceRequest;

    /**
     * @var array
     */
    private array $userIds;

    /**
     * @var array
     */
    private array $notificationData;

    /**
     * Create a new event instance.
     */
    public function __construct(ServiceRequest $serviceRequest, array $userIds, array $notificationData)
    {
        $this->serviceRequest = $serviceRequest;
        $this->userIds = array_unique($userIds);
        $this->notificationData = $notificationData;

        Log::info('NewServiceRequestNotification event created', [
            'service_request_id' => $serviceRequest->id,
            'user_count' => count($this->userIds)
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<Channel>
     */
    public function broadcastOn(): array
    {
        try {
            $channels = array_map(function ($userId) {
                return new PrivateChannel("user.notifications.{$userId}");
            }, $this->userIds);

            Log::debug('Broadcasting notification to channels', [
                'channels' => array_map(fn($channel) => $channel->name, $channels),
                'service_request_id' => $this->serviceRequest->id
            ]);

            return $channels;
        } catch (\Exception $e) {
            Log::error('Error creating broadcast channels', [
                'error' => $e->getMessage(),
                'service_request_id' => $this->serviceRequest->id
            ]);
            return [];
        }
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
                'type' => 'new_service_request',
                'timestamp' => now()->toIso8601String(),
                'service_request' => [
                    'id' => $this->serviceRequest->id,
                    'title' => $this->serviceRequest->title,
                    'slug' => $this->serviceRequest->slug,
                    'description' => $this->serviceRequest->description,
                    'budget' => $this->serviceRequest->budget,
                    'priority' => $this->serviceRequest->priority,
                    'service_type' => $this->serviceRequest->service_type,
                    'created_at' => $this->serviceRequest->created_at->toIso8601String()
                ],
                'notification' => [
                    'title' => __('notifications.types.new_service_request'),
                    'message' => __('notifications.messages.new_service_request', [
                        'title' => $this->serviceRequest->title
                    ]),
                    'action_url' => ServiceOffer::getNotificationActionUrl(
                        'new_service_request',
                        $this->serviceRequest->id
                    )
                ]
            ];

            Log::debug('Broadcasting notification data', [
                'notification_id' => $data['id'],
                'service_request_id' => $this->serviceRequest->id
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('Error preparing broadcast data', [
                'error' => $e->getMessage(),
                'service_request_id' => $this->serviceRequest->id
            ]);
            return [];
        }
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'service.request.notification';
    }

    public function notifyMatchingUsers(): void
    {
        try {
            $matchingUsers = $this->getMatchingUsers();
            
            if ($matchingUsers->isEmpty()) {
                return;
            }

            $notification = $this->createNotification(
                userIds: $matchingUsers->pluck('id')->toArray(),
                type: NotificationType::NEW_SERVICE_REQUEST,
                title: __('notifications.types.new_service_request'),
                message: __('notifications.messages.new_service_request', [
                    'title' => $this->title
                ])
            );

            event(new NewServiceRequestNotification(
                serviceRequest: $this,
                userIds: $matchingUsers->pluck('id')->toArray(),
                notificationData: [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'notifiable' => [
                        'type' => 'service_request',
                        'data' => [
                            'id' => $this->id,
                            'title' => $this->title,
                            'slug' => $this->slug
                        ]
                    ]
                ]
            ));
        } catch (\Exception $e) {
            Log::error('Error notifying matching users', [
                'error' => $e->getMessage(),
                'service_request_id' => $this->id
            ]);
        }
    }
} 