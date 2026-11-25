<?php

namespace App\Http\Controllers;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\QrAcceso;
use App\Models\ReporteExcel;
use App\Models\Tina;
use App\Models\User;
use App\Services\ExcelExportService;
use App\Services\LacteoService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Controlador principal de operaciones de planta: acopio, calidad, tinas, finanzas, Excel y offline.
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
            abort(403, __('lat.account_inactive_or_unauthenticated'));
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

            abort(403, __('lat.plant_none_registered'));
        }

        if (! $usuario->planta_id) {
            abort(403, __('lat.plant_not_assigned'));
        }

        $planta = Planta::find($usuario->planta_id);
        if (! $planta) {
            abort(403, __('lat.plant_assigned_missing'));
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
            $localeActual = $request->session()->get('locale', $request->cookie('lat_locale', config('app.locale', 'es')));
            if (! is_string($localeActual) || ! in_array($localeActual, ['es', 'qu', 'en'], true)) {
                $localeActual = 'es';
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->put('locale', $localeActual);

            return redirect()->route('login')->withErrors([
                'login' => __('lat.account_deactivated'),
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
        $puedeAdminQr = $usuario->tieneAlgunPermiso(['admin.qr', 'admin.usuarios']);

        $plantasPapelera = collect();
        if ($puedeAdminPlantas) {
            $plantas = Planta::withCount(['tinas', 'users', 'proveedores'])->orderBy('codigo')->get();
            if ($usuario->esAdmin()) {
                $limitePurga = Carbon::now()->subDays(Planta::DIAS_RETENCION_PAPELERA);
                $papeleraRaw = Planta::onlyTrashed()
                    ->with('eliminador:id,name')
                    ->withCount(['tinas', 'users', 'proveedores', 'acopios'])
                    ->latest('deleted_at')
                    ->get();

                $plantasPapelera = $papeleraRaw->filter(function (Planta $p) use ($limitePurga): bool {
                    if ($p->deleted_at !== null && $p->deleted_at->lessThanOrEqualTo($limitePurga)) {
                        $p->forceDelete();

                        return false;
                    }

                    return true;
                })->values();
            }
        } else {
            $plantas = collect([$plantaActiva]);
        }

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

        $filtrosAcopio = [
            'fecha_desde' => (string) $request->query('acopio_fecha_desde', ''),
            'fecha_hasta' => (string) $request->query('acopio_fecha_hasta', ''),
            'proveedor_id' => $request->query('acopio_proveedor_id', ''),
            'litros_min' => $request->query('acopio_litros_min', ''),
            'litros_max' => $request->query('acopio_litros_max', ''),
        ];

        $hayFiltrosAcopio = collect($filtrosAcopio)->contains(fn ($v): bool => $v !== null && $v !== '');

        $acopiosHoy = collect();
        if ($puedeVerAcopio || $puedeVerCalidad || $puedeVerTinas || $puedeVerDashboard) {
            $acopiosQuery = Acopio::with('proveedor:id,codigo,nombre,sector')
                ->dePlanta($plantaId);

            if ($hayFiltrosAcopio) {
                $fDesde = preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtrosAcopio['fecha_desde']) ? $filtrosAcopio['fecha_desde'] : null;
                $fHasta = preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtrosAcopio['fecha_hasta']) ? $filtrosAcopio['fecha_hasta'] : null;

                if ($fDesde !== null && $fHasta !== null) {
                    $this->service->aplicarRangoFecha($acopiosQuery, min($fDesde, $fHasta), max($fDesde, $fHasta));
                } elseif ($fDesde !== null) {
                    $acopiosQuery->where('fecha', '>=', $fDesde);
                } elseif ($fHasta !== null) {
                    $this->service->aplicarHastaFecha($acopiosQuery, $fHasta);
                }

                if (is_numeric($filtrosAcopio['proveedor_id']) && (int) $filtrosAcopio['proveedor_id'] > 0) {
                    $acopiosQuery->where('proveedor_id', (int) $filtrosAcopio['proveedor_id']);
                }

                if (is_numeric($filtrosAcopio['litros_min'])) {
                    $acopiosQuery->where('litros', '>=', (float) $filtrosAcopio['litros_min']);
                }

                if (is_numeric($filtrosAcopio['litros_max'])) {
                    $acopiosQuery->where('litros', '<=', (float) $filtrosAcopio['litros_max']);
                }

                $acopiosHoy = $acopiosQuery->orderByDesc('fecha')->orderByDesc('id')->limit(200)->get();
            } else {
                $acopiosHoy = $acopiosQuery->delDia($hoy)->latest('id')->get();
            }
        }

        $litrosHoy = (float) $acopiosHoy->sum('litros');
        $totalPendientesCalidad = $acopiosHoy->where('calidad', 'pendiente')->count();

        $tinasPanel = collect();
        $tinas = null;
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

        $reportesExcel = collect();
        $contadoresPlanta = collect();
        if ($puedeVerFinanzas) {
            $reportesExcel = ReporteExcel::with(['remitente:id,name,cargo', 'destinatario:id,name,cargo', 'proveedor:id,nombre'])
                ->dePlanta($plantaId)
                ->when(! $usuario->esAdmin(), function ($q) use ($usuario): void {
                    $q->where(function ($sub) use ($usuario): void {
                        $sub->where('remitente_id', $usuario->id)
                            ->orWhere('destinatario_id', $usuario->id);
                    });
                })
                ->latest('id')
                ->limit(30)
                ->get();

            $contadoresBase = ($puedeAdminUsuarios && $usuario->esAdmin())
                ? $usuarios
                : User::activos()
                    ->where(function ($q) use ($plantaId): void {
                        $q->where('planta_id', $plantaId)
                            ->orWhere('cargo', 'administracion');
                    })
                    ->orderBy('name')
                    ->get(['id', 'planta_id', 'name', 'usuario', 'cargo', 'permisos', 'activo']);

            $contadoresPlanta = $contadoresBase
                ->filter(fn (User $u): bool => $u->activo
                    && ((int) $u->planta_id === $plantaId || $u->cargo === 'administracion')
                    && ($u->tienePermiso('finanzas.exportar') || $u->cargo === 'contador_externo'))
                ->values();
        }

        $qrAccesos = $puedeAdminQr
            ? QrAcceso::with('creador:id,name')
                ->dePlanta($plantaId)
                ->latest('id')
                ->limit(15)
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
                $rango === 'quincena' ? $estadisticas : null,
                $tinas
            )
            : null;

        $catalogoPermisos = User::PERMISOS_CATALOGO;
        $plantillasCargo = User::PLANTILLAS_CARGO;
        $permisosAsignables = $usuario->permisosAsignables();

        return view('panel', compact(
            'usuario',
            'plantaActiva',
            'plantas',
            'plantasPapelera',
            'usuarios',
            'proveedores',
            'acopiosHoy',
            'filtrosAcopio',
            'hayFiltrosAcopio',
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
            'reportesExcel',
            'contadoresPlanta',
            'qrAccesos',
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
            abort(403, __('lat.forbidden'));
        }

        $datos = $request->validate([
            'planta_id' => ['required', Rule::exists('plantas', 'id')->whereNull('deleted_at')],
        ]);

        $request->session()->put('planta_activa_id', (int) $datos['planta_id']);

        return back()->with('exito', __('lat.plant_switched'));
    }

    public function guardarAcopio(Request $request): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);

        $datos = $request->validate([
            'proveedor_id' => ['required', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'litros' => ['required', 'numeric', 'min:1', 'max:5000'],
            'fecha' => ['nullable', 'date', 'before_or_equal:today'],
            'metodo' => ['nullable', 'in:voz,manual'],
            'frase_dictada' => ['nullable', 'string', 'max:255'],
            'uuid_offline' => ['nullable', 'uuid'],
        ]);

        $uuid = $datos['uuid_offline'] ?? (string) Str::uuid();
        if (! empty($datos['uuid_offline']) && Acopio::where('uuid_offline', $uuid)->exists()) {
            return back()->with('exito', __('lat.intake_created'));
        }

        Acopio::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $datos['proveedor_id'],
            'registrado_por' => $request->user()->id,
            'uuid_offline' => $uuid,
            'fecha' => $datos['fecha'] ?? Carbon::today()->toDateString(),
            'hora' => Carbon::now()->toTimeString(),
            'litros' => $datos['litros'],
            'precio_litro' => $planta->precio_litro,
            'metodo' => $datos['metodo'] ?? 'manual',
            'frase_dictada' => $datos['frase_dictada'] ?? null,
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
        ]);

        return back()->with('exito', __('lat.intake_created'));
    }

    /**
     * Sincroniza lotes de acopios capturados sin conexión garantizando idempotencia por uuid_offline.
     */
    public function sincronizarAcopiosOffline(Request $request): JsonResponse
    {
        $planta = $this->resolverPlantaActiva($request);

        $datos = $request->validate([
            'acopios' => ['required', 'array', 'min:1', 'max:50'],
            'acopios.*.uuid_offline' => ['required', 'uuid'],
            'acopios.*.proveedor_id' => ['required', 'integer'],
            'acopios.*.litros' => ['required', 'numeric', 'min:1', 'max:5000'],
            'acopios.*.fecha' => ['nullable', 'date', 'before_or_equal:today'],
            'acopios.*.hora' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'acopios.*.metodo' => ['nullable', 'in:voz,manual'],
            'acopios.*.frase_dictada' => ['nullable', 'string', 'max:255'],
        ]);

        $proveedoresValidos = Proveedor::dePlanta($planta->id)->pluck('id')->all();
        $sincronizados = [];
        $duplicados = [];
        $errores = [];

        DB::transaction(function () use ($datos, $planta, $request, $proveedoresValidos, &$sincronizados, &$duplicados, &$errores): void {
            foreach ($datos['acopios'] as $idx => $item) {
                $provId = (int) $item['proveedor_id'];
                if (! in_array($provId, $proveedoresValidos, true)) {
                    $errores[] = ['index' => $idx, 'uuid_offline' => $item['uuid_offline'], 'error' => 'Productor no pertenece a la planta activa.'];

                    continue;
                }

                $existente = Acopio::where('uuid_offline', $item['uuid_offline'])->first();
                if ($existente) {
                    $duplicados[] = $item['uuid_offline'];

                    continue;
                }

                $hora = ! empty($item['hora'])
                    ? (strlen($item['hora']) === 5 ? $item['hora'].':00' : $item['hora'])
                    : Carbon::now()->toTimeString();

                Acopio::create([
                    'planta_id' => $planta->id,
                    'proveedor_id' => $provId,
                    'registrado_por' => $request->user()->id,
                    'uuid_offline' => $item['uuid_offline'],
                    'fecha' => $item['fecha'] ?? Carbon::today()->toDateString(),
                    'hora' => $hora,
                    'litros' => $item['litros'],
                    'precio_litro' => $planta->precio_litro,
                    'metodo' => $item['metodo'] ?? 'manual',
                    'frase_dictada' => $item['frase_dictada'] ?? null,
                    'calidad' => 'buena',
                    'destino' => 'queso_yogur',
                ]);

                $sincronizados[] = $item['uuid_offline'];
            }
        });

        return response()->json([
            'ok' => true,
            'sincronizados' => $sincronizados,
            'duplicados' => $duplicados,
            'errores' => $errores,
        ]);
    }

    public function actualizarAcopio(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $acopio = Acopio::dePlanta($planta->id)->findOrFail($id);

        $datos = $request->validate([
            'proveedor_id' => ['required', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'litros' => ['required', 'numeric', 'min:1', 'max:5000'],
            'calidad' => ['required', 'in:pendiente,buena,acida'],
            'metodo' => ['required', 'in:voz,manual'],
            'fecha' => ['nullable', 'date', 'before_or_equal:today'],
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
            'fecha' => ! empty($datos['fecha']) ? $datos['fecha'] : $acopio->fecha,
            'hora' => ! empty($datos['hora'])
                ? (strlen($datos['hora']) === 5 ? $datos['hora'].':00' : $datos['hora'])
                : $acopio->hora,
        ]);

        return back()->with('exito', __('lat.intake_updated'));
    }

    public function eliminarAcopio(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $acopio = Acopio::dePlanta($planta->id)->findOrFail($id);
        $acopio->delete();

        return back()->with('exito', __('lat.intake_deleted'));
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
            $mensaje = __('lat.quality_classified_acid');
        } else {
            $acopio->update([
                'calidad' => 'buena',
                'destino' => 'queso_yogur',
            ]);
            $mensaje = __('lat.quality_classified_good');
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

        return back()->with('exito', __('lat.vat_created'));
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

        return back()->with('exito', __('lat.vat_updated'));
    }

    public function eliminarTina(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $tina = Tina::dePlanta($planta->id)->findOrFail($id);

        if ($tina->loteActivo()->exists()) {
            return back()->withErrors(['tina' => 'No puedes eliminar una tina que tiene un lote en proceso.']);
        }

        $tina->delete();

        return back()->with('exito', 'Tina eliminada correctamente.');
    }

    public function iniciarLoteEnTina(Request $request, int $tinaId): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $tina = Tina::dePlanta($planta->id)->findOrFail($tinaId);

        if (! $tina->activa) {
            return back()->withErrors(['tina' => __('lat.vat_inactive_error')]);
        }

        $datos = $request->validate([
            'litros' => ['required', 'numeric', 'min:5', 'max:'.$tina->capacidad_litros],
            'temperatura_actual' => ['nullable', 'numeric', 'min:4', 'max:95'],
        ]);

        if ($tina->loteActivo()->exists()) {
            return back()->withErrors(['tina' => __('lat.vat_already_active_error')]);
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

        return back()->with('exito', __('lat.batch_started', ['vat' => $tina->nombre]));
    }

    public function actualizarTina(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $lote = Lote::with('tina')->dePlanta($planta->id)->findOrFail($id);

        if ($lote->estado === 'terminado') {
            return back()->withErrors(['tina' => __('lat.batch_finished_error')]);
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

            return back()->with('exito', __('lat.batch_finished'));
        }

        if ($cambios !== []) {
            $lote->update($cambios);
        }

        return back()->with('exito', __('lat.vat_updated'));
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

        return back()->with('exito', __('lat.movement_created'));
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

        return back()->with('exito', __('lat.movement_updated'));
    }

    public function eliminarMovimiento(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $mov = Liquidacion::dePlanta($planta->id)->findOrFail($id);
        $mov->delete();

        return back()->with('exito', __('lat.movement_deleted'));
    }

    public function guardarProveedor(Request $request): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $this->normalizarCodigoInput($request);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('proveedores', 'codigo')->where('planta_id', $planta->id)],
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

        return back()->with('exito', __('lat.producer_created'));
    }

    public function actualizarProveedor(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $prov = Proveedor::dePlanta($planta->id)->findOrFail($id);
        $this->normalizarCodigoInput($request);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('proveedores', 'codigo')->where('planta_id', $planta->id)->ignore($prov->id)],
            'nombre' => ['required', 'string', 'max:120'],
            'sector' => ['nullable', 'string', 'max:120'],
        ]);

        $prov->update([
            'codigo' => $datos['codigo'],
            'nombre' => $datos['nombre'],
            'sector' => array_key_exists('sector', $datos) ? $datos['sector'] : $prov->sector,
        ]);

        return back()->with('exito', __('lat.producer_updated'));
    }

    public function eliminarProveedor(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $prov = Proveedor::dePlanta($planta->id)->findOrFail($id);

        if ($prov->acopios()->exists() || $prov->liquidaciones()->exists()) {
            $prov->update(['activo' => false]);

            return back()->with('exito', "Productor \"{$prov->nombre}\" desactivado para conservar su historial contable.");
        }

        $prov->delete();

        return back()->with('exito', 'Ganadero eliminado correctamente.');
    }

    public function exportarExcel(Request $request): Response
    {
        $planta = $this->resolverPlantaActiva($request);
        [$desde, $hasta] = $this->periodoActual();
        $puedeVerRed = $request->user()->tienePermiso('dashboard.ver_red');

        $xlsx = $this->generarBinarioExcelPeriodo($planta, $desde, $hasta, $puedeVerRed);
        $slug = Str::slug($planta->codigo ?: 'lat');
        $filename = "balance_lat_{$slug}_".Carbon::now()->format('Ymd').'.xlsx';

        return response($xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Genera y envía un reporte Excel (.xlsx) ordenado a la bandeja interna del contador.
     */
    public function enviarReporteExcel(Request $request): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $remitente = $request->user();
        [$defDesde, $defHasta] = $this->periodoActual();

        $datos = $request->validate([
            'destinatario_id' => ['required', 'integer', 'exists:users,id'],
            'proveedor_id' => ['nullable', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'tipo_reporte' => ['required', 'in:ventas_productor,balance_quincenal,acopios_filtrados'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
            'notas' => ['nullable', 'string', 'max:255'],
        ]);

        $destinatario = User::activos()
            ->where('id', (int) $datos['destinatario_id'])
            ->where(function ($q) use ($planta): void {
                $q->where('planta_id', $planta->id)->orWhere('cargo', 'administracion');
            })
            ->first();

        if (! $destinatario || (! $destinatario->tienePermiso('finanzas.exportar') && $destinatario->cargo !== 'contador_externo')) {
            return back()->withErrors(['destinatario_id' => 'El destinatario seleccionado no es un contador autorizado de esta planta.']);
        }

        $desde = $datos['fecha_desde'] ?? $defDesde;
        $hasta = $datos['fecha_hasta'] ?? $defHasta;
        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $provId = ! empty($datos['proveedor_id']) ? (int) $datos['proveedor_id'] : null;
        $xlsx = $this->generarBinarioExcelPeriodo(
            $planta,
            $desde,
            $hasta,
            $remitente->tienePermiso('dashboard.ver_red'),
            $provId
        );

        $slug = Str::slug($planta->codigo ?: 'lat');
        $nombreArchivo = "reporte_{$datos['tipo_reporte']}_{$slug}_".Carbon::now()->format('Ymd_His').'.xlsx';
        $rutaArchivo = 'reportes_excel/'.Str::uuid().'.xlsx';

        Storage::disk('local')->put($rutaArchivo, $xlsx);

        ReporteExcel::create([
            'planta_id' => $planta->id,
            'remitente_id' => $remitente->id,
            'destinatario_id' => $destinatario->id,
            'proveedor_id' => $provId,
            'tipo_reporte' => $datos['tipo_reporte'],
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'nombre_archivo' => $nombreArchivo,
            'ruta_archivo' => $rutaArchivo,
            'notas' => $datos['notas'] ?? null,
            'estado' => 'enviado',
        ]);

        return back()->with('exito', "Reporte Excel ordenado enviado a {$destinatario->name}.");
    }

    public function descargarReporteExcel(Request $request, int $id): Response
    {
        $planta = $this->resolverPlantaActiva($request);
        $usuario = $request->user();

        $reporte = ReporteExcel::dePlanta($planta->id)->findOrFail($id);
        if (! $usuario->esAdmin() && $reporte->remitente_id !== $usuario->id && $reporte->destinatario_id !== $usuario->id) {
            abort(403, __('lat.forbidden'));
        }

        if (! Storage::disk('local')->exists($reporte->ruta_archivo)) {
            abort(404, 'El archivo Excel solicitado ya no está disponible.');
        }

        if ($reporte->destinatario_id === $usuario->id && $reporte->leido_at === null) {
            $reporte->update(['leido_at' => Carbon::now()]);
        }

        $contenido = Storage::disk('local')->get($reporte->ruta_archivo);

        return response($contenido, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$reporte->nombre_archivo.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function marcarReporteRevisado(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $usuario = $request->user();

        $reporte = ReporteExcel::dePlanta($planta->id)->findOrFail($id);
        if (! $usuario->esAdmin() && $reporte->destinatario_id !== $usuario->id) {
            abort(403, __('lat.forbidden'));
        }

        $reporte->update([
            'estado' => 'revisado',
            'leido_at' => $reporte->leido_at ?? Carbon::now(),
        ]);

        return back()->with('exito', 'Reporte Excel marcado como revisado.');
    }

    private function generarBinarioExcelPeriodo(
        Planta $planta,
        string $desde,
        string $hasta,
        bool $puedeVerRed,
        ?int $proveedorId = null
    ): string {
        $liquidaciones = $this->service->liquidarQuincena($desde, $hasta, (float) $planta->precio_litro, $planta->id);
        if ($proveedorId !== null) {
            $liquidaciones = $liquidaciones->where('proveedor_id', $proveedorId)->values();
        }

        $acopios = Acopio::with('proveedor:id,codigo,nombre,sector')
            ->dePlanta($planta->id)
            ->delPeriodo($desde, $hasta)
            ->when($proveedorId !== null, fn ($q) => $q->where('proveedor_id', $proveedorId))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        $movQuery = Liquidacion::with('proveedor:id,nombre')
            ->dePlanta($planta->id)
            ->when($proveedorId !== null, fn ($q) => $q->where('proveedor_id', $proveedorId));
        $this->service->aplicarRangoFecha($movQuery, $desde, $hasta);

        $movimientos = $movQuery
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        $estadisticas = $this->service->obtenerEstadisticas($planta->id, $desde, $hasta, $puedeVerRed);

        return $this->excel->generarBalanceXlsx(
            $planta,
            $desde,
            $hasta,
            $liquidaciones,
            $acopios,
            $movimientos,
            $estadisticas['ranking_plantas']
        );
    }
}
