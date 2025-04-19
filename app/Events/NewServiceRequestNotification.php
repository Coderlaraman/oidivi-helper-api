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
     * Create a new event instance.
     */
    public function __construct(ServiceRequest $serviceRequest, array $userIds)
    {
        $this->serviceRequest = $serviceRequest;
        $this->userIds = array_unique($userIds);

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
                    'title' => 'New Service Request',
                    'message' => "A new service request has been published that matches your skills: {$this->serviceRequest->title}",
                    'action_url' => "/service-requests/{$this->serviceRequest->slug}"
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
} 