<?php

namespace App\Http\Controllers;

use App\Models\Planta;
use App\Models\Tina;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * ============================================================================
 * CONTROLADOR ADMINISTRATIVO: PLANTAS Y CREACIÓN DE USUARIOS PERSONALIZADOS
 * ============================================================================
 * Gestiona plantas lácteas (permiso 'admin.plantas') y creación/edición de
 * usuarios con credenciales propias y permisos granulares (permiso 'admin.usuarios').
 */
class AdminController extends Controller
{
    private function asegurarPermiso(Request $request, string $permiso): User
    {
        $user = $request->user();
        if (! $user || ! $user->tienePermiso($permiso)) {
            abort(403, 'No tienes permisos para realizar esta acción administrativa.');
        }

        return $user;
    }

    /**
     * Resuelve y valida los permisos solicitados respetando la jerarquía de rango.
     *
     * @param  array<int, string>|null  $permisosInput
     * @return list<string>
     */
    private function resolverPermisosPermitidos(User $actor, string $cargo, ?array $permisosInput): array
    {
        if ($cargo === 'administracion') {
            if (! $actor->esAdmin()) {
                abort(403, 'Solo un administrador general puede otorgar el rol de administración.');
            }

            return User::todasLasClavesPermisos();
        }

        $asignables = $actor->permisosAsignables();

        if ($permisosInput === null) {
            $plantilla = User::PLANTILLAS_CARGO[$cargo] ?? [];

            return array_values(array_intersect($plantilla, $asignables));
        }

        $limpios = array_values(array_unique(array_filter($permisosInput, fn ($p): bool => is_string($p) && $p !== '')));
        $fueraDeRango = array_diff($limpios, $asignables);
        if ($fueraDeRango !== []) {
            abort(403, 'No puedes asignar permisos que superan tu propio rango de acceso.');
        }

        return array_values(array_intersect(User::todasLasClavesPermisos(), $limpios));
    }

    /**
     * Resuelve la etiqueta de rango en base al cargo y los permisos otorgados.
     *
     * @param  list<string>  $permisos
     */
    private function resolverRango(string $cargo, ?string $rangoInput, array $permisos): string
    {
        if ($cargo === 'administracion') {
            return 'administrador';
        }

        if ($rangoInput && in_array($rangoInput, ['supervisor', 'operador', 'personalizado'], true)) {
            return $rangoInput;
        }

        if (in_array('admin.usuarios', $permisos, true)) {
            return 'supervisor';
        }

        return $cargo === 'personalizado' ? 'personalizado' : 'operador';
    }

    /** Crea un nuevo usuario / operario o cuenta personalizada con credenciales propias */
    public function crearUsuario(Request $request): RedirectResponse
    {
        $actor = $this->asegurarPermiso($request, 'admin.usuarios');

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'usuario' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,usuario'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'cargo' => ['required', Rule::in(User::CARGOS_VALIDOS)],
            'rango' => ['nullable', Rule::in(User::RANGOS_VALIDOS)],
            'planta_id' => ['required', 'exists:plantas,id'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string'],
        ], [
            'usuario.unique' => 'Ese identificador de usuario ya existe.',
        ]);

        if (! $actor->esAdmin() && (int) $datos['planta_id'] !== (int) $actor->planta_id) {
            abort(403, 'Solo puedes crear usuarios para tu propia planta asignada.');
        }

        $permisosInput = $request->has('permisos') ? ($datos['permisos'] ?? []) : null;
        if ($datos['cargo'] === 'personalizado' && empty($permisosInput)) {
            return back()->withErrors(['permisos' => 'Selecciona al menos una sección o control para el usuario personalizado.']);
        }

        $permisosFinales = $this->resolverPermisosPermitidos($actor, $datos['cargo'], $permisosInput);
        $rangoFinal = $this->resolverRango($datos['cargo'], $datos['rango'] ?? null, $permisosFinales);

        User::create([
            'planta_id' => $actor->esAdmin() ? $datos['planta_id'] : $actor->planta_id,
            'name' => $datos['name'],
            'usuario' => strtolower($datos['usuario']),
            'email' => ! empty($datos['email']) ? $datos['email'] : null,
            'password' => Hash::make($datos['password']),
            'cargo' => $datos['cargo'],
            'rango' => $rangoFinal,
            'permisos' => $permisosFinales,
            'activo' => true,
            'creado_por' => $actor->id,
        ]);

