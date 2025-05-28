<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('user.{id}', function ($user, $id) {
    // Solo el propio usuario puede suscribirse
    return (int)$user->id === (int)$id;
});

// Canal específico para notificaciones de usuario
Broadcast::channel('user.notifications.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);

    if (!$chat) {
        return false;
    }

    return $chat->isParticipant($user);
});

// Canal privado para el seguimiento de ubicación del usuario
Broadcast::channel('location-tracking.{userId}', function ($user, $userId) {
    // Solo el propio usuario puede suscribirse a su canal de ubicación
    return (int)$user->id === (int)$userId;
});

Broadcast::routes(['middleware' => ['auth:sanctum']]);
