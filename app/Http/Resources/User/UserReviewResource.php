<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'reviewer' => new UserProfileResource($this->whenLoaded('reviewer')),
            'reviewed' => new UserProfileResource($this->whenLoaded('reviewed')),
            'service_request' => new UserServiceRequestResource($this->whenLoaded('serviceRequest')),
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
