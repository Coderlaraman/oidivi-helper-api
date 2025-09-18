<?php

return [
    'types' => [
        'new_chat_message' => 'Nuevo Mensaje de Chat',
        'new_service_request' => 'Nueva Solicitud de Servicio',
        'new_offer' => 'Nueva Oferta',
        'offer_accepted' => 'Oferta Aceptada',
        'offer_status_updated' => 'Estado de la Oferta Actualizado',
        'service_request_status_updated' => 'Estado de la Solicitud de Servicio Actualizado',
        'agreement_sent' => 'Acuerdo Enviado',
        'agreement_sent_client' => 'Acuerdo Enviado (Confirmación)',
        'agreement_accepted' => 'Acuerdo Aceptado',
        'agreement_rejected' => 'Acuerdo Rechazado',
        'agreement_cancelled' => 'Acuerdo Cancelado',
        'agreement_expired' => 'Acuerdo Vencido',
        // Añadido tipo de notificación de pago
        'payment_completed' => 'Pago Completado',
    ],
    'messages' => [
        'new_service_request' => 'Se ha creado una nueva solicitud de servicio: :title',
        'new_offer' => 'Has recibido una nueva oferta para: :title',
        'offer_status_updated' => 'El estado de la oferta para :title se ha actualizado a :status',
        'service_request_status_updated' => 'El estado de la solicitud de servicio para :title se ha actualizado a :status',
        'offer_accepted_message' => 'Tu oferta para :title ha sido aceptada.',
        'agreement_sent' => 'Se te ha enviado un acuerdo para: :title',
        'agreement_sent_client' => 'Tu acuerdo fue creado y enviado al colaborador. Está pendiente de aceptación: :title',
        'agreement_accepted' => 'Tu acuerdo para :title ha sido aceptado',
        'agreement_rejected' => 'Tu acuerdo para :title ha sido rechazado',
        'agreement_cancelled' => 'El acuerdo para :title ha sido cancelado',
        'agreement_expired' => 'El acuerdo para :title ha vencido',
        // Añadido mensaje de pago completado
        'payment_completed' => 'Recibiste un pago de :amount :currency por el servicio ":service".',
    ]
];