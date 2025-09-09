<?php

return [
    'types' => [
        'new_chat_message' => 'New Chat Message',
        'new_service_request' => 'New Service Request',
        'new_offer' => 'New Offer',
        'offer_accepted' => 'Offer Accepted',
        'offer_status_updated' => 'Offer Status Updated',
        'service_request_status_updated' => 'Service Request Status Updated',
        'contract_sent' => 'Contract Sent',
        'contract_sent_client' => 'Contract Sent (Confirmation)',
        'contract_accepted' => 'Contract Accepted',
        'contract_rejected' => 'Contract Rejected',
        'contract_cancelled' => 'Contract Cancelled',
        'contract_expired' => 'Contract Expired'
    ],
    'messages' => [
        'new_service_request' => 'A new service request has been created: :title',
        'new_offer' => 'You have received a new offer for: :title',
        'offer_status_updated' => 'The offer status for :title has been updated to :status',
        'service_request_status_updated' => 'The service request status for :title has been updated to :status',
        'offer_accepted_message' => 'Your offer for :title has been accepted.',
        'contract_sent' => 'A contract has been sent to you for: :title',
        'contract_sent_client' => 'Your contract was created and sent to the helper. It is pending acceptance: :title',
        'contract_accepted' => 'Your contract for :title has been accepted',
        'contract_rejected' => 'Your contract for :title has been rejected',
        'contract_cancelled' => 'The contract for :title has been cancelled',
        'contract_expired' => 'The contract for :title has expired'
    ]
];
