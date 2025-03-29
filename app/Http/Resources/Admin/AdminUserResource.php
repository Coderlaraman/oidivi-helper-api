<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAdmin = $request->user()?->hasRole('admin');
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->roles->pluck('name'),
            'is_active' => (bool) $this->is_active,
            'preferred_language' => $this->preferred_language,
            'profile' => [
                'photo_url' => $this->profile_photo_url,
                'video_url' => $this->profile_video_url,
                'biography' => $this->biography,
            ],
            'verification' => [
                'status' => $this->verification_status,
                'notes' => $isAdmin ? $this->verification_notes : null,
                'documents' => $isAdmin ? $this->verification_documents : null,
                'documents_verified_at' => $isAdmin ? $this->documents_verified_at : null,
            ],
            'contact' => [
                'phone' => $this->phone,
                'phone_verified_at' => $isAdmin ? $this->phone_verified_at : null,
                'address' => $this->address,
                'zip_code' => $this->zip_code,
                'coordinates' => [
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                ],
            ],
            'activity' => $isAdmin ? [
                'last_login_at' => $this->last_login_at,
                'last_activity_at' => $this->last_activity_at,
            ] : null,
            'timestamps' => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'deleted_at' => $isAdmin ? $this->deleted_at : null,
            ],
        ];
    }
}