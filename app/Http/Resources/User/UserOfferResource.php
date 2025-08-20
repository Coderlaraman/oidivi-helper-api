<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\ServiceOffer
 */
class UserOfferResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_request_id' => $this->service_request_id,
            'user_id' => $this->user_id,
            'price_proposed' => $this->price_proposed,
            'estimated_time' => $this->estimated_time,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'profile_photo_url' => $this->user?->profile_photo_url ? Storage::url($this->user->profile_photo_url) : null,
            ],
            'serviceRequest' => $this->serviceRequest
                ? [
                    'id' => $this->serviceRequest->id,
                    'title' => $this->serviceRequest->title,
                ]
                : null,
        ];
    }
}
