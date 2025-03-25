<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcast::channel('location-tracking.{userId}', function ($userId) {
//     return (int) auth()->id() === (int) $userId;
// });

Broadcast::channel('location-tracking.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId; // Solo el usuario puede escuchar su ubicación
});
