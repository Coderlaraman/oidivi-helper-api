<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Los idiomas soportados por la aplicación
     */
    protected $availableLocales = ['en', 'es', 'fr'];

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener el idioma del header Accept-Language
        $locale = $request->header('Accept-Language');
        
        // Si no hay un idioma en el header o no es válido, usar el idioma por defecto
        if (!$locale || !in_array($locale, $this->availableLocales)) {
            $locale = config('app.locale');
        }
        
        // Si el usuario está autenticado, su preferencia tiene prioridad
        if ($request->user() && $request->user()->preferred_language) {
            $userLocale = $request->user()->preferred_language;
            
            // Verificar que el idioma preferido del usuario esté soportado
            if (in_array($userLocale, $this->availableLocales)) {
                $locale = $userLocale;
            }
        }

        // Establecer el locale de la aplicación
        App::setLocale($locale);
        
        return $next($request);
    }
}