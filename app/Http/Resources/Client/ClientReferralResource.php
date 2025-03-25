<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientReferralResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'referrer' => new ClientProfileResource($this->whenLoaded('referrer')),
            'referred' => new ClientProfileResource($this->whenLoaded('referred')),
            'accepted_at' => $this->accepted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
