<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\PostTooLargeException;

class HandlePostTooLarge
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (PostTooLargeException $e) {
            return response()->json([
                'message' => __('messages.validation_error'),
                'errors' => [
                    'file' => [__('messages.file_too_large')],
                ],
            ], 422);
        }
    }
}

