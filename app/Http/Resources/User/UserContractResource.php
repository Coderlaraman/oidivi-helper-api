<?php

namespace App\Http\Resources\User;


use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Contract
 */
class UserContractResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'estimated_time' => $this->estimated_time,
            'dates' => [
                'start_date' => $this->start_date?->format('Y-m-d H:i:s'),
                'end_date' => $this->end_date?->format('Y-m-d H:i:s'),
                'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            ],
            'status' => [
                'code' => $this->status,
                'is_pending' => $this->status === 'pending',
                'is_in_progress' => $this->status === 'in_progress',
                'is_completed' => $this->status === 'completed',
                'is_canceled' => $this->status === 'canceled',
                'is_paid' => $this->status === 'paid',
            ],
            'relationships' => [
                'provider' => new UserProfileResource($this->whenLoaded('provider')),
                'client' => new UserProfileResource($this->whenLoaded('client')),
                'service_offer' => new UserOfferResource($this->whenLoaded('serviceOffer')),
                'service_request' => new UserServiceRequestResource($this->whenLoaded('serviceRequest')),
            ],
        ];
    }
}
