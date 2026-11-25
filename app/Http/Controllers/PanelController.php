<?php

namespace App\Http\Controllers;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\Tina;
use App\Models\User;
use App\Services\ExcelExportService;
use App\Services\LacteoService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Controlador principal de operaciones de planta: acopio, calidad, tinas, finanzas y exportación.
 */
class PanelController extends Controller
{
    public function __construct(
        protected LacteoService $service,
        protected ExcelExportService $excel,
    ) {}

    private function resolverPlantaActiva(Request $request): Planta
    {
        $usuario = $request->user();

        if (! $usuario || ! $usuario->activo) {
            abort(403, 'Cuenta inactiva o no autenticada.');
        }

        if ($usuario->tienePermiso('admin.plantas')) {
            $sessionId = $request->session()->get('planta_activa_id');
            if ($sessionId) {
                $planta = Planta::find($sessionId);
                if ($planta) {
                    return $planta;
                }
            }

            if ($usuario->planta_id) {
                $planta = Planta::find($usuario->planta_id);
                if ($planta) {
                    return $planta;
                }
            }

            $primera = Planta::orderBy('id')->first();
            if ($primera) {
                return $primera;
            }

            abort(403, 'No existe ninguna planta registrada.');
        }

        if (! $usuario->planta_id) {
            abort(403, 'No tienes una planta asignada.');
        }

        $planta = Planta::find($usuario->planta_id);
        if (! $planta) {
            abort(403, 'La planta asignada no existe.');
        }

        return $planta;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function periodoActual(): array
    {
        $hoy = Carbon::today();

        if ($hoy->day <= 15) {
            return [
                $hoy->copy()->startOfMonth()->toDateString(),
                $hoy->copy()->startOfMonth()->addDays(14)->toDateString(),
            ];
        }

        return [
            $hoy->copy()->startOfMonth()->addDays(15)->toDateString(),
            $hoy->copy()->endOfMonth()->toDateString(),
        ];
    }

    private function normalizarCodigoInput(Request $request): void
    {
        if ($request->has('codigo')) {
            $request->merge([
                'codigo' => strtoupper(trim((string) $request->input('codigo'))),
            ]);
        }
    }

    public function index(Request $request): View|RedirectResponse
    {
        $usuario = $request->user();

        if (! $usuario || ! $usuario->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
            ]);
        }

        $plantaActiva = $this->resolverPlantaActiva($request);
        $plantaId = $plantaActiva->id;
        $hoy = Carbon::today()->toDateString();
        $precioLitro = (float) $plantaActiva->precio_litro;
        [$desde, $hasta] = $this->periodoActual();

        $rangoInput = (string) $request->query('rango', '7d');
        $rango = in_array($rangoInput, ['hoy', '7d', 'quincena', 'mes'], true) ? $rangoInput : '7d';

        $seccionesPermitidas = $usuario->seccionesPermitidas();
        $seccionInicial = $usuario->primeraSeccionPermitida() ?? 'sin_permisos';

        $puedeVerAcopio = $usuario->puedeVerSeccion('acopio');
        $puedeVerCalidad = $usuario->puedeVerSeccion('calidad');
        $puedeVerTinas = $usuario->puedeVerSeccion('tinas');
        $puedeVerFinanzas = $usuario->puedeVerSeccion('finanzas');
        $puedeVerStats = $usuario->puedeVerSeccion('estadisticas');
        $puedeVerDashboard = $usuario->puedeVerSeccion('dashboard');
        $puedeVerRed = $usuario->tienePermiso('dashboard.ver_red');
        $puedeAdminPlantas = $usuario->tienePermiso('admin.plantas');
        $puedeAdminUsuarios = $usuario->tienePermiso('admin.usuarios');

        $plantas = $puedeAdminPlantas
            ? Planta::withCount(['tinas', 'users', 'proveedores'])->orderBy('codigo')->get()
            : collect([$plantaActiva]);

        $usuarios = collect();
        if ($puedeAdminUsuarios) {
            $usuarios = User::with(['planta:id,codigo,nombre', 'creador:id,name,usuario'])
                ->when(! $usuario->esAdmin(), function ($q) use ($plantaId): void {
                    $q->where('planta_id', $plantaId)->where('cargo', '!=', 'administracion');
                })
                ->orderBy('name')
                ->get();
        }

