<?php

namespace App\Http\Controllers;

use App\Models\Planta;
use App\Models\QrAcceso;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Genera y consume códigos QR temporales para registro y acceso rápido desde celular.
 */
class QrAccesoController extends Controller
{
    /** @var list<string> */
    private const CARGOS_QR_PERMITIDOS = [
        'recolector_ruta',
        'control_tina',
        'encargado_planta',
        'contador_externo',
    ];

    public function crear(Request $request): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor || ! $actor->tieneAlgunPermiso(['admin.qr', 'admin.usuarios'])) {
            abort(403, __('lat.admin_forbidden'));
        }

        $datos = $request->validate([
            'etiqueta' => ['required', 'string', 'max:120'],
            'cargo_asignado' => ['required', Rule::in(self::CARGOS_QR_PERMITIDOS)],
            'planta_id' => ['required', Rule::exists('plantas', 'id')->whereNull('deleted_at')],
            'horas_vigencia' => ['nullable', 'integer', 'min:1', 'max:168'],
            'max_usos' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if (! $actor->esAdmin() && (int) $datos['planta_id'] !== (int) $actor->planta_id) {
            abort(403, __('lat.admin_only_own_plant_create'));
        }

        $planta = Planta::findOrFail((int) $datos['planta_id']);
        $horas = (int) ($datos['horas_vigencia'] ?? 24);
        $maxUsos = (int) ($datos['max_usos'] ?? 10);
        $tokenPlano = Str::random(40);

        $qr = QrAcceso::create([
            'planta_id' => $planta->id,
            'creado_por' => $actor->id,
            'token_hash' => hash('sha256', $tokenPlano),
            'etiqueta' => $datos['etiqueta'],
            'cargo_asignado' => $datos['cargo_asignado'],
            'max_usos' => $maxUsos,
            'usos_actuales' => 0,
            'expira_en' => Carbon::now()->addHours($horas),
            'activo' => true,
        ]);

        $request->session()->flash('qr_generado', [
            'id' => $qr->id,
            'etiqueta' => $qr->etiqueta,
            'cargo' => $qr->cargo_asignado,
            'planta' => $planta->nombre,
            'url_path' => route('qr.show', ['token' => $tokenPlano], false),
            'expira_en' => $qr->expira_en->format('d/m/Y H:i'),
            'max_usos' => $qr->max_usos,
        ]);

        return back()->with('exito', "Código QR generado para {$planta->nombre}. Escanéalo desde el celular para probar.");
    }

    public function revocar(Request $request, int $id): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor || ! $actor->tieneAlgunPermiso(['admin.qr', 'admin.usuarios'])) {
            abort(403, __('lat.admin_forbidden'));
        }

        $qr = QrAcceso::when(! $actor->esAdmin(), fn ($q) => $q->where('planta_id', $actor->planta_id))
            ->findOrFail($id);

        $qr->delete();

        return back()->with('exito', 'Enlace QR revocado y desactivado.');
    }

    public function mostrar(string $token): View
    {
        $qr = QrAcceso::with('planta:id,codigo,nombre,ubicacion,deleted_at')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        $vigente = $qr !== null && $qr->estaVigente() && $qr->planta !== null && $qr->planta->deleted_at === null;

        return view('acceso-qr', [
            'token' => $token,
            'qr' => $qr,
            'vigente' => $vigente,
        ]);
    }

    public function registrar(Request $request, string $token): RedirectResponse
    {
        if ($request->has('usuario')) {
            $request->merge([
                'usuario' => strtolower(trim((string) $request->input('usuario'))),
            ]);
        }

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'usuario' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,usuario'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $tokenHash = hash('sha256', $token);

        $user = DB::transaction(function () use ($tokenHash, $datos): ?User {
            $qr = QrAcceso::where('token_hash', $tokenHash)->lockForUpdate()->first();
            if (! $qr || ! $qr->estaVigente()) {
                return null;
            }

            $planta = Planta::find($qr->planta_id);
            if (! $planta) {
                return null;
            }

            $cargo = in_array($qr->cargo_asignado, self::CARGOS_QR_PERMITIDOS, true)
                ? $qr->cargo_asignado
                : 'recolector_ruta';

            $nuevo = User::create([
                'planta_id' => $planta->id,
                'name' => $datos['name'],
                'usuario' => $datos['usuario'],
                'email' => null,
                'password' => Hash::make($datos['password']),
                'cargo' => $cargo,
                'rango' => $cargo === 'encargado_planta' ? 'supervisor' : 'operador',
                'permisos' => User::PLANTILLAS_CARGO[$cargo] ?? User::PLANTILLAS_CARGO['recolector_ruta'],
                'activo' => true,
                'creado_por' => $qr->creado_por,
            ]);

            $qr->increment('usos_actuales');

            return $nuevo;
        });

        if (! $user) {
            return back()->withErrors(['qr' => 'Este código QR expiró, alcanzó su límite de usos o fue revocado.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/panel#sec-acopio')->with('exito', 'Registro móvil completado. Ya puedes realizar acopios desde tu celular.');
    }
}
