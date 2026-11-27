<?php

namespace App\Http\Controllers;

use App\Models\Planta;
use App\Models\Tina;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Controlador de autenticación, registro público de plantas e idioma.
 */
class AuthController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('panel');
        }

        return view('login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => __('lat.auth_login_required'),
            'password.required' => __('lat.auth_password_required'),
        ]);

        $loginNormalizado = strtolower(trim((string) $credenciales['login']));
        $throttleKey = Str::transliterate($loginNormalizado.'|'.$request->ip());
        $ipThrottleKey = 'login-ip|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5) || RateLimiter::tooManyAttempts($ipThrottleKey, 20)) {
            $segundos = max(RateLimiter::availableIn($throttleKey), RateLimiter::availableIn($ipThrottleKey));

            return back()->withErrors([
                'login' => __('lat.auth_throttle', ['seconds' => $segundos]),
            ])->onlyInput('login');
        }

        $esCorreo = filter_var(trim((string) $credenciales['login']), FILTER_VALIDATE_EMAIL) !== false;
        $campo = $esCorreo ? 'email' : 'usuario';
        $valorLogin = $esCorreo ? trim((string) $credenciales['login']) : $loginNormalizado;

        if (Auth::attempt([$campo => $valorLogin, 'password' => $credenciales['password'], 'activo' => true], $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            RateLimiter::clear($ipThrottleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('panel'))->with('exito', __('lat.auth_welcome'));
        }

        RateLimiter::hit($throttleKey, 60);
        RateLimiter::hit($ipThrottleKey, 60);

        return back()->withErrors([
            'login' => __('lat.auth_invalid_credentials'),
        ])->onlyInput('login');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('panel');
        }

        return view('register');
    }

    public function register(Request $request): RedirectResponse
    {
        $merge = [];
        if ($request->has('usuario')) {
            $merge['usuario'] = strtolower(trim((string) $request->input('usuario')));
        }
        if ($request->has('email') && $request->input('email') !== null) {
            $emailTrim = trim((string) $request->input('email'));
            $merge['email'] = $emailTrim === '' ? null : strtolower($emailTrim);
        }
        if ($merge !== []) {
            $request->merge($merge);
        }

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'usuario' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,usuario'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'planta_nombre' => ['required', 'string', 'max:140'],
            'planta_ubicacion' => ['required', 'string', 'max:140'],
            'precio_litro' => ['nullable', 'numeric', 'min:0.5', 'max:20'],
        ], [
            'usuario.unique' => __('lat.auth_usuario_unique'),
            'usuario.regex' => __('lat.auth_usuario_regex'),
            'email.unique' => __('lat.auth_email_unique'),
            'password.confirmed' => __('lat.auth_password_confirmed'),
            'planta_nombre.required' => __('lat.auth_planta_nombre_required'),
        ]);

        $user = DB::transaction(function () use ($datos): User {
            $siguienteId = (int) (Planta::withTrashed()->lockForUpdate()->max('id') ?? 0) + 1;
            $codigoPlanta = 'PLT-'.str_pad((string) $siguienteId, 2, '0', STR_PAD_LEFT);
            while (Planta::withTrashed()->where('codigo', $codigoPlanta)->exists()) {
                $siguienteId++;
                $codigoPlanta = 'PLT-'.str_pad((string) $siguienteId, 2, '0', STR_PAD_LEFT);
            }

            $planta = Planta::create([
                'codigo' => $codigoPlanta,
                'nombre' => $datos['planta_nombre'],
                'ubicacion' => $datos['planta_ubicacion'],
                'precio_litro' => $datos['precio_litro'] ?? 1.65,
                'activa' => true,
            ]);

            Tina::create([
                'planta_id' => $planta->id,
                'codigo' => app()->getLocale() === 'en' ? 'VAT-01' : 'TINA-01',
                'nombre' => __('lat.default_main_vat'),
                'capacidad_litros' => 500,
                'tipo' => 'principal_queso',
                'activa' => true,
            ]);

            // Aislamiento multi-planta (OWASP A01): el registro público crea un supervisor de SU nueva planta,
            // nunca un administrador global de toda la red.
            $permisosPropiaPlanta = [
                'dashboard.ver',
                'acopio.ver', 'acopio.crear', 'acopio.editar', 'acopio.proveedores',
                'calidad.ver', 'calidad.evaluar',
                'tinas.ver', 'tinas.operar', 'tinas.gestionar',
                'finanzas.ver', 'finanzas.registrar', 'finanzas.editar', 'finanzas.exportar', 'finanzas.enviar_excel',
                'estadisticas.ver',
                'admin.usuarios', 'admin.qr',
            ];

            return User::create([
                'planta_id' => $planta->id,
                'name' => $datos['name'],
                'usuario' => $datos['usuario'],
                'email' => ! empty($datos['email']) ? $datos['email'] : null,
                'password' => Hash::make($datos['password']),
                'cargo' => 'encargado_planta',
                'rango' => 'supervisor',
                'permisos' => $permisosPropiaPlanta,
                'activo' => true,
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('panel')->with('exito', __('lat.auth_registered'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $localeActual = $request->session()->get('locale', $request->cookie('lat_locale', config('app.locale', 'es')));
        if (! is_string($localeActual) || ! in_array($localeActual, ['es', 'qu', 'en'], true)) {
            $localeActual = 'es';
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('locale', $localeActual);

        $cookie = cookie('lat_locale', $localeActual, 60 * 24 * 365, '/', null, false, false);

        return redirect()->route('inicio')->with('info', __('lat.auth_logout'))->withCookie($cookie);
    }

    public function cambiarIdioma(Request $request, string $lang): RedirectResponse|JsonResponse
    {
        if (! in_array($lang, ['es', 'qu', 'en'], true)) {
            $lang = 'es';
        }

        $request->session()->put('locale', $lang);
        $cookie = cookie('lat_locale', $lang, 60 * 24 * 365, '/', null, false, false);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'locale' => $lang])->withCookie($cookie);
        }

        return back()->withCookie($cookie);
    }
}
