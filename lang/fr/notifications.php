<?php

return [
    'types' => [
        'new_chat_message' => 'Nouveau message de chat',
        'new_service_request' => 'Nouvelle demande de service',
        'new_offer' => 'Nouvelle offre',
        'offer_accepted' => 'Offre acceptée',
        'offer_status_updated' => 'Statut de l\'offre mis à jour',
        'service_request_status_updated' => 'Statut de la demande de service mis à jour',
        'agreement_sent' => 'Accord envoyé',
        'agreement_sent_client' => 'Accord envoyé (Confirmation)',
        'agreement_accepted' => 'Accord accepté',
        'agreement_rejected' => 'Accord rejeté',
        'agreement_cancelled' => 'Accord annulé',
        'agreement_expired' => 'Accord expiré',
        // Ajouté type de notification de paiement
        'payment_completed' => 'Paiement effectué',
    ],
    'messages' => [
        'new_service_request' => 'Une nouvelle demande de service a été créée : :title',
        'new_offer' => 'Vous avez reçu une nouvelle offre pour : :title',
        'offer_status_updated' => 'Le statut de l\'offre pour :title a été mis à jour à :status',
        'service_request_status_updated' => 'Le statut de la demande de service pour :title a été mis à jour à :status',
        'offer_accepted_message' => 'Votre offre pour :title a été acceptée.',
        'agreement_sent' => 'Un accord vous a été envoyé pour : :title',
        'agreement_sent_client' => 'Votre accord a été créé et envoyé à l\'assistant. Il est en attente d\'acceptation : :title',
        'agreement_accepted' => 'Votre accord pour :title a été accepté',
        'agreement_rejected' => 'Votre accord pour :title a été rejeté',
        'agreement_cancelled' => 'L\'accord pour :title a été annulé',
        'agreement_expired' => 'L\'accord pour :title a expiré',
        // Ajouté message de paiement effectué
        'payment_completed' => 'Vous avez reçu un paiement de :amount :currency pour le service ":service".',
    ]
];