<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ClientUserProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'zip_code' => $this->zip_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'profile_photo_url' => $this->profile_photo_url ? Storage::url($this->profile_photo_url) : null,
            'profile_video_url' => $this->profile_video_url ? Storage::url($this->profile_video_url) : null,
            'roles' => $this->roles->pluck('name'),
            // Agrega cualquier otro dato o relación específica para el perfil
            'stats' => new ClientUserStatResource($this->whenLoaded('stats')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
