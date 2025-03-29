<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserProfileResource($this->whenLoaded('user')),
            'plan_name' => $this->plan_name,
            'status' => $this->status,
            'price' => $this->price,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'details' => $this->details,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
