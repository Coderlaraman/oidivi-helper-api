<?php

use App\Http\Controllers\Api\V1\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\Categories\AdminCategoryController;
// use App\Http\Controllers\Api\V1\Admin\ServiceRequests\AdminServiceRequestController;
use App\Http\Controllers\Api\V1\Admin\Skills\AdminSkillController;
use App\Http\Controllers\Api\V1\Admin\Users\AdminUserController;
use App\Http\Controllers\Api\V1\User\Auth\UserAuthController;
use App\Http\Controllers\Api\V1\User\Auth\UserEmailVerificationController;
use App\Http\Controllers\Api\V1\User\Categories\UserCategoryController;
use App\Http\Controllers\Api\V1\User\Locations\UserLocationController;
use App\Http\Controllers\Api\V1\User\Payments\UserPaymentController;
use App\Http\Controllers\Api\V1\User\Profiles\UserProfileController;
use App\Http\Controllers\Api\V1\User\Referrals\UserReferralController;
use App\Http\Controllers\Api\V1\User\Reports\UserReportController;
use App\Http\Controllers\Api\V1\User\Reviews\UserReviewController;
use App\Http\Controllers\Api\V1\User\ServiceRequests\UserServiceRequestController;
use App\Http\Controllers\Api\V1\User\Skills\UserSkillController;
use App\Http\Controllers\Api\V1\User\Subscriptions\UserSubscriptionController;
use App\Http\Controllers\Api\V1\User\Tickets\UserTicketController;
use App\Http\Controllers\Api\V1\User\Transactions\UserTransactionController;
use App\Http\Controllers\Api\V1\Chat\ChatController;
use App\Http\Controllers\Api\V1\Chat\MessageController;
use App\Http\Controllers\Api\V1\User\Notifications\UserNotificationController;
use App\Http\Controllers\Api\V1\User\ServiceOffers\UserServiceOfferController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes - V1
|--------------------------------------------------------------------------
|
| Se definen las rutas para la versión 1 de la API, separadas por dominios.
|
*/

