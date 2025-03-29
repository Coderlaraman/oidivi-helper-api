<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorizedResponse('You are not logged in.');
        }

        if (!$user->hasRole('admin')) {
            return $this->errorResponse(
                'You do not have administrative privileges.',
                403
            );
        }

        if (!$user->isActive()) {
            return $this->errorResponse(
                'Your account is disabled.',
                403
            );
        }

        return $next($request);
    }
}
