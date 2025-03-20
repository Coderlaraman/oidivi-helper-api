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
            'biography' => $this->biography,
            'verification_status' => $this->verification_status,
            'is_verified' => $this->isVerified(),
            'verification_documents' => collect($this->verification_documents)->map(function ($document) {
                return [
                    'url' => Storage::url($document['url']),
                    'type' => $document['type'],
                    'uploaded_at' => $document['uploaded_at'],
                ];
            }),
            'documents_verified_at' => $this->documents_verified_at,
            'roles' => $this->roles->pluck('name'),
            'stats' => new ClientUserStatResource($this->whenLoaded('stats')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
