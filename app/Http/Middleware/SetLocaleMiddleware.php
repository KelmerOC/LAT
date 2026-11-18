<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica el idioma guardado en sesión (es / qu / en) a cada petición web.
 */
class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale', 'es'));
        if (in_array($locale, ['es', 'qu', 'en'], true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
