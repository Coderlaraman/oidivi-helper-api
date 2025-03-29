<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserReferralResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'referrer' => new UserProfileResource($this->whenLoaded('referrer')),
            'referred' => new UserProfileResource($this->whenLoaded('referred')),
            'accepted_at' => $this->accepted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
