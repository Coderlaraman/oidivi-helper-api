<?php

use App\Http\Controllers\Api\V1\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\Categories\AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\Categories\CategoryController;
use App\Http\Controllers\Api\V1\Admin\ServiceRequests\ServiceRequestController;
use App\Http\Controllers\Api\V1\Admin\Skills\SkillController;
use App\Http\Controllers\Api\V1\Admin\Users\AdminUserController;
use App\Http\Controllers\Api\V1\Client\Auth\ClientAuthController;
use App\Http\Controllers\Api\V1\Client\Auth\ClientEmailVerificationController;
use App\Http\Controllers\Api\V1\Client\Categories\ClientCategoryController;
use App\Http\Controllers\Api\V1\Client\Locations\ClientLocationController;
use App\Http\Controllers\Api\V1\Client\Payments\ClientPaymentController;
use App\Http\Controllers\Api\V1\Client\Profiles\ClientProfileController;
use App\Http\Controllers\Api\V1\Client\Referrals\ClientReferralController;
use App\Http\Controllers\Api\V1\Client\Reports\ClientReportController;
use App\Http\Controllers\Api\V1\Client\Reviews\ClientReviewController;
use App\Http\Controllers\Api\V1\Client\ServiceRequests\ClientServiceRequestController;
use App\Http\Controllers\Api\V1\Client\Skills\ClientSkillController;
use App\Http\Controllers\Api\V1\Client\Subscriptions\ClientSubscriptionController;
use App\Http\Controllers\Api\V1\Client\Tickets\ClientTicketController;
use App\Http\Controllers\Api\V1\Client\Transactions\ClientTransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - V1
|--------------------------------------------------------------------------
|
| Se definen las rutas para la versión 1 de la API, separadas por dominios.
|
*/

