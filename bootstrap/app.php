<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Application;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withBroadcasting(
        // Fuente de verdad para la ruta de autorización.
        __DIR__ . '/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['api', 'auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => CheckRole::class,
            'verified' => EnsureEmailIsVerified::class,
            'locale' => LocaleMiddleware::class,
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('messages.validation_error'),
                    'errors' => ['file' => [__('messages.file_too_large')]],
                ], 422);
            }
            return false;
        });
    })
    ->create();
