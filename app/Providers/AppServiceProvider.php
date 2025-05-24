<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar rutas de broadcasting con autenticación Sanctum
        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        // Cargar definiciones de canales
        require base_path('routes/channels.php');
    }
}
