<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================================================
 * CONTROLADOR: AUTENTICACIÓN Y SEGURIDAD UNIVERSITARIA
 * ============================================================================
 * Gestiona el inicio y cierre de sesión de administradores y operarios.
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
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required'    => 'Ingrese su usuario o correo electrónico.',
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

    /** Cierra la sesión activa */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio')->with('info', 'Sesión cerrada correctamente.');
    }
}
