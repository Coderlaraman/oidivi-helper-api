<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class LocaleHelper
{
    /**
     * Cambiar el idioma de la aplicación
     *
     * @param string $locale
     * @return void
     */
    public static function setLocale($locale)
    {
        if (!in_array($locale, ['en', 'es', 'fr'])) {
            $locale = 'en';
        }

        App::setLocale($locale);

        // Si el usuario está autenticado, actualizar su preferencia de idioma
        if (Auth::check()) {
            Auth::user()->update(['preferred_language' => $locale]);
        }
    }

    /**
     * Obtener el idioma actual
     *
     * @return string
     */
    public static function getCurrentLocale()
    {
        return App::getLocale();
    }

    /**
     * Obtener el nombre del idioma actual
     *
     * @return string
     */
    public static function getCurrentLocaleName()
    {
        $locales = [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
        ];

        return $locales[App::getLocale()] ?? 'English';
    }
} 