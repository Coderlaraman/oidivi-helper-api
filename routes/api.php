<?php

use App\Http\Controllers\Api\V1\Admin\AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Client\Auth\ClientAuthController;
use App\Http\Controllers\Api\V1\Client\Auth\ClientEmailVerificationController;
use App\Http\Controllers\Api\V1\Client\Profile\ClientUserController;
use App\Http\Controllers\Api\V1\Client\Services\ClientCategoryController;
use App\Http\Controllers\Api\V1\Client\Services\ClientServiceRequestController;
use App\Http\Controllers\Api\V1\Client\Services\ClientSkillController;
use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | API Routes - V1
 * |--------------------------------------------------------------------------
 * |
 * | Aquí definimos las rutas para la versión 1 de la API, separadas por dominios.
 * |
 */

Route::prefix('v1')->group(function () {

    /*
     * Rutas para Admin
     */
    Route::prefix('admin')
        ->middleware(['auth:sanctum', 'role:admin'])
        ->group(function () {
            Route::post('/register', [AdminAuthController::class, 'register'])->name('admin.register');
            Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login');
            Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
            Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
            Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
            Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        });

    /*
     * Rutas para Cliente
     */
    Route::prefix('client')->group(function () {
        // Ruta de prueba
        Route::get('/test', fn() => response()->json(['message' => 'API is working']));

        /*
         * Rutas de Autenticación (Auth)
         */
        Route::prefix('auth')->group(function () {
            Route::post('/register', [ClientAuthController::class, 'register'])->name('client.auth.register');
            Route::post('/login', [ClientAuthController::class, 'login'])->name('client.auth.login');
            Route::post('/logout', [ClientAuthController::class, 'logout'])->name('client.auth.logout')->middleware('auth:sanctum');
            Route::post('/forgot-password', [ClientAuthController::class, 'forgotPassword'])->middleware('auth:sanctum');

            Route::post('/email/verification-notification', [ClientEmailVerificationController::class, 'sendVerificationEmail']);
            Route::get('/email/verify/{id}/{hash}', [ClientEmailVerificationController::class, 'verify'])->name('verification.verify');
        });

        /*
         * Rutas de Perfil (Profile)
         */
        Route::prefix('profile')->middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('/me', [ClientUserController::class, 'showProfile']);
            Route::put('/update', [ClientUserController::class, 'updateProfile']);
            Route::post('/photo', [ClientUserController::class, 'uploadProfilePhoto']);
            Route::delete('/photo', [ClientUserController::class, 'deleteProfilePhoto']);
            Route::post('/video', [ClientUserController::class, 'uploadProfileVideo']);
            Route::put('/skills', [ClientUserController::class, 'updateSkills']);
            Route::get('/dashboard', [ClientUserController::class, 'dashboard']);
        });

        /*
         * Rutas de Servicios (Services)
         */
        Route::prefix('services')->middleware('auth:sanctum')->group(function () {
            Route::get('/', [ClientServiceRequestController::class, 'index']);
            Route::get('/{id}', [ClientServiceRequestController::class, 'show']);
            Route::post('/', [ClientServiceRequestController::class, 'store']);
            Route::put('/{id}', [ClientServiceRequestController::class, 'update']);
            Route::delete('/{id}', [ClientServiceRequestController::class, 'destroy']);
            Route::post('/{serviceId}/reviews', [ClientServiceRequestController::class, 'storeReview']);
            Route::post('/{serviceId}/transactions', [ClientServiceRequestController::class, 'storeTransaction']);
        });

        /*
         * Rutas de Categorías (Categories)
         */
        Route::prefix('categories')->group(function () {
            Route::get('/', [ClientCategoryController::class, 'index']);
            Route::get('/{id}', [ClientCategoryController::class, 'show']);
            Route::middleware('auth:sanctum')->group(function () {
                Route::post('/', [ClientCategoryController::class, 'store']);
                Route::put('/{id}', [ClientCategoryController::class, 'update']);
                Route::delete('/{id}', [ClientCategoryController::class, 'destroy']);
            });
        });

        /*
         * Rutas de Habilidades (Skills)
         */
        Route::prefix('skills')->group(function () {
            Route::get('/', [ClientSkillController::class, 'index']);
            Route::get('/{id}', [ClientSkillController::class, 'show']);
            Route::middleware('auth:sanctum')->group(function () {
                Route::post('/', [ClientSkillController::class, 'store']);
                Route::put('/{id}', [ClientSkillController::class, 'update']);
                Route::delete('/{id}', [ClientSkillController::class, 'destroy']);
            });
        });
    });
});