        $proveedores = ($puedeVerAcopio || $puedeVerFinanzas)
            ? Proveedor::dePlanta($plantaId)
                ->activos()
                ->select('id', 'planta_id', 'codigo', 'nombre', 'sector')
                ->ordenadosPorNombre()
                ->get()
            : collect();

        $acopiosHoy = ($puedeVerAcopio || $puedeVerCalidad || $puedeVerTinas || $puedeVerDashboard)
            ? Acopio::with('proveedor:id,codigo,nombre,sector')
                ->dePlanta($plantaId)
                ->delDia($hoy)
                ->latest('id')
                ->get()
            : collect();

        $litrosHoy = (float) $acopiosHoy->sum('litros');
        $totalPendientesCalidad = $acopiosHoy->where('calidad', 'pendiente')->count();

        $tinasPanel = collect();
        $loteActivo = null;
        $lotesActivos = collect();
        $termica = null;
        $receta = null;

        if ($puedeVerTinas || $puedeVerDashboard) {
            $tinas = Tina::dePlanta($plantaId)
                ->with('loteActivo')
                ->orderBy('codigo')
                ->get();

            $tinasPanel = $tinas->map(function (Tina $tina): object {
                $lote = $tina->loteActivo;
                $tTermica = $lote ? $this->service->evaluarTermica($lote->temperatura_actual) : null;
                $tReceta = $lote ? $this->service->dosificarReceta($lote->litros) : null;

                return (object) [
                    'tina' => $tina,
                    'lote' => $lote,
                    'termica' => $tTermica,
                    'receta' => $tReceta,
                ];
            });

            $lotesActivos = $tinasPanel->pluck('lote')->filter()->values();
            $loteActivo = $lotesActivos->first()
                ?? Lote::dePlanta($plantaId)->enProceso()->latest('id')->first();

            if ($loteActivo) {
                $termica = $this->service->evaluarTermica($loteActivo->temperatura_actual);
                $receta = $this->service->dosificarReceta($loteActivo->litros);
            }
        }

        $liquidaciones = $puedeVerFinanzas
            ? $this->service->liquidarQuincena($desde, $hasta, $precioLitro, $plantaId)
            : collect();

        $movimientos = $puedeVerFinanzas
            ? Liquidacion::with('proveedor:id,nombre')
                ->dePlanta($plantaId)
                ->latest('id')
                ->limit(25)
                ->get()
            : collect();

        $estadisticas = ($puedeVerStats || $puedeVerRed)
            ? $this->service->obtenerEstadisticas($plantaId, $desde, $hasta, $puedeVerRed)
            : null;

        $dashboardData = $puedeVerDashboard
            ? $this->service->obtenerDatosDashboard(
                $plantaId,
                $rango,
                $puedeVerRed,
                $rango === 'quincena' ? $estadisticas : null
            )
            : null;

        $catalogoPermisos = User::PERMISOS_CATALOGO;
        $plantillasCargo = User::PLANTILLAS_CARGO;
        $permisosAsignables = $usuario->permisosAsignables();

