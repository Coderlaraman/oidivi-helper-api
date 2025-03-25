<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'reviewer' => new ClientProfileResource($this->whenLoaded('reviewer')),
            'reviewed' => new ClientProfileResource($this->whenLoaded('reviewed')),
            'service_request' => new ClientServiceRequestResource($this->whenLoaded('serviceRequest')),
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
