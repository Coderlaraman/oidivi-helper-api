<?php

use App\Http\Controllers\Api\V1\Admin\AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
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
         * Rutas de Categorías (Categories) 
         */
        Route::prefix('categories')->group(function () {
            // Listar todas las categorías con filtros y paginación
            Route::get('/', [ClientCategoryController::class, 'index'])->name('client.categories.index');

            // Mostrar una categoría específica junto a sus relaciones
            Route::get('/{category}', [ClientCategoryController::class, 'show'])->name('client.categories.show');
            
            Route::middleware('auth:sanctum')->group(function () {
                // Crear una nueva categoría
                Route::post('/', [ClientCategoryController::class, 'store'])->name('client.categories.store');

                // Actualizar una categoría existente
                Route::put('/{category}', [ClientCategoryController::class, 'update'])->name('client.categories.update');

                // Eliminar (soft delete) una categoría
                Route::delete('/{category}', [ClientCategoryController::class, 'destroy'])->name('client.categories.destroy');

            });
        });

        /*
         * Rutas de Pagos (Payments)
         */
        Route::prefix('payments')->group(function () {
            Route::post('/payment/process', [ClientPaymentController::class, 'processPayment']);
            Route::post('/payment/confirm', [ClientPaymentController::class, 'confirmPayment']);
        });

        /*
         * Rutas de Perfil (Profile)
         */
        Route::prefix('profile')->middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('/me', [ClientProfileController::class, 'showProfile']);
            Route::put('/update', [ClientProfileController::class, 'updateProfile']);
            Route::post('/photo', [ClientProfileController::class, 'uploadProfilePhoto']);
            Route::delete('/photo', [ClientProfileController::class, 'deleteProfilePhoto']);
            Route::post('/video', [ClientProfileController::class, 'uploadProfileVideo']);
            Route::put('/skills', [ClientProfileController::class, 'updateSkills']);
            Route::get('/dashboard', [ClientProfileController::class, 'dashboard']);
            Route::post('/location/update', [ClientLocationController::class, 'updateLocation']);
        });

        /*
         * Rutas de Referrals (Referrals)
         */
        Route::prefix('referrals')->group(function () {
            Route::get('/', [ClientReferralController::class, 'index']);
            Route::post('/', [ClientReferralController::class, 'store']);
            Route::put('/{referral}', [ClientReferralController::class, 'update']);
            Route::delete('/{referral}', [ClientReferralController::class, 'destroy']);
            Route::get('/{referral}', [ClientReferralController::class, 'show']);
        });

        /*
         * Rutas de Reportes (Reports)
         */
        Route::prefix('reports')->group(function () {
            Route::post('/reports', [ClientReportController::class, 'store']);
            Route::get('/reports', [ClientReportController::class, 'index']);
        });

        /*
         * Rutas de Reseñas (Reviews)
         */
        Route::prefix('reviews')->group(function () {
            Route::post('/reviews', [ClientReviewController::class, 'store']);
            Route::get('/reviews/{userId}', [ClientReviewController::class, 'index']);
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

        /*
         * Rutas de Suscripciones (Subscriptions)
         */
        Route::prefix('subscriptions')->group(function () {
            Route::get('/', [ClientSubscriptionController::class, 'index']);
            Route::post('/', [ClientSubscriptionController::class, 'store']);
            Route::put('/{subscription}', [ClientSubscriptionController::class, 'update']);
            Route::delete('/{subscription}', [ClientSubscriptionController::class, 'destroy']);
            Route::get('/{subscription}', [ClientSubscriptionController::class, 'show']);
        });

        /*
         * Rutas de Tickets (Tickets)
         */
        Route::prefix('tickets')->group(function () {
            Route::post('/tickets', [ClientTicketController::class, 'store']);
            Route::get('/tickets', [ClientTicketController::class, 'index']);
            Route::get('/tickets/{ticket}', [ClientTicketController::class, 'show']);
            Route::post('/tickets/{ticket}/reply', [ClientTicketController::class, 'reply']);
        });

        /*
         * Rutas de Transacciones (Transactions)
         */
        Route::prefix('transactions')->group(function () {
            Route::get('/', [ClientTransactionController::class, 'index']);
            Route::get('/{transaction}', [ClientTransactionController::class, 'show']);
            Route::post('/{transaction}/refund', [ClientTransactionController::class, 'refund']);
        });

    });
});
