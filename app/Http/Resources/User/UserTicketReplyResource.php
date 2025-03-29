<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserTicketReplyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'user' => new UserProfileResource($this->whenLoaded('user')),
            'message' => $this->message,
            'created_at' => $this->created_at
        ];
    }
}
