<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de autorización granular por sección y control operativo.
 */
class CheckLatPermission
{
    /**
     * Verifica que el usuario autenticado posea al menos uno de los permisos requeridos.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permisos): Response
    {
        $user = $request->user();

        if (! $user || ! $user->activo) {
            abort(403, __('lat.account_inactive_or_unauthenticated'));
        }

        $requeridos = [];
        foreach ($permisos as $item) {
            foreach (explode('|', $item) as $clave) {
                $claveLimpia = trim($clave);
                if ($claveLimpia !== '') {
                    $requeridos[] = $claveLimpia;
                }
            }
        }

        if ($requeridos !== [] && ! $user->tieneAlgunPermiso($requeridos)) {
            abort(403, __('lat.permission_denied_action'));
        }

        return $next($request);
    }
}
