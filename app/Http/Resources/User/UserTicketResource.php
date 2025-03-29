<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserTicketResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserProfileResource($this->whenLoaded('user')),
            'category' => $this->category,
            'message' => $this->message,
            'status' => $this->status,
            'replies' => UserTicketReplyResource::collection($this->whenLoaded('replies')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