        return view('panel', compact(
            'usuario',
            'plantaActiva',
            'plantas',
            'usuarios',
            'proveedores',
            'acopiosHoy',
            'litrosHoy',
            'precioLitro',
            'totalPendientesCalidad',
            'loteActivo',
            'lotesActivos',
            'tinasPanel',
            'termica',
            'receta',
            'liquidaciones',
            'movimientos',
            'estadisticas',
            'dashboardData',
            'rango',
            'seccionesPermitidas',
            'seccionInicial',
            'catalogoPermisos',
            'plantillasCargo',
            'permisosAsignables',
            'desde',
            'hasta'
        ));
    }

    public function cambiarPlanta(Request $request): RedirectResponse
    {
        if (! $request->user()->tienePermiso('admin.plantas')) {
            abort(403);
        }

        $datos = $request->validate([
            'planta_id' => ['required', 'exists:plantas,id'],
        ]);

        $request->session()->put('planta_activa_id', (int) $datos['planta_id']);

        return back()->with('exito', 'Planta activa cambiada correctamente.');
    }

    public function guardarAcopio(Request $request): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);

        $datos = $request->validate([
            'proveedor_id' => ['required', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'litros' => ['required', 'numeric', 'min:0.5', 'max:5000'],
            'metodo' => ['nullable', 'in:voz,manual'],
            'frase_dictada' => ['nullable', 'string', 'max:255'],
        ]);

        Acopio::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $datos['proveedor_id'],
            'registrado_por' => $request->user()->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => Carbon::now()->toTimeString(),
            'litros' => $datos['litros'],
            'precio_litro' => $planta->precio_litro,
            'metodo' => $datos['metodo'] ?? 'manual',
            'frase_dictada' => $datos['frase_dictada'] ?? null,
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
        ]);

        return back()->with('exito', 'Acopio registrado con éxito.');
    }

    public function actualizarAcopio(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $acopio = Acopio::dePlanta($planta->id)->findOrFail($id);

        $datos = $request->validate([
            'proveedor_id' => ['required', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'litros' => ['required', 'numeric', 'min:0.5', 'max:5000'],
            'calidad' => ['required', 'in:pendiente,buena,acida'],
            'metodo' => ['required', 'in:voz,manual'],
            'hora' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ]);

        $destino = $datos['calidad'] === 'acida'
            ? 'quesillo_4_dias'
            : ($datos['calidad'] === 'buena' ? 'queso_yogur' : 'pendiente');

        $acopio->update([
            'proveedor_id' => $datos['proveedor_id'],
            'litros' => $datos['litros'],
            'calidad' => $datos['calidad'],
            'destino' => $destino,
            'metodo' => $datos['metodo'],
            'hora' => ! empty($datos['hora'])
                ? (strlen($datos['hora']) === 5 ? $datos['hora'].':00' : $datos['hora'])
                : $acopio->hora,
        ]);

        return back()->with('exito', 'Registro de acopio actualizado correctamente.');
    }

    public function eliminarAcopio(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $acopio = Acopio::dePlanta($planta->id)->findOrFail($id);
        $acopio->delete();

        return back()->with('exito', 'Registro de acopio eliminado.');
    }

    public function evaluarCalidad(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $acopio = Acopio::dePlanta($planta->id)->findOrFail($id);

        $datos = $request->validate([
            'calidad' => ['required', 'in:buena,acida'],
        ]);

        if ($datos['calidad'] === 'acida') {
            $acopio->update([
                'calidad' => 'acida',
                'destino' => 'quesillo_4_dias',
            ]);
            $mensaje = 'Leche ácida clasificada y derivada a quesillos frescos de 4 días.';
        } else {
            $acopio->update([
                'calidad' => 'buena',
                'destino' => 'queso_yogur',
            ]);
            $mensaje = 'Leche aprobada para pasteurización en tina principal.';
        }

        return back()->with('exito', $mensaje);
    }

    public function crearTina(Request $request): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $this->normalizarCodigoInput($request);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:30', Rule::unique('tinas', 'codigo')->where('planta_id', $planta->id)],
            'nombre' => ['required', 'string', 'max:100'],
            'capacidad_litros' => ['required', 'numeric', 'min:20', 'max:10000'],
            'tipo' => ['required', 'in:principal_queso,quesillo,yogur,multiproposito'],
        ]);

        Tina::create([
            'planta_id' => $planta->id,
            'codigo' => $datos['codigo'],
            'nombre' => $datos['nombre'],
            'capacidad_litros' => $datos['capacidad_litros'],
            'tipo' => $datos['tipo'],
            'activa' => true,
        ]);

        return back()->with('exito', 'Nueva tina agregada a la planta.');
    }

    public function editarTina(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $tina = Tina::dePlanta($planta->id)->findOrFail($id);
        $this->normalizarCodigoInput($request);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:30', Rule::unique('tinas', 'codigo')->where('planta_id', $planta->id)->ignore($tina->id)],
            'nombre' => ['required', 'string', 'max:100'],
            'capacidad_litros' => ['required', 'numeric', 'min:20', 'max:10000'],
            'tipo' => ['required', 'in:principal_queso,quesillo,yogur,multiproposito'],
            'activa' => ['nullable', 'boolean'],
        ]);

        $tina->update([
            'codigo' => $datos['codigo'],
            'nombre' => $datos['nombre'],
            'capacidad_litros' => $datos['capacidad_litros'],
            'tipo' => $datos['tipo'],
            'activa' => $request->has('activa') ? $request->boolean('activa') : $tina->activa,
        ]);

        return back()->with('exito', 'Tina actualizada correctamente.');
    }

    public function iniciarLoteEnTina(Request $request, int $tinaId): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $tina = Tina::dePlanta($planta->id)->findOrFail($tinaId);

        if (! $tina->activa) {
            return back()->withErrors(['tina' => 'No se puede iniciar un lote en una tina inactiva.']);
        }

        $datos = $request->validate([
            'litros' => ['required', 'numeric', 'min:5', 'max:'.$tina->capacidad_litros],
            'temperatura_actual' => ['nullable', 'numeric', 'min:4', 'max:95'],
        ]);

        if ($tina->loteActivo()->exists()) {
            return back()->withErrors(['tina' => 'Esta tina ya tiene un lote en proceso. Finalízalo antes de iniciar otro.']);
        }

        $temp = (float) ($datos['temperatura_actual'] ?? 72.0);
        $evaluacion = $this->service->evaluarTermica($temp);
        $receta = $this->service->dosificarReceta((float) $datos['litros']);
        $codigoLote = sprintf(
            'LOTE-P%d-T%d-%s-%s',
            $planta->id,
            $tina->id,
            Carbon::now()->format('YmdHis'),
            Str::upper(Str::random(3))
        );

        Lote::create([
            'planta_id' => $planta->id,
            'tina_id' => $tina->id,
            'codigo' => substr($codigoLote, 0, 40),
            'fecha' => Carbon::today()->toDateString(),
            'litros' => $datos['litros'],
            'temperatura_actual' => $temp,
            'cuajo_ml' => $receta['cuajo_ml'],
            'sal_kg' => $receta['sal_kg'],
            'cultivo_g' => $receta['cultivo_g'],
            'moldes_producidos' => 0,
            'estado' => $evaluacion['estado'] === 'muy_fria' ? 'enfriando' : $evaluacion['estado'],
        ]);

        return back()->with('exito', 'Lote iniciado en '.$tina->nombre.' con dosificación calculada.');
    }

    public function actualizarTina(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $lote = Lote::with('tina')->dePlanta($planta->id)->findOrFail($id);

        if ($lote->estado === 'terminado') {
            return back()->withErrors(['tina' => 'Este lote ya se encuentra finalizado y no puede modificarse.']);
        }

        $maxLitros = $lote->tina ? (float) $lote->tina->capacidad_litros : 10000.0;

        $datos = $request->validate([
            'litros' => ['nullable', 'numeric', 'min:0.1', 'max:'.$maxLitros],
            'temperatura' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'moldes' => ['nullable', 'integer', 'min:1'],
        ]);

        $cambios = [];

        if (isset($datos['litros'])) {
            $litros = (float) $datos['litros'];
            $receta = $this->service->dosificarReceta($litros);
            $cambios['litros'] = $litros;
            $cambios['cuajo_ml'] = $receta['cuajo_ml'];
            $cambios['sal_kg'] = $receta['sal_kg'];
            $cambios['cultivo_g'] = $receta['cultivo_g'];
        }

        if (isset($datos['temperatura'])) {
            $nuevaTemp = (float) $datos['temperatura'];
            $evaluacion = $this->service->evaluarTermica($nuevaTemp);
            $cambios['temperatura_actual'] = $nuevaTemp;
            $cambios['estado'] = $evaluacion['estado'] === 'muy_fria' ? 'enfriando' : $evaluacion['estado'];
        }

        if (isset($datos['moldes'])) {
            $cambios['moldes_producidos'] = (int) $datos['moldes'];
            $cambios['estado'] = 'terminado';
            $lote->update($cambios);

            return back()->with('exito', 'Lote finalizado y moldes registrados en la tina.');
        }

        if ($cambios !== []) {
            $lote->update($cambios);
        }

        return back()->with('exito', 'Tina actualizada correctamente.');
    }

    public function registrarMovimiento(Request $request): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);

        $datos = $request->validate([
            'tipo' => ['required', 'in:adelanto_efectivo,adelanto_queso,ayuda_del_dia,gasto_operativo,venta_producto'],
            'proveedor_id' => ['nullable', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'concepto' => ['required', 'string', 'max:160'],
            'cantidad_unidades' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'monto' => ['required', 'numeric', 'min:0.5'],
        ]);

        Liquidacion::create([
            'planta_id' => $planta->id,
            'proveedor_id' => ! empty($datos['proveedor_id']) ? $datos['proveedor_id'] : null,
            'fecha' => Carbon::today()->toDateString(),
            'tipo' => $datos['tipo'],
            'concepto' => $datos['concepto'],
            'litros' => 0,
            'cantidad_unidades' => $datos['cantidad_unidades'] ?? 0,
            'monto' => $datos['monto'],
            'comision' => 0.00,
            'descontado' => ! in_array($datos['tipo'], ['adelanto_efectivo', 'adelanto_queso'], true),
        ]);

        return back()->with('exito', 'Movimiento registrado correctamente.');
    }

    public function actualizarMovimiento(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $mov = Liquidacion::dePlanta($planta->id)->findOrFail($id);

        $datos = $request->validate([
            'tipo' => ['required', 'in:adelanto_efectivo,adelanto_queso,ayuda_del_dia,gasto_operativo,venta_producto'],
            'proveedor_id' => ['nullable', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'concepto' => ['required', 'string', 'max:160'],
            'cantidad_unidades' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'monto' => ['required', 'numeric', 'min:0.5'],
        ]);

        $mov->update([
            'tipo' => $datos['tipo'],
            'proveedor_id' => ! empty($datos['proveedor_id']) ? $datos['proveedor_id'] : null,
            'concepto' => $datos['concepto'],
            'cantidad_unidades' => $datos['cantidad_unidades'] ?? 0,
            'monto' => $datos['monto'],
            'descontado' => ! in_array($datos['tipo'], ['adelanto_efectivo', 'adelanto_queso'], true),
        ]);

        return back()->with('exito', 'Movimiento actualizado correctamente.');
    }

    public function eliminarMovimiento(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $mov = Liquidacion::dePlanta($planta->id)->findOrFail($id);
        $mov->delete();

        return back()->with('exito', 'Movimiento eliminado.');
    }

    public function guardarProveedor(Request $request): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $this->normalizarCodigoInput($request);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20', 'unique:proveedores,codigo'],
            'nombre' => ['required', 'string', 'max:120'],
            'sector' => ['nullable', 'string', 'max:120'],
        ]);

        Proveedor::create([
            'planta_id' => $planta->id,
            'codigo' => $datos['codigo'],
            'nombre' => $datos['nombre'],
            'sector' => $datos['sector'] ?? $planta->ubicacion,
            'activo' => true,
        ]);

        return back()->with('exito', 'Ganadero registrado en la planta.');
    }

    public function actualizarProveedor(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $prov = Proveedor::dePlanta($planta->id)->findOrFail($id);
        $this->normalizarCodigoInput($request);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('proveedores', 'codigo')->ignore($prov->id)],
            'nombre' => ['required', 'string', 'max:120'],
            'sector' => ['nullable', 'string', 'max:120'],
        ]);

        $prov->update([
            'codigo' => $datos['codigo'],
            'nombre' => $datos['nombre'],
            'sector' => array_key_exists('sector', $datos) ? $datos['sector'] : $prov->sector,
        ]);

        return back()->with('exito', 'Datos del ganadero actualizados.');
    }

    public function exportarExcel(Request $request): Response
    {
        $planta = $this->resolverPlantaActiva($request);
        [$desde, $hasta] = $this->periodoActual();
        $puedeVerRed = $request->user()->tienePermiso('dashboard.ver_red');

        $liquidaciones = $this->service->liquidarQuincena($desde, $hasta, (float) $planta->precio_litro, $planta->id);
        $acopios = Acopio::with('proveedor:id,codigo,nombre,sector')
            ->dePlanta($planta->id)
            ->delPeriodo($desde, $hasta)
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();
        $movimientos = Liquidacion::with('proveedor:id,nombre')
            ->dePlanta($planta->id)
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();
        $estadisticas = $this->service->obtenerEstadisticas($planta->id, $desde, $hasta, $puedeVerRed);

        $xlsx = $this->excel->generarBalanceXlsx(
            $planta,
            $desde,
            $hasta,
            $liquidaciones,
            $acopios,
            $movimientos,
            $estadisticas['ranking_plantas']
        );

        $slug = Str::slug($planta->codigo ?: 'lat');
        $filename = "balance_lat_{$slug}_".Carbon::now()->format('Ymd').'.xlsx';

        return response($xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
