<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * Resource para formatear datos de contratos en respuestas API.
 */
class ContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        $isClient = $user && $user->id === $this->client_id;
        $isProvider = $user && $user->id === $this->provider_id;

        return [
            'id' => $this->id,
            'service_request_id' => $this->service_request_id,
            'service_offer_id' => $this->service_offer_id,
            'client_id' => $this->client_id,
            'provider_id' => $this->provider_id,
            'status' => $this->status,
            'terms' => $this->terms,
            'rejection_reason' => $this->rejection_reason,
            'cancellation_reason' => $this->cancellation_reason,
            'version' => $this->version,
            'revision_note' => $this->revision_note,
            
            // Fechas estructuradas
            'dates' => [
                'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
                'sent_at' => $this->sent_at?->format('Y-m-d H:i:s'),
                'responded_at' => $this->responded_at?->format('Y-m-d H:i:s'),
                'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
                'completed_at' => $this->status === 'completed' ? $this->updated_at?->format('Y-m-d H:i:s') : null,
                'edited_at' => $this->edited_at?->format('Y-m-d H:i:s'),
                're_sent_at' => $this->re_sent_at?->format('Y-m-d H:i:s'),
            ],
            
            // Permisos basados en el usuario actual y estado del contrato
            'permissions' => [
                'can_view' => $isClient || $isProvider,
                'can_send' => $isClient && $this->status === 'draft',
                'can_accept' => $isProvider && $this->status === 'sent' && !$this->isExpired(),
                'can_reject' => $isProvider && $this->status === 'sent' && !$this->isExpired(),
                'can_pay' => $isClient && $this->status === 'accepted',
                'can_cancel' => ($isClient || $isProvider) && !$this->isFinal(),
                'can_edit' => $isClient && $this->status === 'draft',
                'can_delete' => $isClient && $this->status === 'draft',
                'can_revise' => $isClient && $this->status === 'rejected',
            ],
            
            // Flags de estado
            'flags' => [
                'is_draft' => $this->status === 'draft',
                'is_sent' => $this->status === 'sent',
                'is_accepted' => $this->status === 'accepted',
                'is_rejected' => $this->status === 'rejected',
                'is_cancelled' => $this->status === 'cancelled',
                'is_expired' => $this->status === 'expired' || $this->isExpired(),
                'is_completed' => $this->status === 'completed',
                'is_final' => $this->isFinal(),
                'is_payable' => $this->canBePaid(),
                'is_owner_client' => $isClient,
                'is_owner_provider' => $isProvider,
            ],
            
            // Relaciones cuando están cargadas
            'service_request' => $this->whenLoaded('serviceRequest'),
            'service_offer' => $this->whenLoaded('serviceOffer'),
            'client' => $this->whenLoaded('client'),
            'provider' => $this->whenLoaded('provider'),
            'payments' => $this->whenLoaded('payments'),
        ];
    }
}