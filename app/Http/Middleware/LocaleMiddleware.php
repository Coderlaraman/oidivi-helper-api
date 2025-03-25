<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtener el idioma del header Accept-Language
        $locale = $request->getPreferredLanguage(['en', 'es', 'fr']);
        
        // Si el usuario está autenticado, usar su preferencia de idioma
        if ($request->user()) {
            $locale = $request->user()->preferred_language ?? $locale;
        }

        // Establecer el locale de la aplicación
        App::setLocale($locale);

        return $next($request);
    }
}
