<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data ?? [],
            'read_at' => $this->read_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'time_ago' => $this->created_at->diffForHumans(),
            'is_read' => $this->is_read,
            'service_request' => $this->whenLoaded('serviceRequest', [
                'id' => $this->serviceRequest?->id,
                'title' => $this->serviceRequest?->title,
                'status' => $this->serviceRequest?->status,
                'slug' => $this->serviceRequest?->slug,
            ]),
            'user' => $this->whenLoaded('user', [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'profile_photo_url' => $this->user?->profile_photo_url,
            ]),
            'links' => [
                'mark_as_read' => "/api/v1/user/notifications/{$this->id}/read",
                'delete' => "/api/v1/user/notifications/{$this->id}",
                'service_request' => $this->serviceRequest 
                    ? "/api/v1/user/service-requests/{$this->serviceRequest->id}"
                    : null,
            ]
        ];
    }
} 