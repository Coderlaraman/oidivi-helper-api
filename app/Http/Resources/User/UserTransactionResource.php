<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'payer' => new UserProfileResource($this->whenLoaded('payer')),
            'payee' => new UserProfileResource($this->whenLoaded('payee')),
            'service_request' => new UserServiceRequestResource($this->whenLoaded('serviceRequest')),
            'amount' => $this->amount,
            'system_fee' => $this->system_fee,
            'final_amount' => $this->final_amount,
            'status' => $this->status,
            'payment_method' => new UserPaymentMethodResource($this->whenLoaded('paymentMethod')),
            'transaction_id' => $this->transaction_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
