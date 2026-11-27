<?php

namespace App\Http\Controllers;

use App\Models\Planta;
use App\Models\Tina;
use App\Models\User;
use App\Services\InformePlantaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Gestión administrativa de plantas lácteas (con papelera de 30 días), usuarios y permisos granulares.
 */
class AdminController extends Controller
{
    public function __construct(protected InformePlantaService $informes) {}

    private function asegurarPermiso(Request $request, string $permiso): User
    {
        $user = $request->user();
        if (! $user || ! $user->tienePermiso($permiso)) {
            abort(403, __('lat.admin_forbidden'));
        }

        return $user;
    }

    /**
     * Retorna los permisos almacenados en BD sin depender del estado activo del usuario.
     *
     * @return list<string>
     */
    private function permisosGuardados(User $user): array
    {
        if ($user->esAdmin()) {
            return User::PLANTILLAS_CARGO['administracion'];
        }

        if (is_array($user->permisos)) {
            return array_values(array_intersect(User::todasLasClavesPermisos(), $user->permisos));
        }

        return User::PLANTILLAS_CARGO[$user->cargo] ?? [];
    }

    /**
     * Resuelve y valida los permisos solicitados respetando el alcance del actor.
     *
     * @param  array<int, string>|null  $permisosInput
     * @return list<string>
     */
    private function resolverPermisosPermitidos(User $actor, string $cargo, ?array $permisosInput, ?User $target = null): array
    {
        if ($cargo === 'administracion') {
            if (! $actor->esAdmin()) {
                abort(403, __('lat.admin_only_grant_admin'));
            }

            return User::PLANTILLAS_CARGO['administracion'];
        }

        $prohibidosNoAdmin = ['admin.plantas', 'dashboard.ver_red', 'admin.usuarios'];
        if (! $actor->esAdmin() && $permisosInput !== null && array_intersect($permisosInput, $prohibidosNoAdmin) !== []) {
            abort(403, __('lat.admin_permissions_exceed_rank'));
        }

        $asignables = $actor->permisosAsignables();
        $fueraDeAlcanceActuales = ($target && ! $actor->esAdmin())
            ? array_values(array_diff($this->permisosGuardados($target), $asignables, $prohibidosNoAdmin))
            : [];

        if ($permisosInput === null) {
            $plantilla = User::PLANTILLAS_CARGO[$cargo] ?? [];
            $base = array_values(array_intersect($plantilla, $asignables));
            $combinados = array_values(array_unique(array_merge($base, $fueraDeAlcanceActuales)));

            return array_values(array_intersect(User::todasLasClavesPermisos(), $combinados));
        }

        $limpios = array_values(array_unique(array_filter($permisosInput, fn ($p): bool => is_string($p) && $p !== '')));
        $permitidosParaActor = array_values(array_unique(array_merge($asignables, $fueraDeAlcanceActuales)));

        if (array_diff($limpios, $permitidosParaActor) !== []) {
            abort(403, __('lat.admin_permissions_exceed_rank'));
        }

        $combinados = array_values(array_unique(array_merge($limpios, $fueraDeAlcanceActuales)));

        return array_values(array_intersect(User::todasLasClavesPermisos(), $combinados));
    }

    /**
     * Resuelve la etiqueta de rango respetando la jerarquía del cargo y permisos.
     *
     * @param  list<string>  $permisos
     */
    private function resolverRango(string $cargo, ?string $rangoInput, array $permisos): string
    {
        if ($cargo === 'administracion') {
            return 'administrador';
        }

        if (in_array('admin.usuarios', $permisos, true) || ($cargo === 'encargado_planta' && $rangoInput === 'supervisor')) {
            return 'supervisor';
        }

        if ($rangoInput && in_array($rangoInput, ['operador', 'personalizado'], true)) {
            return $rangoInput;
        }

        return $cargo === 'personalizado' ? 'personalizado' : 'operador';
    }

    private function normalizarUsuarioInput(Request $request): void
    {
        if ($request->has('usuario')) {
            $request->merge([
                'usuario' => strtolower(trim((string) $request->input('usuario'))),
            ]);
        }
    }

    private function normalizarCodigoPlantaInput(Request $request): void
    {
        if (! $request->has('codigo')) {
            return;
        }

        $valor = strtoupper(trim((string) $request->input('codigo')));
        $sufijo = trim((string) preg_replace('/^PLT[\s-]*/i', '', $valor));

        $request->merge([
            'codigo' => $sufijo !== '' ? 'PLT-'.$sufijo : '',
        ]);
    }

