<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

/**
 * ============================================================================
 * PROVEEDOR DE SERVICIOS GENERAL DE LA APLICACIÓN
 * Buenas Prácticas: Configuración de límites de longitud para MariaDB/MySQL
 * ============================================================================
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registro de bindings e interfaces si se requieren
    }

    public function boot(): void
    {
        // Compatibilidad garantizada para índices en MySQL/MariaDB XAMPP
        Schema::defaultStringLength(191);

        if ($this->app->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
