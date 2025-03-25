<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientSubscriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new ClientProfileResource($this->whenLoaded('user')),
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
