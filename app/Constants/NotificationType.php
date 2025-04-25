<?php

namespace App\Constants;

class NotificationType
{
    public const NEW_SERVICE_REQUEST = 'new_service_request';
    public const NEW_OFFER = 'new_offer';
    public const OFFER_STATUS_UPDATED = 'offer_status_updated';
    public const SERVICE_REQUEST_STATUS_UPDATED = 'service_request_status_updated';

    public static function isValid(string $type): bool
    {
        return in_array($type, [
            self::NEW_SERVICE_REQUEST,
            self::NEW_OFFER,
            self::OFFER_STATUS_UPDATED,
            self::SERVICE_REQUEST_STATUS_UPDATED
        ]);
    }
} 