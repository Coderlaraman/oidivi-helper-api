<?php

use App\Http\Controllers\Api\V1\Admin\AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Client\ClientAuthController;
use App\Http\Controllers\Api\V1\Client\ClientUserController;
use App\Http\Controllers\Api\V1\Client\EmailVerificationController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\SkillController;
use Illuminate\Support\Facades\Route;

// Versión 1 de la API

// Rutas para Admin
Route::prefix('v1/admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
    });

// Rutas para Cliente
Route::prefix('v1/client')->group(function () {
    // Ruta de prueba
    Route::get('/test', function () {
        return response()->json(['message' => 'API is working']);
    });

    // Rutas de autenticación
    Route::post('/login', [ClientAuthController::class, 'login']);
    Route::post('/register', [ClientAuthController::class, 'register']);

    // Endpoint para reenviar el email de verificación
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'sendVerificationEmail']);
    // Endpoint de verificación (accedido a través del enlace del email)
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->name('verification.verify');

    // Rutas de categorías
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{category}', [CategoryController::class, 'show']);
        Route::post('/', [CategoryController::class, 'store'])->middleware('auth:sanctum');
        Route::put('/{category}', [CategoryController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->middleware('auth:sanctum');
    });

    // Rutas de habilidades
    Route::prefix('skills')->group(function () {
        Route::get('/', [SkillController::class, 'index']);
        Route::get('/{skill}', [SkillController::class, 'show']);
        Route::post('/', [SkillController::class, 'store'])->middleware('auth:sanctum');
        Route::put('/{skill}', [SkillController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{skill}', [SkillController::class, 'destroy'])->middleware('auth:sanctum');
    });

    // Rutas de servicios
    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::get('/{id}', [ServiceController::class, 'show']);
        Route::post('/', [ServiceController::class, 'store'])->middleware('auth:sanctum');
        Route::put('/{id}', [ServiceController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/{id}', [ServiceController::class, 'destroy'])->middleware('auth:sanctum');
        Route::post('/{serviceId}/reviews', [ServiceController::class, 'storeReview'])->middleware('auth:sanctum');
        Route::post('/{serviceId}/transactions', [ServiceController::class, 'storeTransaction'])->middleware('auth:sanctum');
    });

    // Rutas de usuarios
    Route::prefix('users')->group(function () {
        // Buscar usuarios
        Route::get('/search', [ClientUserController::class, 'search']);
        Route::get('/find', [ClientUserController::class, 'find']);
    });

    // Rutas protegidas por autenticación
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard', [ClientUserController::class, 'dashboard']);
        Route::get('/me', [ClientUserController::class, 'me']);
        Route::post('/update-profile', [ClientUserController::class, 'updateProfile']);
        Route::post('/logout', [ClientAuthController::class, 'logout']);
        Route::put('/user/skills', [ClientUserController::class, 'updateSkills']);
        Route::post('/profile/photo', [ClientUserController::class, 'uploadProfilePhoto']);
        Route::delete('/profile/photo', [ClientUserController::class, 'deleteProfilePhoto']);
        Route::post('/profile/video', [ClientUserController::class, 'uploadProfileVideo']);
    });
});
