<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

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

// Canal privado para notificaciones de usuario
Broadcast::channel('user.notifications.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum']]);

// Canal privado para usuarios
Broadcast::channel('user.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum']]);

// Canal para chats privados
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum']]);


// Canal para chats privados
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = \App\Models\Chat::find($chatId); // Usar el FQN para Chat

    if (!$chat) {
        return false;
    }

    // Verificar si el usuario es participante del chat
    return $chat->isParticipant($user);
}, ['guards' => ['sanctum']]);


// Broadcast::channel('my-proof', function(){
//     return true;
// });
