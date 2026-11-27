<?php

use App\Http\Middleware\CheckLatPermission;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\SetLocaleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $proxies = env('TRUSTED_PROXIES');
        if ($proxies !== null && $proxies !== '') {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : array_map('trim', explode(',', (string) $proxies)));
        }

        $middleware->web(append: [
            SetLocaleMiddleware::class,
            SecurityHeadersMiddleware::class,
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
