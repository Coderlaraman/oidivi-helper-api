<?php

use App\Models\ServiceOffer;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Aquí se registran los canales privados (y públicos, si los hubiera).
| La llamada a Broadcast::routes() registra las rutas internas que usa
| Laravel para autenticar suscripciones a canales privados.
|
*/

// Registrar las rutas de autorización de canales
Broadcast::routes(['middleware' => ['auth:sanctum']]);

// Canal genérico para cada usuario (notificaciones, etc.)
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});

// Canal específico para notificaciones de usuario
Broadcast::channel('user.notifications.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});

// Canal del chat 1:1 por oferta (chat.offer.{offerId})
Broadcast::channel('chat.offer.{offerId}', function ($user, $offerId) {
    $offer = ServiceOffer::with('serviceRequest.user')->find($offerId);
    if (! $offer) {
        return false;
    }

    $requesterId = $offer->serviceRequest->user_id;
    $offererId   = $offer->user_id;

    return $user->id === $requesterId || $user->id === $offererId;
});

// Canal privado para seguimiento de ubicación (si lo usas)
Broadcast::channel('location-tracking.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});
