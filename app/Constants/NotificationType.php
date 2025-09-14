<?php

namespace App\Constants;

class NotificationType
{
    public const NEW_SERVICE_REQUEST = 'new_service_request';
    public const NEW_OFFER = 'new_offer';
    public const OFFER_STATUS_UPDATED = 'offer_status_updated';
    public const SERVICE_REQUEST_STATUS_UPDATED = 'service_request_status_updated';
    public const NEW_CHAT_MESSAGE               = 'new_chat_message';
    public const OFFER_ACCEPTED                 = 'offer_accepted';
    
    // Agreement notifications
    public const AGREEMENT_SENT = 'agreement_sent';
    public const AGREEMENT_ACCEPTED = 'agreement_accepted';
    public const AGREEMENT_REJECTED = 'agreement_rejected';
    public const AGREEMENT_CANCELLED = 'agreement_cancelled';
    public const AGREEMENT_EXPIRED = 'agreement_expired';

    public static function isValid(string $type): bool
    {
        return in_array($type, [
            self::NEW_SERVICE_REQUEST,
            self::NEW_OFFER,
            self::OFFER_STATUS_UPDATED,
            self::SERVICE_REQUEST_STATUS_UPDATED,
            self::NEW_CHAT_MESSAGE,
            self::OFFER_ACCEPTED,
            self::AGREEMENT_SENT,
            self::AGREEMENT_ACCEPTED,
            self::AGREEMENT_REJECTED,
            self::AGREEMENT_CANCELLED,
            self::AGREEMENT_EXPIRED,
        ]);
    }
}
