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
            'would_recommend' => $this->would_recommend,
            'can_edit' => $this->when(auth()->check(), function () {
                return auth()->id() === $this->reviewer_id;
            }),
            'can_delete' => $this->when(auth()->check(), function () {
                return auth()->id() === $this->reviewer_id;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_human' => $this->created_at?->diffForHumans(),
            'updated_at_human' => $this->updated_at?->diffForHumans()
        ];
    }
}
