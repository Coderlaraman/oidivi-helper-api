<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorizedResponse('You are not logged in.');
        }

        if (!$user->hasAnyRole($roles)) {
            return $this->errorResponse(
                'You do not have the necessary permissions to access this resource.',
                403
            );
        }

        if (!$user->isActive()) {
            return $this->errorResponse(
                'Your account is disabled. Contact the administrator.',
                403
            );
        }

        return $next($request);
    }
}