Route::prefix('v1')->middleware('locale')->group(function () {

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
            Route::prefix('users')->group(function () {
                Route::get('/', [AdminUserController::class, 'index'])->name('admin.users.index');
                Route::post('/', [AdminUserController::class, 'store'])->name('admin.users.store');
                Route::get('/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
                Route::put('/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
                Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
                Route::post('/{user}/restore', [AdminUserController::class, 'restore'])->name('admin.users.restore');
                Route::delete('/{user}/force', [AdminUserController::class, 'forceDelete'])->name('admin.users.forceDelete');
                Route::patch('/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('admin.users.toggleActive');
            });

            // Rutas de categorías (definición manual)
            Route::prefix('categories')->group(function () {
                Route::get('/', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
                Route::get('/{category}', [AdminCategoryController::class, 'show'])->name('admin.categories.show');
                Route::post('/', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
                Route::put('/{category}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
                Route::delete('/{category}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');
                Route::post('/{id}/restore', [AdminCategoryController::class, 'restore'])->name('admin.categories.restore');
            });

            Route::apiResource('skills', AdminSkillController::class);
            Route::post('skills/{skill}/restore', [AdminSkillController::class, 'restore'])->name('admin.skills.restore');

            Route::apiResource('service-requests', AdminServiceRequestController::class)->except(['store']);
            Route::post('service-requests/{serviceRequest}/restore', [AdminServiceRequestController::class, 'restore'])->name('admin.serviceRequests.restore');
        });

    // Rutas públicas para los usuarios comunes
    Route::prefix('user')->group(function () {

        // Rutas de autenticación del usuario común
        Route::prefix('auth')->group(function () {  // Changed from 'user/auth' to just 'auth'
            Route::post('register', [UserAuthController::class, 'register'])->name('user.auth.register');
            Route::post('login', [UserAuthController::class, 'login'])->name('user.auth.login');
            Route::post('logout', [UserAuthController::class, 'logout'])
                ->name('user.auth.logout')
                ->middleware('auth:sanctum');

            // Password reset routes
            Route::post('forgot-password', [UserAuthController::class, 'forgotPassword'])->name('user.auth.forgot-password');
            Route::post('reset-password', [UserAuthController::class, 'resetPassword'])->name('user.auth.reset-password');

            // Email verification routes
            Route::post('email/verification-notification', [UserEmailVerificationController::class,'sendVerificationEmail']);
            Route::get('email/verify/{id}/{hash}', [UserEmailVerificationController::class,'verify'])
                ->name('verification.verify');
        });

        // Rutas de pagos
        Route::prefix('payments')->group(function () {
            Route::post('payment/process', [UserPaymentController::class, 'processPayment']);
            Route::post('payment/confirm', [UserPaymentController::class, 'confirmPayment']);
        });

        // Rutas de perfil (protegidas y con verificación)
        Route::prefix('profile')->middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('me', [UserProfileController::class, 'showProfile']);
            Route::put('update', [UserProfileController::class, 'updateProfile']);
            Route::post('photo', [UserProfileController::class, 'uploadProfilePhoto']);
            Route::delete('photo', [UserProfileController::class, 'deleteProfilePhoto']);
            Route::post('video', [UserProfileController::class, 'uploadProfileVideo']);
            Route::delete('video', [UserProfileController::class, 'deleteProfileVideo']);
            Route::put('skills', [UserProfileController::class, 'updateSkills']);
            Route::get('dashboard', [UserProfileController::class, 'dashboard']);
            Route::get('search', [UserProfileController::class, 'search']); // Añadido el endpoint de búsqueda
            Route::post('location/update', [UserLocationController::class, 'updateLocation']);
        });

        // Rutas de referrals
        Route::prefix('referrals')->group(function () {
            Route::get('/', [UserReferralController::class, 'index']);
            Route::post('/', [UserReferralController::class, 'store']);
            Route::put('{referral}', [UserReferralController::class, 'update']);
            Route::delete('{referral}', [UserReferralController::class, 'destroy']);
            Route::get('{referral}', [UserReferralController::class, 'show']);
        });

        // Rutas de reportes
        Route::prefix('reports')->group(function () {
            Route::post('reports', [UserReportController::class, 'store']);
            Route::get('reports', [UserReportController::class, 'index']);
        });

        // Rutas de reseñas
        Route::prefix('reviews')->group(function () {
            Route::post('reviews', [UserReviewController::class, 'store']);
            Route::get('reviews/{userId}', [UserReviewController::class, 'index']);
        });

        // Rutas de solicitudes de servicios (protegidas)
        Route::prefix('service-requests')->middleware('auth:sanctum')->group(function () {
            // Rutas públicas (solicitudes de otros usuarios)
            Route::get('/', [UserServiceRequestController::class, 'index']);

            // Rutas de ofertas específicas
            Route::get('/my-requests/offers', [UserServiceOfferController::class, 'receivedOffers']);
            Route::get('/my-requests/offers/{offer}', [UserServiceOfferController::class, 'showOffer']);

            // Rutas de solicitudes propias
            Route::prefix('my-requests')->group(function () {
                Route::get('/', [UserServiceRequestController::class, 'myRequests']);
                Route::get('/trash', [UserServiceRequestController::class, 'trashedRequests']);
                Route::get('/{id}/offers', [UserServiceOfferController::class, 'requestOffers']);
                Route::put('/{id}', [UserServiceRequestController::class, 'update']);
                Route::post('/{id}/restore', [UserServiceRequestController::class, 'restore']);
            });

            // Rutas generales de solicitudes
            Route::post('/', [UserServiceRequestController::class, 'store']);
            Route::get('/{id}', [UserServiceRequestController::class, 'show']);
            Route::patch('/{id}/status', [UserServiceRequestController::class, 'updateStatus']);
            Route::delete('/{id}', [UserServiceRequestController::class, 'destroy']);

            // Rutas de ofertas
            Route::post('/{serviceRequest}/offers', [UserServiceOfferController::class, 'store']);
            Route::patch('/offers/{offer}', [UserServiceOfferController::class, 'update']);
        });

        // Rutas de habilidades
        Route::prefix('skills')->middleware(['auth:sanctum'])->group(function () {
            Route::get('/', [UserSkillController::class, 'index']);
            Route::get('/available', [UserSkillController::class, 'available']);
            Route::post('/', [UserSkillController::class, 'store']);
            Route::delete('/{skill}', [UserSkillController::class, 'destroy']);
        });

        // Rutas de suscripciones
        Route::prefix('subscriptions')->group(function () {
            Route::get('/', [UserSubscriptionController::class, 'index']);
            Route::post('/', [UserSubscriptionController::class, 'store']);
            Route::put('{subscription}', [UserSubscriptionController::class, 'update']);
            Route::delete('{subscription}', [UserSubscriptionController::class, 'destroy']);
            Route::get('{subscription}', [UserSubscriptionController::class, 'show']);
        });

        // Rutas de tickets
        Route::prefix('tickets')->group(function () {
            Route::post('tickets', [UserTicketController::class, 'store']);
            Route::get('tickets', [UserTicketController::class, 'index']);
            Route::get('tickets/{ticket}', [UserTicketController::class, 'show']);
            Route::post('tickets/{ticket}/reply', [UserTicketController::class, 'reply']);
        });

        // Rutas de transacciones
        Route::prefix('transactions')->group(function () {
            Route::get('/', [UserTransactionController::class, 'index']);
            Route::get('{transaction}', [UserTransactionController::class, 'show']);
            Route::post('{transaction}/refund', [UserTransactionController::class, 'refund']);
        });

        Route::prefix('categories')->group(function () {
            Route::get('/', [UserCategoryController::class, 'index']);
        });

        // Rutas públicas de perfiles de usuario
        Route::get('public/profiles/{user}', [UserProfileController::class, 'showPublicProfile']);

        // Rutas de notificaciones
        Route::prefix('notifications')->middleware('auth:sanctum')->group(function () {
            Route::get('/', [UserNotificationController::class, 'index']);
            Route::get('/unread-count', [UserNotificationController::class, 'getUnreadCount']);
            Route::patch('{notification}/read', [UserNotificationController::class, 'markAsRead']);
            Route::patch('/read-all', [UserNotificationController::class, 'markAllAsRead']);
            Route::delete('{notification}', [UserNotificationController::class, 'destroy']);
        });
    });

    // Rutas de chat y mensajes
    Route::prefix('chats')->middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::post('/', [ChatController::class, 'store']);
        Route::get('/{chat}', [ChatController::class, 'show']);
        Route::put('/{chat}', [ChatController::class, 'update']);
        Route::delete('/{chat}', [ChatController::class, 'destroy']);
        Route::post('/{chat}/read', [ChatController::class, 'markAsRead']);
        Route::post('/{chat}/typing', [ChatController::class, 'typing']);

        // Rutas de mensajes
        Route::get('/{chat}/messages', [MessageController::class, 'index']); // Mantener si MessageController maneja listado
        Route::post('/{chat}/messages', [ChatController::class, 'storeMessage']); // Apuntar al nuevo método en ChatController
        Route::get('/{chat}/messages/{message}', [MessageController::class, 'show']);
        Route::put('/{chat}/messages/{message}', [MessageController::class, 'update']);
        Route::delete('/{chat}/messages/{message}', [MessageController::class, 'destroy']);
        Route::post('/{chat}/messages/{message}/seen', [MessageController::class, 'markAsSeen']);
    });
});