Route::prefix('v1')->group(function () {

    
    // Ruta de prueba
    Route::get('test', fn() => response()->json(['message' => 'API is working']));

    // Rutas públicas de autenticación para el administrador
    Route::prefix('admin/auth')->group(function () {
        Route::post('login', [AdminAuthController::class, 'login']);
    });

    // Rutas protegidas para el administrador
    Route::prefix('admin')
        ->middleware(['auth:sanctum', 'role:admin'])
        ->group(function () {
            // Autenticación del administrador
            Route::post('auth/logout', [AdminAuthController::class, 'logout']);
            Route::get('auth/me', [AdminAuthController::class, 'me']);

            // Rutas de gestión de usuarios
            Route::get('users', [AdminUserController::class, 'index']);
            Route::post('users', [AdminUserController::class, 'store']);
            Route::delete('users/{id}', [AdminUserController::class, 'destroy']);

            // Rutas de categorías (definición manual)
            Route::prefix('categories')->group(function () {
                Route::get('/', [AdminCategoryController::class, 'index']);
                Route::get('/{category}', [AdminCategoryController::class, 'show']);
                Route::post('/', [AdminCategoryController::class, 'store']);
                Route::put('/{category}', [AdminCategoryController::class, 'update']);
                Route::delete('/{category}', [AdminCategoryController::class, 'destroy']);
                Route::post('/{id}/restore', [AdminCategoryController::class, 'restore']);
            });

            // Alternativamente, recursos API para categorías y otros recursos:
            Route::apiResource('categories', CategoryController::class);
            Route::post('categories/{category}/restore', [CategoryController::class, 'restore']);

            Route::apiResource('skills', SkillController::class);
            Route::post('skills/{skill}/restore', [SkillController::class, 'restore']);

            Route::apiResource('service-requests', ServiceRequestController::class)->except(['store']);
            Route::post('service-requests/{serviceRequest}/restore', [ServiceRequestController::class, 'restore']);
        });

    // Rutas públicas para el cliente
    Route::prefix('client')->group(function () {

        // Rutas de autenticación del cliente
        Route::prefix('auth')->group(function () {
            Route::post('register', [ClientAuthController::class, 'register'])->name('client.auth.register');
            Route::post('login', [ClientAuthController::class, 'login'])->name('client.auth.login');
            Route::post('logout', [ClientAuthController::class, 'logout'])
                ->name('client.auth.logout')
                ->middleware('auth:sanctum');
            Route::post('forgot-password', [ClientAuthController::class, 'forgotPassword'])
                ->middleware('auth:sanctum');

            Route::post('email/verification-notification', [ClientEmailVerificationController::class, 'sendVerificationEmail']);
            Route::get('email/verify/{id}/{hash}', [ClientEmailVerificationController::class, 'verify'])
                ->name('verification.verify');
        });

        // Rutas de categorías (solo lectura para usuarios)
        Route::prefix('categories')->group(function () {
            Route::get('/', [ClientCategoryController::class, 'index'])->name('client.categories.index');
            Route::get('/{category}', [ClientCategoryController::class, 'show'])->name('client.categories.show');
        });

        // Rutas de pagos
        Route::prefix('payments')->group(function () {
            Route::post('payment/process', [ClientPaymentController::class, 'processPayment']);
            Route::post('payment/confirm', [ClientPaymentController::class, 'confirmPayment']);
        });

        // Rutas de perfil (protegidas y con verificación)
        Route::prefix('profile')->middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('me', [ClientProfileController::class, 'showProfile']);
            Route::put('update', [ClientProfileController::class, 'updateProfile']);
            Route::post('photo', [ClientProfileController::class, 'uploadProfilePhoto']);
            Route::delete('photo', [ClientProfileController::class, 'deleteProfilePhoto']);
            Route::post('video', [ClientProfileController::class, 'uploadProfileVideo']);
            Route::put('skills', [ClientProfileController::class, 'updateSkills']);
            Route::get('dashboard', [ClientProfileController::class, 'dashboard']);
            Route::post('location/update', [ClientLocationController::class, 'updateLocation']);
        });

        // Rutas de referrals
        Route::prefix('referrals')->group(function () {
            Route::get('/', [ClientReferralController::class, 'index']);
            Route::post('/', [ClientReferralController::class, 'store']);
            Route::put('{referral}', [ClientReferralController::class, 'update']);
            Route::delete('{referral}', [ClientReferralController::class, 'destroy']);
            Route::get('{referral}', [ClientReferralController::class, 'show']);
        });

        // Rutas de reportes
        Route::prefix('reports')->group(function () {
            Route::post('reports', [ClientReportController::class, 'store']);
            Route::get('reports', [ClientReportController::class, 'index']);
        });

        // Rutas de reseñas
        Route::prefix('reviews')->group(function () {
            Route::post('reviews', [ClientReviewController::class, 'store']);
            Route::get('reviews/{userId}', [ClientReviewController::class, 'index']);
        });

        // Rutas de servicios (protegidas)
        Route::prefix('services')->middleware('auth:sanctum')->group(function () {
            Route::get('/', [ClientServiceRequestController::class, 'index']);
            Route::get('{id}', [ClientServiceRequestController::class, 'show']);
            Route::post('/', [ClientServiceRequestController::class, 'store']);
            Route::put('{id}', [ClientServiceRequestController::class, 'update']);
            Route::delete('{id}', [ClientServiceRequestController::class, 'destroy']);
            Route::post('{serviceId}/reviews', [ClientServiceRequestController::class, 'storeReview']);
            Route::post('{serviceId}/transactions', [ClientServiceRequestController::class, 'storeTransaction']);
        });

        // Rutas de habilidades
        Route::prefix('skills')->group(function () {
            Route::get('/', [ClientSkillController::class, 'index']);
            Route::get('{id}', [ClientSkillController::class, 'show']);
            Route::middleware('auth:sanctum')->group(function () {
                Route::post('/', [ClientSkillController::class, 'store']);
                Route::put('{id}', [ClientSkillController::class, 'update']);
                Route::delete('{id}', [ClientSkillController::class, 'destroy']);
            });
        });

        // Rutas de suscripciones
        Route::prefix('subscriptions')->group(function () {
            Route::get('/', [ClientSubscriptionController::class, 'index']);
            Route::post('/', [ClientSubscriptionController::class, 'store']);
            Route::put('{subscription}', [ClientSubscriptionController::class, 'update']);
            Route::delete('{subscription}', [ClientSubscriptionController::class, 'destroy']);
            Route::get('{subscription}', [ClientSubscriptionController::class, 'show']);
        });

        // Rutas de tickets
        Route::prefix('tickets')->group(function () {
            Route::post('tickets', [ClientTicketController::class, 'store']);
            Route::get('tickets', [ClientTicketController::class, 'index']);
            Route::get('tickets/{ticket}', [ClientTicketController::class, 'show']);
            Route::post('tickets/{ticket}/reply', [ClientTicketController::class, 'reply']);
        });

        // Rutas de transacciones
        Route::prefix('transactions')->group(function () {
            Route::get('/', [ClientTransactionController::class, 'index']);
            Route::get('{transaction}', [ClientTransactionController::class, 'show']);
            Route::post('{transaction}/refund', [ClientTransactionController::class, 'refund']);
        });
    });

    // Ejemplo de rutas para otros roles (clientes, helpers, etc.)
    Route::middleware(['auth:sanctum', 'role:client,helper'])->group(function () {
        // Rutas adicionales accesibles por clientes y helpers
    });
});
