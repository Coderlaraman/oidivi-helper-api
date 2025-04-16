<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserAuthResource extends JsonResource
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
            'email' => $this->email,
            'accepted_terms' => $this->accepted_terms,
            'is_active' => $this->is_active,
            'phone' => $this->phone,
            'address' => $this->address,
            'zip_code' => $this->zip_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'biography' => $this->biography,
            'profile_photo_url' => $this->profile_photo_url
                ? Storage::url($this->profile_photo_url)
                : null,
            'profile_video_url' => $this->profile_video_url
                ? Storage::url($this->profile_video_url)
                : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'roles' => $this->roles->pluck('name')->toArray(),
            'skills' => $this->skills->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'categories' => $skill->categories->pluck('name')->toArray(),
                ];
            })->toArray(),
        ];
    }
}
