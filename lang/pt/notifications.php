<?php

return [
    'types' => [
        'new_chat_message' => 'Nova Mensagem de Chat',
        'new_service_request' => 'Nova Solicitação de Serviço',
        'new_offer' => 'Nova Oferta',
        'offer_accepted' => 'Oferta Aceita',
        'offer_status_updated' => 'Status da Oferta Atualizado',
        'service_request_status_updated' => 'Status da Solicitação de Serviço Atualizado',
        'agreement_sent' => 'Acordo Enviado',
        'agreement_sent_client' => 'Acordo Enviado (Confirmação)',
        'agreement_accepted' => 'Acordo Aceito',
        'agreement_rejected' => 'Acordo Rejeitado',
        'agreement_cancelled' => 'Acordo Cancelado',
        'agreement_expired' => 'Acordo Expirado',
        // Adicionado tipo de notificação de pagamento
        'payment_completed' => 'Pagamento Concluído',
    ],
    'messages' => [
        'new_service_request' => 'Uma nova solicitação de serviço foi criada: :title',
        'new_offer' => 'Você recebeu uma nova oferta para: :title',
        'offer_status_updated' => 'O status da oferta para :title foi atualizado para :status',
        'service_request_status_updated' => 'O status da solicitação de serviço para :title foi atualizado para :status',
        'offer_accepted_message' => 'Sua oferta para :title foi aceita.',
        'agreement_sent' => 'Um acordo foi enviado para você para: :title',
        'agreement_sent_client' => 'Seu acordo foi criado e enviado ao colaborador. Está pendente de aceitação: :title',
        'agreement_accepted' => 'Seu acordo para :title foi aceito',
        'agreement_rejected' => 'Seu acordo para :title foi rejeitado',
        'agreement_cancelled' => 'O acordo para :title foi cancelado',
        'agreement_expired' => 'O acordo para :title expirou',
        // Adicionada mensagem de pagamento concluído
        'payment_completed' => 'Você recebeu um pagamento de :amount :currency pelo serviço ":service".',
    ]
];
