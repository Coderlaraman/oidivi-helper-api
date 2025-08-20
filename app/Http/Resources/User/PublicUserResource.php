<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PublicUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'profile_photo_url' => $this->profile_photo_url ? Storage::url($this->profile_photo_url) : null,
            'biography' => $this->biography,
            'skills' => UserSkillResource::collection($this->whenLoaded('skills')),
            'stats' => UserStatResource::make($this->whenLoaded('stats')),
            'reviews_received' => UserReviewResource::collection($this->whenLoaded('reviewsReceived')),
            // Otros campos públicos si se definen en el futuro
        ];
    }
}
