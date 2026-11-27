<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica el idioma guardado en sesión o cookie (es / qu / en) a cada petición web.
 */
class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $permitidos = ['es', 'qu', 'en'];
        $sessionLocale = $request->session()->get('locale');
        $cookieLocale = $request->cookie('lat_locale');

        if (is_string($sessionLocale) && in_array($sessionLocale, $permitidos, true)) {
            $locale = $sessionLocale;
        } elseif (is_string($cookieLocale) && in_array($cookieLocale, $permitidos, true)) {
            $locale = $cookieLocale;
            $request->session()->put('locale', $locale);
        } else {
            $defaultLocale = (string) config('app.locale', 'es');
            $locale = in_array($defaultLocale, $permitidos, true) ? $defaultLocale : 'es';
        }

        App::setLocale($locale);
        Carbon::setLocale($locale === 'qu' ? 'es' : $locale);

        return $next($request);
    }
}
