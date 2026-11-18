<?php

use App\Http\Middleware\CheckLatPermission;
use App\Http\Middleware\SetLocaleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/**
 * ============================================================================
 * PROYECTO UNIVERSITARIO: LAT - SISTEMA DE GESTIÓN LÁCTEA COMPACTO
 * CONFIGURACIÓN DE ARRANQUE (BOOTSTRAP LARAVEL)
 * ============================================================================
 */

// Garantiza la resolución local de los espacios de nombres App y Database\Seeders
spl_autoload_register(function (string $class): bool {
    if (str_starts_with($class, 'App\\')) {
        $relativa = str_replace('\\', '/', substr($class, 4));
        $archivo = dirname(__DIR__).'/app/'.$relativa.'.php';
        if (file_exists($archivo)) {
            require_once $archivo;

            return true;
        }
    }

    if (str_starts_with($class, 'Database\\Seeders\\')) {
        $relativa = str_replace('\\', '/', substr($class, 17));
        $archivo = dirname(__DIR__).'/database/seeders/'.$relativa.'.php';
        if (file_exists($archivo)) {
            require_once $archivo;

            return true;
        }
    }

    return false;
}, true, true);

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            SetLocaleMiddleware::class,
        ]);
        $middleware->alias([
            'permiso' => CheckLatPermission::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('panel'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejo centralizado de excepciones
    })->create();
