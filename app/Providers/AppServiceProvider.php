<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
    }
}
