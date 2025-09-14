<?php

return [
    'types' => [
        'new_chat_message' => 'New Chat Message',
        'new_service_request' => 'New Service Request',
        'new_offer' => 'New Offer',
        'offer_accepted' => 'Offer Accepted',
        'offer_status_updated' => 'Offer Status Updated',
        'service_request_status_updated' => 'Service Request Status Updated',
        'agreement_sent' => 'Agreement Sent',
        'agreement_sent_client' => 'Agreement Sent (Confirmation)',
        'agreement_accepted' => 'Agreement Accepted',
        'agreement_rejected' => 'Agreement Rejected',
        'agreement_cancelled' => 'Agreement Cancelled',
        'agreement_expired' => 'Agreement Expired'
    ],
    'messages' => [
        'new_service_request' => 'A new service request has been created: :title',
        'new_offer' => 'You have received a new offer for: :title',
        'offer_status_updated' => 'The offer status for :title has been updated to :status',
        'service_request_status_updated' => 'The service request status for :title has been updated to :status',
        'offer_accepted_message' => 'Your offer for :title has been accepted.',
        'agreement_sent' => 'An agreement has been sent to you for: :title',
        'agreement_sent_client' => 'Your agreement was created and sent to the helper. It is pending acceptance: :title',
        'agreement_accepted' => 'Your agreement for :title has been accepted',
        'agreement_rejected' => 'Your agreement for :title has been rejected',
        'agreement_cancelled' => 'The agreement for :title has been cancelled',
        'agreement_expired' => 'The agreement for :title has expired'
    ]
];
