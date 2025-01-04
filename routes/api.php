<?php

use App\Http\Controllers\Api\V1\Admin\AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Client\ClientAuthController;
use App\Http\Controllers\Api\V1\Client\ClientUserController;
use Illuminate\Support\Facades\Route;

// Versión 1 de la API

Route::get('/v1/admin/test', [AdminUserController::class, 'test']);
// Rutas para Admin
Route::prefix('v1/admin')->middleware(['auth:admin', 'role:admin'])->group(function () {
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
});

// Rutas para Cliente
Route::prefix('v1/client')->group(function () {

    Route::post('/login', [ClientAuthController::class, 'login']);
    Route::post('/register', [ClientAuthController::class, 'register']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard', [ClientUserController::class, 'dashboard']);
        Route::post('/logout', [ClientAuthController::class, 'logout']);
    });
});

