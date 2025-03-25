<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientTicketResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new ClientProfileResource($this->whenLoaded('user')),
            'category' => $this->category,
            'message' => $this->message,
            'status' => $this->status,
            'replies' => ClientTicketReplyResource::collection($this->whenLoaded('replies')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
