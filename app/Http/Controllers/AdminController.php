<?php

namespace App\Http\Controllers;

use App\Models\Planta;
use App\Models\Tina;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * ============================================================================
 * CONTROLADOR ADMINISTRATIVO: PLANTAS Y CREACIÓN DE CREDENCIALES DE USUARIOS
 * ============================================================================
 * Exclusivo para el rol 'administracion'. Gestiona alta/edición de plantas y
 * creación/edición de credenciales para operarios sin exigir correo obligatorio.
 */
class AdminController extends Controller
{
    private function asegurarAdmin(Request $request): void
    {
        if (!$request->user() || !$request->user()->esAdmin()) {
            abort(403, 'Solo el administrador puede realizar esta acción.');
        }
    }

    /** Crea un nuevo usuario / operario con credenciales desde el panel admin */
    public function crearUsuario(Request $request)
    {
        $this->asegurarAdmin($request);

        $datos = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'usuario'   => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,usuario'],
            'email'     => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:6'],
            'cargo'     => ['required', 'in:administracion,encargado_planta,recolector_ruta,control_tina,contador_externo'],
            'planta_id' => ['required', 'exists:plantas,id'],
        ], [
            'usuario.unique' => 'Ese identificador de usuario ya existe.',
        ]);

        User::create([
            'planta_id' => $datos['planta_id'],
            'name'      => $datos['name'],
            'usuario'   => strtolower($datos['usuario']),
            'email'     => !empty($datos['email']) ? $datos['email'] : null,
            'password'  => Hash::make($datos['password']),
            'cargo'     => $datos['cargo'],
            'activo'    => true,
        ]);

        return back()->with('exito', 'Credenciales de usuario creadas correctamente.');
    }

    /** Actualiza cargo, planta, estado o contraseña de un usuario */
    public function actualizarUsuario(Request $request, int $id)
    {
        $this->asegurarAdmin($request);
        $target = User::findOrFail($id);

        $datos = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'usuario'   => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users', 'usuario')->ignore($target->id)],
            'email'     => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target->id)],
            'cargo'     => ['required', 'in:administracion,encargado_planta,recolector_ruta,control_tina,contador_externo'],
            'planta_id' => ['required', 'exists:plantas,id'],
            'activo'    => ['nullable', 'boolean'],
            'password'  => ['nullable', 'string', 'min:6'],
        ]);

        $activo = $request->has('activo') ? (bool) $request->boolean('activo') : $target->activo;
        if ($target->id === $request->user()->id && !$activo) {
            return back()->withErrors(['usuario' => 'No puedes desactivar tu propia cuenta mientras estás en sesión.']);
        }

        $payload = [
            'planta_id' => $datos['planta_id'],
            'name'      => $datos['name'],
            'usuario'   => strtolower($datos['usuario']),
            'email'     => !empty($datos['email']) ? $datos['email'] : null,
            'cargo'     => $datos['cargo'],
            'activo'    => $activo,
        ];

        if (!empty($datos['password'])) {
            $payload['password'] = Hash::make($datos['password']);
        }

        $target->update($payload);

        return back()->with('exito', 'Usuario actualizado correctamente.');
    }

    /** Crea una nueva planta láctea junto con su tina inicial */
    public function crearPlanta(Request $request)
    {
        $this->asegurarAdmin($request);

        $datos = $request->validate([
            'codigo'       => ['required', 'string', 'max:20', 'unique:plantas,codigo'],
            'nombre'       => ['required', 'string', 'max:140'],
            'ubicacion'    => ['required', 'string', 'max:140'],
            'precio_litro' => ['required', 'numeric', 'min:0.5', 'max:20'],
        ]);

        $planta = DB::transaction(function () use ($datos) {
            $p = Planta::create([
                'codigo'       => strtoupper($datos['codigo']),
                'nombre'       => $datos['nombre'],
                'ubicacion'    => $datos['ubicacion'],
                'precio_litro' => $datos['precio_litro'],
                'activa'       => true,
            ]);

            Tina::create([
                'planta_id'        => $p->id,
                'codigo'           => 'TINA-01',
                'nombre'           => 'Tina Principal',
                'capacidad_litros' => 500,
                'tipo'             => 'principal_queso',
                'activa'           => true,
            ]);

            return $p;
        });

        $request->session()->put('planta_activa_id', $planta->id);

        return back()->with('exito', 'Planta "'.$planta->nombre.'" agregada y activada correctamente.');
    }

    /** Edita los datos operativos o estado de una planta */
    public function actualizarPlanta(Request $request, int $id)
    {
        $this->asegurarAdmin($request);
        $planta = Planta::findOrFail($id);

        $datos = $request->validate([
            'codigo'       => ['required', 'string', 'max:20', Rule::unique('plantas', 'codigo')->ignore($planta->id)],
            'nombre'       => ['required', 'string', 'max:140'],
            'ubicacion'    => ['required', 'string', 'max:140'],
            'precio_litro' => ['required', 'numeric', 'min:0.5', 'max:20'],
            'activa'       => ['nullable', 'boolean'],
        ]);

        $planta->update([
            'codigo'       => strtoupper($datos['codigo']),
            'nombre'       => $datos['nombre'],
            'ubicacion'    => $datos['ubicacion'],
            'precio_litro' => $datos['precio_litro'],
            'activa'       => $request->has('activa') ? $request->boolean('activa') : $planta->activa,
        ]);

        return back()->with('exito', 'Datos de la planta actualizados correctamente.');
    }
}
