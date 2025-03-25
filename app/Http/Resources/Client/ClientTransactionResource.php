<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'payer' => new ClientProfileResource($this->whenLoaded('payer')),
            'payee' => new ClientProfileResource($this->whenLoaded('payee')),
            'service_request' => new ClientServiceRequestResource($this->whenLoaded('serviceRequest')),
            'amount' => $this->amount,
            'system_fee' => $this->system_fee,
            'final_amount' => $this->final_amount,
            'status' => $this->status,
            'payment_method' => new ClientPaymentMethodResource($this->whenLoaded('paymentMethod')),
            'transaction_id' => $this->transaction_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
