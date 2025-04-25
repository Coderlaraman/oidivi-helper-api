<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $serviceRequest = $this->serviceRequests->first();

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'title' => $this->title,
            'title_translated' => __('notifications.types.' . $this->type),
            'message' => $this->message,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'service_request' => $serviceRequest ? [
                'id' => $serviceRequest->id,
                'title' => $serviceRequest->title,
                'slug' => $serviceRequest->slug,
                'description' => $serviceRequest->description,
                'budget' => $serviceRequest->budget,
                'priority' => $serviceRequest->priority,
                'service_type' => $serviceRequest->service_type,
                'status' => $serviceRequest->status,
                'created_at' => $serviceRequest->created_at->toIso8601String(),
            ] : null,
            'notification' => [
                'title' => $this->title,
                'message' => $this->message,
                'action_url' => $serviceRequest ? "/service-requests/{$serviceRequest->id}" : null,
            ],
        ];
    }
}

