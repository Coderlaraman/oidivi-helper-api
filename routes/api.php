<?php

use App\Http\Controllers\Api\V1\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\Categories\AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\ServiceRequests\AdminServiceRequestController;
use App\Http\Controllers\Api\V1\Admin\Skills\AdminSkillController;
use App\Http\Controllers\Api\V1\Admin\Users\AdminUserController;
use App\Http\Controllers\Api\V1\Chat\ChatController;
use App\Http\Controllers\Api\V1\Chat\MessageController;
use App\Http\Controllers\Api\V1\TermsController;
use App\Http\Controllers\Api\V1\User\Auth\UserAuthController;
use App\Http\Controllers\Api\V1\User\Auth\UserEmailVerificationController;
use App\Http\Controllers\Api\V1\User\Categories\UserCategoryController;
use App\Http\Controllers\Api\V1\User\Contracts\UserContractController;
use App\Http\Controllers\Api\V1\User\Locations\UserLocationController;
use App\Http\Controllers\Api\V1\User\Notifications\UserNotificationController;
use App\Http\Controllers\Api\V1\User\Payments\UserPaymentController;
use App\Http\Controllers\Api\V1\User\Profiles\UserProfileController;
use App\Http\Controllers\Api\V1\User\Referrals\UserReferralController;
use App\Http\Controllers\Api\V1\User\Reports\UserReportController;
use App\Http\Controllers\Api\V1\User\Reviews\UserReviewController;
use App\Http\Controllers\Api\V1\User\ServiceOffers\UserServiceOfferController;
use App\Http\Controllers\Api\V1\User\ServiceRequests\UserServiceRequestController;
use App\Http\Controllers\Api\V1\User\Skills\UserSkillController;
use App\Http\Controllers\Api\V1\User\Subscriptions\UserSubscriptionController;
use App\Http\Controllers\Api\V1\User\Tickets\UserTicketController;
use App\Http\Controllers\Api\V1\User\Transactions\UserTransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | API Routes - V1
 * |--------------------------------------------------------------------------
 * |
 * | Se definen las rutas para la versión 1 de la API, separadas por dominios.
 * |
 */

