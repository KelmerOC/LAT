<?php

namespace App\Http\Controllers;

use App\Models\Planta;
use App\Models\Tina;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ============================================================================
 * CONTROLADOR: AUTENTICACIÓN, REGISTRO PÚBLICO E IDIOMA
 * ============================================================================
 * Gestiona inicio/cierre de sesión, registro público de nuevas plantas/cuentas
 * y persistencia del idioma elegido (es / qu / en).
 */
class AuthController extends Controller
{
    /** Muestra el formulario de acceso */
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('panel');
        }

        return view('login');
    }

    /** Procesa las credenciales de ingreso */
    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Ingrese su usuario o correo electrónico.',
            'password.required' => 'Ingrese su contraseña.',
        ]);

        $campo = filter_var($credenciales['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'usuario';

        if (Auth::attempt([$campo => $credenciales['login'], 'password' => $credenciales['password'], 'activo' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('panel'))->with('exito', 'Bienvenido a Mi Panel Lácteo.');
        }

        return back()->withErrors([
            'login' => 'Credenciales no válidas o cuenta inactiva.',
        ])->onlyInput('login');
    }

    /** Muestra el formulario de registro público */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('panel');
        }

        return view('register');
    }

    /** Registra un nuevo usuario administrador junto con su planta láctea inicial */
    public function register(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'usuario' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,usuario'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'planta_nombre' => ['required', 'string', 'max:140'],
            'planta_ubicacion' => ['required', 'string', 'max:140'],
            'precio_litro' => ['nullable', 'numeric', 'min:0.5', 'max:20'],
        ], [
            'usuario.unique' => 'Este nombre de usuario ya está en uso.',
            'usuario.regex' => 'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'email.unique' => 'Este correo electrónico ya se encuentra registrado.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'planta_nombre.required' => 'Ingrese el nombre de su planta o quesería.',
        ]);

        $user = DB::transaction(function () use ($datos) {
            $siguienteId = (Planta::max('id') ?? 0) + 1;
            $codigoPlanta = 'PLT-'.str_pad((string) $siguienteId, 2, '0', STR_PAD_LEFT);
            while (Planta::where('codigo', $codigoPlanta)->exists()) {
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
                'codigo' => 'TINA-01',
                'nombre' => 'Tina Principal',
                'capacidad_litros' => 500,
                'tipo' => 'principal_queso',
                'activa' => true,
            ]);

            return User::create([
                'planta_id' => $planta->id,
                'name' => $datos['name'],
                'usuario' => strtolower($datos['usuario']),
                'email' => ! empty($datos['email']) ? $datos['email'] : null,
                'password' => Hash::make($datos['password']),
                'cargo' => 'administracion',
                'rango' => 'administrador',
                'permisos' => User::PLANTILLAS_CARGO['administracion'],
                'activo' => true,
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('panel')->with('exito', 'Cuenta y planta registradas correctamente.');
    }

    /** Cierra la sesión activa */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio')->with('info', 'Sesión cerrada correctamente.');
    }

    /** Persiste la selección de idioma en la sesión del servidor */
    public function cambiarIdioma(Request $request, string $lang)
    {
        if (! in_array($lang, ['es', 'qu', 'en'], true)) {
            $lang = 'es';
        }

        $request->session()->put('locale', $lang);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'locale' => $lang]);
        }

        return back();
    }
}
