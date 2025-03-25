<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientTicketReplyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'user' => new ClientProfileResource($this->whenLoaded('user')),
            'message' => $this->message,
            'created_at' => $this->created_at
        ];
    }
}