    public function crearUsuario(Request $request): RedirectResponse
    {
        $actor = $this->asegurarPermiso($request, 'admin.usuarios');
        $this->normalizarUsuarioInput($request);

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'usuario' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,usuario'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'cargo' => ['required', Rule::in(User::CARGOS_VALIDOS)],
            'rango' => ['nullable', Rule::in(User::RANGOS_VALIDOS)],
            'planta_id' => ['required', Rule::exists('plantas', 'id')->whereNull('deleted_at')],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string'],
        ], [
            'usuario.unique' => __('lat.admin_usuario_unique'),
        ]);

        if (! $actor->esAdmin() && ($actor->planta_id === null || (int) $datos['planta_id'] !== (int) $actor->planta_id)) {
            abort(403, __('lat.admin_only_own_plant_create'));
        }

        $permisosInput = $request->boolean('permisos_present') ? ($datos['permisos'] ?? []) : null;
        if ($datos['cargo'] === 'personalizado' && empty($permisosInput)) {
            return back()->withErrors(['permisos' => __('lat.admin_custom_needs_permission')]);
        }

        $permisosFinales = $this->resolverPermisosPermitidos($actor, $datos['cargo'], $permisosInput);
        $rangoFinal = $this->resolverRango($datos['cargo'], $datos['rango'] ?? null, $permisosFinales);

        $creado = User::create([
            'planta_id' => $actor->esAdmin() ? $datos['planta_id'] : $actor->planta_id,
            'name' => $datos['name'],
            'usuario' => $datos['usuario'],
            'email' => ! empty($datos['email']) ? $datos['email'] : null,
            'password' => Hash::make($datos['password']),
            'cargo' => $datos['cargo'],
            'rango' => $rangoFinal,
            'permisos' => $permisosFinales,
            'activo' => true,
            'creado_por' => $actor->id,
        ]);

        if ($creado->planta_id !== null) {
            $this->informes->registrarActividad(
                (int) $creado->planta_id,
                $actor->id,
                'admin',
                'usuario_creado',
                "Usuario creado: {$creado->name} (@{$creado->usuario}, {$creado->cargo}).",
                ['target_user_id' => $creado->id, 'cargo' => $creado->cargo]
            );
        }

        return back()->with('exito', __('lat.admin_user_created'));
    }

    public function actualizarUsuario(Request $request, int $id): RedirectResponse
    {
        $actor = $this->asegurarPermiso($request, 'admin.usuarios');
        $target = User::findOrFail($id);

        if (! $actor->puedeGestionarUsuario($target)) {
            abort(403, __('lat.admin_rank_cannot_edit_user'));
        }

        $this->normalizarUsuarioInput($request);

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'usuario' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users', 'usuario')->ignore($target->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target->id)],
            'cargo' => ['required', Rule::in(User::CARGOS_VALIDOS)],
            'rango' => ['nullable', Rule::in(User::RANGOS_VALIDOS)],
            'planta_id' => ['required', Rule::exists('plantas', 'id')->whereNull('deleted_at')],
            'activo' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['string'],
        ]);

        if (! $actor->esAdmin() && ($actor->planta_id === null || (int) $datos['planta_id'] !== (int) $actor->planta_id)) {
            abort(403, __('lat.admin_cannot_transfer_plant'));
        }

        $activo = $request->has('activo') ? $request->boolean('activo') : $target->activo;
        if ($target->id === $actor->id && ! $activo) {
            return back()->withErrors(['usuario' => __('lat.admin_cannot_deactivate_self')]);
        }

        if ($request->boolean('permisos_present') || $request->has('permisos')) {
            $permisosInput = $datos['permisos'] ?? [];
        } else {
            $permisosInput = $datos['cargo'] === $target->cargo ? $this->permisosGuardados($target) : null;
        }

        $permisosFinales = $this->resolverPermisosPermitidos($actor, $datos['cargo'], $permisosInput, $target);

        if ($datos['cargo'] === 'personalizado' && $permisosFinales === []) {
            return back()->withErrors(['permisos' => __('lat.admin_custom_keep_permission')]);
        }

        $rangoFinal = $this->resolverRango($datos['cargo'], $datos['rango'] ?? null, $permisosFinales);

        $payload = [
            'planta_id' => $actor->esAdmin() ? $datos['planta_id'] : $actor->planta_id,
            'name' => $datos['name'],
            'usuario' => $datos['usuario'],
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

        if ($target->planta_id !== null) {
            $this->informes->registrarActividad(
                (int) $target->planta_id,
                $actor->id,
                'admin',
                'usuario_actualizado',
                "Usuario actualizado: {$target->name} (@{$target->usuario}, {$target->cargo}).",
                ['target_user_id' => $target->id, 'activo' => $target->activo]
            );
        }

        return back()->with('exito', __('lat.admin_user_updated'));
    }

    public function eliminarUsuario(Request $request, int $id): RedirectResponse
    {
        $actor = $this->asegurarPermiso($request, 'admin.usuarios');
        $target = User::findOrFail($id);

        if ($target->id === $actor->id) {
            return back()->withErrors(['usuario' => 'No puedes eliminar tu propia cuenta mientras estás en sesión.']);
        }

        if (! $actor->puedeGestionarUsuario($target)) {
            abort(403, __('lat.admin_rank_cannot_edit_user'));
        }

        $plantaId = $target->planta_id;
        $nombre = $target->name;
        $usuarioNombre = $target->usuario;
        $targetId = $target->id;

        $target->delete();

        if ($plantaId !== null) {
            $this->informes->registrarActividad(
                (int) $plantaId,
                $actor->id,
                'admin',
                'usuario_eliminado',
                "Usuario eliminado: {$nombre} (@{$usuarioNombre}).",
                ['target_user_id' => $targetId]
            );
        }

        return back()->with('exito', 'Usuario eliminado del sistema correctamente.');
    }

    public function crearPlanta(Request $request): RedirectResponse
    {
        $this->asegurarPermiso($request, 'admin.plantas');
        $this->normalizarCodigoPlantaInput($request);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20', 'regex:/^PLT-[A-Z0-9][A-Z0-9-]*$/', 'unique:plantas,codigo'],
            'nombre' => ['required', 'string', 'max:140'],
            'ubicacion' => ['required', 'string', 'max:140'],
            'precio_litro' => ['required', 'numeric', 'min:0.5', 'max:20'],
        ]);

        $planta = DB::transaction(function () use ($datos): Planta {
            $p = Planta::create([
                'codigo' => $datos['codigo'],
                'nombre' => $datos['nombre'],
                'ubicacion' => $datos['ubicacion'],
                'precio_litro' => $datos['precio_litro'],
                'activa' => true,
            ]);

            Tina::create([
                'planta_id' => $p->id,
                'codigo' => app()->getLocale() === 'en' ? 'VAT-01' : 'TINA-01',
                'nombre' => __('lat.default_main_vat'),
                'capacidad_litros' => 500,
                'tipo' => 'principal_queso',
                'activa' => true,
            ]);

            return $p;
        });

        $request->session()->put('planta_activa_id', $planta->id);

        return back()->with('exito', __('lat.admin_plant_created', ['name' => $planta->nombre]));
    }

    public function actualizarPlanta(Request $request, int $id): RedirectResponse
    {
        $this->asegurarPermiso($request, 'admin.plantas');
        $planta = Planta::findOrFail($id);
        $this->normalizarCodigoPlantaInput($request);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20', 'regex:/^PLT-[A-Z0-9][A-Z0-9-]*$/', Rule::unique('plantas', 'codigo')->ignore($planta->id)],
            'nombre' => ['required', 'string', 'max:140'],
            'ubicacion' => ['required', 'string', 'max:140'],
            'precio_litro' => ['required', 'numeric', 'min:0.5', 'max:20'],
            'activa' => ['nullable', 'boolean'],
        ]);

        $planta->update([
            'codigo' => $datos['codigo'],
            'nombre' => $datos['nombre'],
            'ubicacion' => $datos['ubicacion'],
            'precio_litro' => $datos['precio_litro'],
            'activa' => $request->has('activa') ? $request->boolean('activa') : $planta->activa,
        ]);

        return back()->with('exito', __('lat.admin_plant_updated'));
    }

    /**
     * Envía una planta a la papelera (Tacho) con retención de 30 días antes del borrado definitivo.
     */
    public function eliminarPlanta(Request $request, int $id): RedirectResponse
    {
        $actor = $this->asegurarPermiso($request, 'admin.plantas');
        if (! $actor->esAdmin()) {
            abort(403, __('lat.admin_forbidden'));
        }

        if (Planta::count() <= 1) {
            return back()->withErrors(['planta' => 'No puedes eliminar la última planta activa del sistema.']);
        }

        $planta = Planta::findOrFail($id);

        DB::transaction(function () use ($planta, $actor): void {
            $planta->update([
                'activa' => false,
                'eliminado_por' => $actor->id,
            ]);
            $planta->delete();
        });

        if ((int) $request->session()->get('planta_activa_id') === $planta->id) {
            $siguiente = Planta::orderBy('id')->first();
            if ($siguiente) {
                $request->session()->put('planta_activa_id', $siguiente->id);
            } else {
                $request->session()->forget('planta_activa_id');
            }
        }

        return back()->with('exito', "Planta \"{$planta->nombre}\" enviada al tacho. Podrás restaurarla durante ".Planta::DIAS_RETENCION_PAPELERA.' días.');
    }

    /**
     * Restaura una planta desde la papelera (Tacho) manteniendo intactos sus datos y usuarios.
     */
    public function restaurarPlanta(Request $request, int $id): RedirectResponse
    {
        $actor = $this->asegurarPermiso($request, 'admin.plantas');
        if (! $actor->esAdmin()) {
            abort(403, __('lat.admin_forbidden'));
        }

        $planta = Planta::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($planta): void {
            $planta->restore();
            $planta->update([
                'activa' => true,
                'eliminado_por' => null,
            ]);
        });

        $request->session()->put('planta_activa_id', $planta->id);

        return back()->with('exito', "Planta \"{$planta->nombre}\" restaurada correctamente.");
    }

    /**
     * Elimina de forma permanente e irreversible una planta que está en la papelera.
     */
    public function eliminarPlantaDefinitivo(Request $request, int $id): RedirectResponse
    {
        $actor = $this->asegurarPermiso($request, 'admin.plantas');
        if (! $actor->esAdmin()) {
            abort(403, __('lat.admin_forbidden'));
        }

        $planta = Planta::onlyTrashed()->findOrFail($id);
        $nombre = $planta->nombre;
        $planta->forceDelete();

        return back()->with('exito', "Planta \"{$nombre}\" eliminada definitivamente del sistema.");
    }
}