Route::prefix('v1')->middleware('locale')->group(function () {
    /**
     * Rutas generales públicas de la API.
     */
    // Ruta de prueba
    Route::get('test', fn() => response()->json(['message' => 'API is working']));
    // Ruta pública para obtener los términos y condiciones
    Route::get('terms', [TermsController::class, 'show']);

    // Ruta pública Webhook para recibir eventos de Stripe (confirmación de pago).
    Route::post('stripe/webhook', [UserPaymentController::class, 'handleStripeWebhook']);


    /**
     * Rutas de autenticación para el administrador.
     */
    Route::prefix('admin/auth')->group(function () {
        Route::post('login', [AdminAuthController::class, 'login']);
    });

    /**
     * Rutas protegidas para el administrador (gestión de usuarios, categorías, skills y solicitudes de servicio).
     */
    Route::prefix('admin')
        ->middleware(['auth:sanctum', 'role:admin'])
        ->group(function () {
            // Autenticación del administrador
            Route::get('auth/me', [AdminAuthController::class, 'me']);
            Route::post('auth/logout', [AdminAuthController::class, 'logout']);

            /**
             * Gestión de categorías de la plataforma.
             */
            Route::prefix('categories')->group(function () {
                Route::delete('/{category}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');
                Route::get('/', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
                Route::get('/{category}', [AdminCategoryController::class, 'show'])->name('admin.categories.show');
                Route::post('/', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
                Route::post('/{id}/restore', [AdminCategoryController::class, 'restore'])->name('admin.categories.restore');
                Route::put('/{category}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
            });

            /**
             * Gestión de solicitudes de servicio (service-requests) para el administrador.
             */
            Route::apiResource('service-requests', AdminServiceRequestController::class)->except(['store']);
            Route::post('service-requests/{serviceRequest}/restore', [AdminServiceRequestController::class, 'restore'])->name('admin.serviceRequests.restore');

            /**
             * Gestión de skills para el administrador.
             */
            Route::apiResource('skills', AdminSkillController::class);
            Route::post('skills/{skill}/restore', [AdminSkillController::class, 'restore'])->name('admin.skills.restore');

            /**
             * Gestión de usuarios para el administrador.
             */
            Route::prefix('users')->group(function () {
                Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
                Route::delete('/{user}/force', [AdminUserController::class, 'forceDelete'])->name('admin.users.forceDelete');
                Route::get('/', [AdminUserController::class, 'index'])->name('admin.users.index');
                Route::get('/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
                Route::patch('/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('admin.users.toggleActive');
                Route::post('/', [AdminUserController::class, 'store'])->name('admin.users.store');
                Route::post('/{user}/restore', [AdminUserController::class, 'restore'])->name('admin.users.restore');
                Route::put('/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
            });
        });

    /**
     * Rutas públicas y protegidas para usuarios comunes (clientes y proveedores).
     */
    Route::prefix('user')->group(function () {
        /**
         * Rutas de categorías disponibles para usuarios.
         */
        Route::prefix('categories')->group(function () {
            Route::get('/', [UserCategoryController::class, 'index']);
        });

        /**
         * Rutas de notificaciones del usuario autenticado.
         */
        Route::prefix('notifications')->middleware('auth:sanctum')->group(function () {
            Route::delete('{notification}', [UserNotificationController::class, 'destroy']);
            Route::get('/', [UserNotificationController::class, 'index']);
            Route::get('/unread-count', [UserNotificationController::class, 'getUnreadCount']);
            Route::patch('{notification}/read', [UserNotificationController::class, 'markAsRead']);
            Route::patch('/read-all', [UserNotificationController::class, 'markAllAsRead']);
        });

        /**
         * Rutas de autenticación y verificación de usuario común.
         */
        Route::prefix('auth')->group(function () {
            Route::post('email/verification-notification', [UserEmailVerificationController::class, 'sendVerificationEmail']);
            Route::get('email/verify/{id}/{hash}', [UserEmailVerificationController::class, 'verify'])->name('verification.verify');
            Route::post('forgot-password', [UserAuthController::class, 'forgotPassword'])->name('user.auth.forgot-password');
            Route::post('login', [UserAuthController::class, 'login'])->name('user.auth.login');
            Route::post('logout', [UserAuthController::class, 'logout'])->name('user.auth.logout')->middleware('auth:sanctum');
            Route::post('register', [UserAuthController::class, 'register'])->name('user.auth.register');
            Route::post('reset-password', [UserAuthController::class, 'resetPassword'])->name('user.auth.reset-password');
        });

        /**
         * Rutas de pagos del usuario.
         */

        Route::prefix('payments')->middleware('auth:sanctum')->group(function () {
            Route::post('initiate', [UserPaymentController::class, 'initiatePayment']);
        });

        /**
         * Rutas de perfil del usuario autenticado (protegidas y con verificación).
         */
        Route::prefix('profile')->middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::delete('photo', [UserProfileController::class, 'deleteProfilePhoto']);
            Route::delete('video', [UserProfileController::class, 'deleteProfileVideo']);
            Route::get('dashboard', [UserProfileController::class, 'dashboard']);
            Route::get('me', [UserProfileController::class, 'showProfile']);
            Route::get('search', [UserProfileController::class, 'search']);
            Route::post('location/update', [UserLocationController::class, 'updateLocation']);
            Route::post('photo', [UserProfileController::class, 'uploadProfilePhoto']);
            Route::post('video', [UserProfileController::class, 'uploadProfileVideo']);
            Route::put('skills', [UserProfileController::class, 'updateSkills']);
            Route::put('update', [UserProfileController::class, 'updateProfile']);
        });

        /**
         * Rutas de referrals (referidos) del usuario.
         */
        Route::prefix('referrals')->group(function () {
            Route::delete('{referral}', [UserReferralController::class, 'destroy']);
            Route::get('/', [UserReferralController::class, 'index']);
            Route::get('{referral}', [UserReferralController::class, 'show']);
            Route::post('/', [UserReferralController::class, 'store']);
            Route::put('{referral}', [UserReferralController::class, 'update']);
        });

        /**
         * Rutas de reportes del usuario.
         */
        Route::prefix('reports')->group(function () {
            Route::get('reports', [UserReportController::class, 'index']);
            Route::post('reports', [UserReportController::class, 'store']);
        });

        /**
         * Rutas de reseñas del usuario.
         */
        Route::prefix('reviews')->group(function () {
            Route::get('reviews/{userId}', [UserReviewController::class, 'index']);
            Route::post('reviews', [UserReviewController::class, 'store']);
        });

        /**
         * Rutas de solicitudes de servicio propias del usuario autenticado.
         */
        Route::prefix('my-service-requests')->middleware('auth:sanctum')->group(function () {
            Route::get('/', [UserServiceRequestController::class, 'myServiceRequests']);
            Route::get('/offers/{offer}', [UserServiceOfferController::class, 'showOffer']); // Detalle de oferta recibida
            Route::get('/trash', [UserServiceRequestController::class, 'trashedRequests']);
            Route::get('/{id}/offers', [UserServiceOfferController::class, 'requestOffers']);
            Route::patch('/{id}/status', [UserServiceRequestController::class, 'updateStatus']);
            Route::patch('/offers/{offer}', [UserServiceOfferController::class, 'update']);
            Route::post('/{id}/offers', [UserServiceOfferController::class, 'store']);
            Route::post('/{id}/restore', [UserServiceRequestController::class, 'restore']);
            Route::put('/{id}', [UserServiceRequestController::class, 'update']);
        });

        /**
         * Rutas de solicitudes de servicio de otros usuarios (protegidas).
         */
        Route::prefix('service-requests')->middleware('auth:sanctum')->group(function () {
            Route::delete('/{id}', [UserServiceRequestController::class, 'destroy']);
            Route::get('/', [UserServiceRequestController::class, 'index']);
            Route::get('/{id}', [UserServiceRequestController::class, 'show']);
            Route::patch('/{id}/status', [UserServiceRequestController::class, 'updateStatus']);
            Route::post('/', [UserServiceRequestController::class, 'store']);
            Route::post('/{id}/offers', [UserServiceOfferController::class, 'store']);
        });

        /**
         * Rutas de habilidades del usuario autenticado.
         */
        Route::prefix('skills')->middleware(['auth:sanctum'])->group(function () {
            Route::delete('/{skill}', [UserSkillController::class, 'destroy']);
            Route::get('/', [UserSkillController::class, 'index']);
            Route::get('/available', [UserSkillController::class, 'available']);
            Route::post('/', [UserSkillController::class, 'store']);
        });

        /**
         * Rutas de suscripciones del usuario.
         */
        Route::prefix('subscriptions')->group(function () {
            Route::delete('{subscription}', [UserSubscriptionController::class, 'destroy']);
            Route::get('/', [UserSubscriptionController::class, 'index']);
            Route::get('{subscription}', [UserSubscriptionController::class, 'show']);
            Route::post('/', [UserSubscriptionController::class, 'store']);
            Route::put('{subscription}', [UserSubscriptionController::class, 'update']);
        });

        /**
         * Rutas de tickets de soporte del usuario.
         */
        Route::prefix('tickets')->group(function () {
            Route::get('tickets', [UserTicketController::class, 'index']);
            Route::get('tickets/{ticket}', [UserTicketController::class, 'show']);
            Route::post('tickets', [UserTicketController::class, 'store']);
            Route::post('tickets/{ticket}/reply', [UserTicketController::class, 'reply']);
        });

        /**
         * Rutas de contratos del usuario autenticado.
         */
        Route::prefix('contracts')->middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::delete('/{contract}', [UserContractController::class, 'destroy']);
            Route::get('/', [UserContractController::class, 'index']);
            Route::get('/{contract}', [UserContractController::class, 'show']);
            Route::post('/', [UserContractController::class, 'store']);
            Route::put('/{contract}', [UserContractController::class, 'update']);
        });

        /**
         * Rutas de transacciones del usuario.
         */
        Route::prefix('transactions')->group(function () {
            Route::get('/', [UserTransactionController::class, 'index']);
            Route::get('/{transaction}', [UserTransactionController::class, 'show']);
            Route::post('{transaction}/cancel', [UserTransactionController::class, 'cancel']);
            Route::post('{transaction}/complete', [UserTransactionController::class, 'complete']);
            Route::post('{transaction}/refund', [UserTransactionController::class, 'refund']);
        });

        /**
         * Rutas de ofertas realizadas por el usuario autenticado.
         */
        Route::get('my-offers', [UserServiceOfferController::class, 'myOffers']);

        /**
         * Rutas de ofertas recibidas y enviadas por el usuario autenticado.
         */
        Route::get('service-offers/received', [UserServiceOfferController::class, 'receivedOffers'])->middleware('auth:sanctum');
        Route::get('service-offers/sent', [UserServiceOfferController::class, 'sentOffers'])->middleware('auth:sanctum');

        /**
         * Rutas públicas de perfiles de usuario.
         */
        Route::get('public/profiles/{user}', [UserProfileController::class, 'showPublicProfile']);
    });
    /**
     * Rutas de chat y mensajes entre usuarios autenticados.
     */
    Route::prefix('chats')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('chats.index');
        Route::get('/offers/{offerId}', [ChatController::class, 'showOrCreate']);
        Route::post('/offers/{offerId}/messages', [MessageController::class, 'store']);
        Route::post('/{chat}/messages', [MessageController::class, 'store']);
    });
});
