<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $serviceRequest = null;
        $serviceOffer = null;

        // Determinar el tipo de notificación y cargar la relación relevante
        if ($this->type === 'new_service_request' || $this->type === 'service_request_status_updated') {
            $serviceRequest = $this->serviceRequests->first();
        } elseif ($this->type === 'new_offer' || $this->type === 'offer_status_updated') {
            // Para notificaciones de oferta, obtenemos la primera oferta asociada y a través de ella la solicitud de servicio
            $serviceOffer = $this->serviceOffers->first(); // Usar la relación morphedByMany y obtener el primer resultado
            if ($serviceOffer && $serviceOffer->serviceRequest) {
                $serviceRequest = $serviceOffer->serviceRequest; // Accede a la solicitud a través de la oferta
            }
        }

        // Construir la URL de acción basada en el tipo de notificación y los datos disponibles
        $actionUrl = null;
        if ($serviceRequest) {
            if ($this->type === 'new_service_request' || $this->type === 'service_request_status_updated') {
                 $actionUrl = "/service-requests/{$serviceRequest->id}";
            } elseif (($this->type === 'new_offer' || $this->type === 'offer_status_updated') && $serviceOffer) {
                 // Si es una notificación de oferta y tenemos la oferta y la solicitud, construimos la URL de detalle de oferta
                 $actionUrl = "/my-service-requests/{$serviceRequest->id}/offers/{$serviceOffer->id}";
            }
        }


        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'title' => $this->title,
            'title_translated' => __('notifications.types.' . $this->type),
            'message' => $this->message,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'service_request' => $serviceRequest ? [
                'id' => $serviceRequest->id,
                'title' => $serviceRequest->title,
                'slug' => $serviceRequest->slug,
                'description' => $serviceRequest->description,
                'budget' => $serviceRequest->budget,
                'priority' => $serviceRequest->priority,
                'service_type' => $serviceRequest->service_type,
                'status' => $serviceRequest->status,
                'created_at' => $serviceRequest->created_at->toIso8601String(),
            ] : null,
             'service_offer' => $serviceOffer ? [
                 'id' => $serviceOffer->id,
                 // Puedes agregar otros campos de ServiceOffer si son relevantes para el frontend
             ] : null,
            'notification' => [
                'title' => $this->title,
                'message' => $this->message,
                'action_url' => $actionUrl,
            ],
        ];
    }
}
