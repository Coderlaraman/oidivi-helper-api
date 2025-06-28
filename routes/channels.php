<?php

use App\Models\ServiceOffer;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Aquí registras todos los canales de difusión de eventos que tu aplicación
| soporta. Estos callbacks son ejecutados para verificar si un usuario
| autenticado puede escuchar en el canal dado.
|
*/


// Canal para notificaciones generales y de chat para un usuario específico.
// Este canal es crucial para las notificaciones push.
Broadcast::channel('user.notifications.{id}', function ($user, $id) {
    // Verifica que el usuario autenticado solo pueda escuchar su propio canal.
    return (int) $user->id === (int) $id;
});

// Canal para la sala de chat en tiempo real.
// Este canal es para cuando el usuario tiene la ventana de chat abierta.
Broadcast::channel('chat.offer.{offerId}', function ($user, $offerId) {
    $offer = ServiceOffer::find($offerId);

    // Si la oferta no existe, deniega el acceso.
    if (!$offer) {
        return false;
    }

    // Usa un método en el modelo para mantener la lógica encapsulada y limpia.
    // Esto es más mantenible que tener la lógica de negocio aquí.
    return $offer->isParticipant($user);
});

// Canal privado para seguimiento de ubicación
Broadcast::channel('location-tracking.{userId}', function ($user, $userId) {
    return (int)$user->id === (int)$userId;
});