        return back()->with('exito', 'Credenciales y permisos del usuario creados correctamente.');
    }

    /** Actualiza cargo, permisos por sección, planta, estado o contraseña de un usuario */
    public function actualizarUsuario(Request $request, int $id): RedirectResponse
    {
        $actor = $this->asegurarPermiso($request, 'admin.usuarios');
        $target = User::findOrFail($id);

        if (! $actor->puedeGestionarUsuario($target)) {
            abort(403, 'Tu rango no permite modificar esta cuenta.');
        }

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'usuario' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users', 'usuario')->ignore($target->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target->id)],
            'cargo' => ['required', Rule::in(User::CARGOS_VALIDOS)],
            'rango' => ['nullable', Rule::in(User::RANGOS_VALIDOS)],
            'planta_id' => ['required', 'exists:plantas,id'],
            'activo' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string'],
        ]);

        if (! $actor->esAdmin() && (int) $datos['planta_id'] !== (int) $actor->planta_id) {
            abort(403, 'No puedes trasladar usuarios a otra planta.');
        }

        $activo = $request->has('activo') ? (bool) $request->boolean('activo') : $target->activo;
        if ($target->id === $actor->id && ! $activo) {
            return back()->withErrors(['usuario' => 'No puedes desactivar tu propia cuenta mientras estás en sesión.']);
        }

        // Si el formulario incluyó el selector granular (marcador permisos_present=1) o un arreglo permisos[],
        // respetamos exactamente los checkboxes enviados (incluso si se desmarcan todos en cargo personalizado).
        if ($request->boolean('permisos_present') || $request->has('permisos')) {
            $permisosInput = $datos['permisos'] ?? [];
        } else {
            $permisosInput = $datos['cargo'] === $target->cargo ? $target->permisosEfectivos() : null;
        }

        if ($datos['cargo'] === 'personalizado' && empty($permisosInput)) {
            return back()->withErrors(['permisos' => 'Un usuario personalizado debe conservar al menos un permiso habilitado.']);
        }

        $permisosFinales = $this->resolverPermisosPermitidos($actor, $datos['cargo'], $permisosInput);
        $rangoFinal = $this->resolverRango($datos['cargo'], $datos['rango'] ?? null, $permisosFinales);

        $payload = [
            'planta_id' => $actor->esAdmin() ? $datos['planta_id'] : $actor->planta_id,
            'name' => $datos['name'],
            'usuario' => strtolower($datos['usuario']),
            'email' => ! empty($datos['email']) ? $datos['email'] : null,
            'cargo' => $datos['cargo'],
            'rango' => $rangoFinal,
            'permisos' => $permisosFinales,
            'activo' => $activo,
        ];

        if (! empty($datos['password'])) {
            $payload['password'] = Hash::make($datos['password']);
        }

        $target->update($payload);

        return back()->with('exito', 'Usuario y permisos actualizados correctamente.');
    }

    /** Crea una nueva planta láctea junto con su tina inicial */
    public function crearPlanta(Request $request): RedirectResponse
    {
        $this->asegurarPermiso($request, 'admin.plantas');

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20', 'unique:plantas,codigo'],
            'nombre' => ['required', 'string', 'max:140'],
            'ubicacion' => ['required', 'string', 'max:140'],
            'precio_litro' => ['required', 'numeric', 'min:0.5', 'max:20'],
        ]);

        $planta = DB::transaction(function () use ($datos): Planta {
            $p = Planta::create([
                'codigo' => strtoupper($datos['codigo']),
                'nombre' => $datos['nombre'],
                'ubicacion' => $datos['ubicacion'],
                'precio_litro' => $datos['precio_litro'],
                'activa' => true,
            ]);

            Tina::create([
                'planta_id' => $p->id,
                'codigo' => 'TINA-01',
                'nombre' => 'Tina Principal',
                'capacidad_litros' => 500,
                'tipo' => 'principal_queso',
                'activa' => true,
            ]);

            return $p;
        });

        $request->session()->put('planta_activa_id', $planta->id);

        return back()->with('exito', 'Planta "'.$planta->nombre.'" agregada y activada correctamente.');
    }

    /** Edita los datos operativos o estado de una planta */
    public function actualizarPlanta(Request $request, int $id): RedirectResponse
    {
        $this->asegurarPermiso($request, 'admin.plantas');
        $planta = Planta::findOrFail($id);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('plantas', 'codigo')->ignore($planta->id)],
            'nombre' => ['required', 'string', 'max:140'],
            'ubicacion' => ['required', 'string', 'max:140'],
            'precio_litro' => ['required', 'numeric', 'min:0.5', 'max:20'],
            'activa' => ['nullable', 'boolean'],
        ]);

        $planta->update([
            'codigo' => strtoupper($datos['codigo']),
            'nombre' => $datos['nombre'],
            'ubicacion' => $datos['ubicacion'],
            'precio_litro' => $datos['precio_litro'],
            'activa' => $request->has('activa') ? $request->boolean('activa') : $planta->activa,
        ]);

        return back()->with('exito', 'Datos de la planta actualizados correctamente.');
    }
}
