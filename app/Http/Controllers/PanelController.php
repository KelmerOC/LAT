<?php

namespace App\Http\Controllers;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\NotificacionPanel;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\QrAcceso;
use App\Models\ReporteExcel;
use App\Models\Tina;
use App\Models\User;
use App\Services\ExcelExportService;
use App\Services\LacteoService;
use App\Services\TinaWorkflowService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
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
        protected TinaWorkflowService $tinaWorkflow,
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

    private function normalizarCodigoTinaInput(Request $request): void
    {
        if (! $request->has('codigo')) {
            return;
        }

        $valor = strtoupper(trim((string) $request->input('codigo')));
        $prefijo = app()->getLocale() === 'en' ? 'VAT-' : 'TINA-';

        if (preg_match('/^(VAT|TINA)[\s-]*/i', $valor, $coincidencia) === 1) {
            $prefijo = strtoupper($coincidencia[1]).'-';
            $sufijo = trim((string) preg_replace('/^(?:VAT|TINA)[\s-]*/i', '', $valor));
        } else {
            $sufijo = $valor;
        }

        $request->merge([
            'codigo' => $sufijo !== '' ? $prefijo.$sufijo : '',
        ]);
    }

    /**
     * Calcula el siguiente sufijo numérico correlativo (con relleno a 2 dígitos) para uno o más prefijos.
     *
     * @param  iterable<int, string|null>  $codigos
     * @param  string|list<string>  $prefijos
     */
    private function calcularSiguienteSufijo(iterable $codigos, string|array $prefijos): string
    {
        $max = 0;
        $alternativas = implode('|', array_map(fn (string $p): string => preg_quote($p, '/'), (array) $prefijos));
        $patron = '/^(?:'.$alternativas.')(\d+)$/i';

        foreach ($codigos as $codigo) {
            if (is_string($codigo) && preg_match($patron, $codigo, $matches) === 1) {
                $numero = (int) $matches[1];
                if ($numero > $max) {
                    $max = $numero;
                }
            }
        }

        return str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Construye el resumen de leche disponible recibida hoy según calidad para el panel de tinas.
     *
     * @param  Collection<int, Acopio>  $acopios
     * @param  Collection<int, Lote>  $lotesActivos
     * @return array{
     *     optima: array{litros: float, entregas: int, productores: list<string>},
     *     acida: array{litros: float, entregas: int, productores: list<string>},
     *     pendiente: array{litros: float, entregas: int},
     *     total_litros: float,
     *     litros_en_tinas_activas: float
     * }
     */
    private function construirResumenLecheTinas(Collection $acopios, Collection $lotesActivos): array
    {
        $disponibles = $acopios->filter(fn (Acopio $acopio): bool => $acopio->lote_id === null);

        $resumenPorCalidad = function (string $calidad) use ($disponibles): array {
            $filtrados = $disponibles->where('calidad', $calidad);

            return [
                'litros' => round((float) $filtrados->sum('litros'), 2),
                'entregas' => $filtrados->count(),
                'productores' => $filtrados->pluck('proveedor.nombre')->filter()->unique()->values()->all(),
            ];
        };

        $pendientes = $disponibles->where('calidad', 'pendiente');

        return [
            'optima' => $resumenPorCalidad('buena'),
            'acida' => $resumenPorCalidad('acida'),
            'pendiente' => [
                'litros' => round((float) $pendientes->sum('litros'), 2),
                'entregas' => $pendientes->count(),
            ],
            'total_litros' => round((float) $disponibles->sum('litros'), 2),
            'litros_en_tinas_activas' => round((float) $lotesActivos->sum('litros'), 2),
        ];
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
            $usuarios = User::with($puedeAdminPlantas ? ['creador:id,name,usuario'] : ['planta:id,codigo,nombre', 'creador:id,name,usuario'])
                ->when(! $usuario->esAdmin(), function ($q) use ($plantaId): void {
                    $q->where('planta_id', $plantaId)->where('cargo', '!=', 'administracion');
                })
                ->orderBy('name')
                ->get();

            if ($puedeAdminPlantas) {
                $plantasPorId = $plantas->keyBy('id');
                $usuarios->each(function (User $u) use ($plantasPorId): void {
                    $u->setRelation('planta', $u->planta_id ? $plantasPorId->get($u->planta_id) : null);
                });
            }
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
        $resumenLecheTinas = $this->construirResumenLecheTinas(collect(), collect());
        $siguienteSufijoTina = '01';
        $siguienteSufijoPlanta = $puedeAdminPlantas
            ? $this->calcularSiguienteSufijo(
                $plantas->pluck('codigo')->merge($plantasPapelera->pluck('codigo')),
                'PLT-'
            )
            : '01';
        $recetasBase = $plantaActiva->obtenerRecetasBase();
        $prefijoTinaIdioma = app()->getLocale() === 'en' ? 'VAT-' : 'TINA-';
        $acopiosDisponiblesTinas = $usuario->tienePermiso('tinas.operar')
            ? Acopio::with('proveedor:id,codigo,nombre')
                ->dePlanta($plantaId)
                ->disponiblesParaTina()
                ->orderByDesc('evaluado_en')
                ->orderByDesc('id')
                ->limit(120)
                ->get()
            : collect();

        if ($puedeVerTinas || $puedeVerDashboard) {
            $tinas = Tina::dePlanta($plantaId)
                ->with('loteActivo.acopios:id,lote_id,litros,calidad')
                ->orderBy('codigo')
                ->get();

            $tinasPanel = $tinas->map(function (Tina $tina) use ($recetasBase): object {
                $lote = $tina->loteActivo;
                if ($lote) {
                    $lote->setRelation('tina', $tina);
                }

                $tTermica = $lote ? $this->service->evaluarTermica($lote->temperatura_actual) : null;
                $tReceta = null;
                if ($lote) {
                    $claveReceta = ($lote->receta_nombre && isset($recetasBase[$lote->receta_nombre]))
                        ? $lote->receta_nombre
                        : (isset($recetasBase[$tina->tipo]) ? $tina->tipo : 'principal_queso');
                    $dosif = $this->tinaWorkflow->dosificarConRecetaBase((float) $lote->litros, $recetasBase[$claveReceta]);
                    $tReceta = [
                        'litros' => $dosif['litros'],
                        'cuajo_ml' => round((float) $lote->cuajo_ml, 2),
                        'sal_kg' => round((float) $lote->sal_kg, 2),
                        'cultivo_g' => round((float) $lote->cultivo_g, 2),
                        'moldes_estimados' => $dosif['moldes_estimados'],
                    ];
                }

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
                $recetaPanel = $tinasPanel->firstWhere('lote.id', $loteActivo->id)?->receta;
                $receta = $recetaPanel ?? $this->service->dosificarReceta($loteActivo->litros);
            }

            $this->tinaWorkflow->verificarCronometrosVencidos($plantaId, $lotesActivos);

            $coleccionHoyTinas = ! $hayFiltrosAcopio
                ? $acopiosHoy
                : Acopio::with('proveedor:id,nombre')
                    ->dePlanta($plantaId)
                    ->delDia($hoy)
                    ->get();

            $resumenLecheTinas = $this->construirResumenLecheTinas($coleccionHoyTinas, $lotesActivos);
            $siguienteSufijoTina = $this->calcularSiguienteSufijo($tinas->pluck('codigo'), ['TINA-', 'VAT-']);
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

        $plantasPrecalculadas = $puedeAdminPlantas ? $plantas : null;

        $estadisticas = ($puedeVerStats || $puedeVerRed)
            ? $this->service->obtenerEstadisticas($plantaId, $desde, $hasta, $puedeVerRed, $plantasPrecalculadas)
            : null;

        $dashboardData = $puedeVerDashboard
            ? $this->service->obtenerDatosDashboard(
                $plantaId,
                $rango,
                $puedeVerRed,
                $rango === 'quincena' ? $estadisticas : null,
                $tinas,
                $plantasPrecalculadas
            )
            : null;

        $catalogoPermisos = User::PERMISOS_CATALOGO;
        $plantillasCargo = User::PLANTILLAS_CARGO;
        $permisosAsignables = $usuario->permisosAsignables();
        $notificacionesPanel = $this->tinaWorkflow->obtenerNotificacionesParaUsuario($plantaId, $usuario->id);

        return view('panel', compact(
            'usuario',
            'plantaActiva',
            'plantas',
            'plantasPapelera',
            'usuarios',
            'proveedores',
            'acopiosHoy',
            'acopiosDisponiblesTinas',
            'filtrosAcopio',
            'hayFiltrosAcopio',
            'litrosHoy',
            'precioLitro',
            'totalPendientesCalidad',
            'loteActivo',
            'lotesActivos',
            'tinasPanel',
            'resumenLecheTinas',
            'recetasBase',
            'notificacionesPanel',
            'prefijoTinaIdioma',
            'siguienteSufijoTina',
            'siguienteSufijoPlanta',
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

        $acopio = Acopio::create([
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
            'calidad' => 'pendiente',
            'destino' => 'pendiente',
        ]);

        if ($acopio->metodo === 'voz') {
            $acopio->loadMissing('proveedor:id,nombre');
            $proveedorNombre = $acopio->proveedor?->nombre ?? __('lat.producer_default');

            return back()
                ->with('exito', __('lat.intake_voice_auto_created', [
                    'producer' => $proveedorNombre,
                    'liters' => number_format((float) $acopio->litros, 1),
                ]))
                ->with('acopio_voz_reciente', [
                    'id' => $acopio->id,
                    'proveedor_id' => $acopio->proveedor_id,
                    'proveedor_nombre' => $proveedorNombre,
                    'litros' => (float) $acopio->litros,
                    'calidad' => $acopio->calidad,
                    'metodo' => $acopio->metodo,
                    'fecha' => $acopio->fecha?->format('Y-m-d') ?? Carbon::today()->toDateString(),
                    'hora' => substr((string) $acopio->hora, 0, 5),
                    'frase_dictada' => (string) ($acopio->frase_dictada ?? ''),
                ]);
        }

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
                    'calidad' => 'pendiente',
                    'destino' => 'pendiente',
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
            'frase_dictada' => ['nullable', 'string', 'max:255'],
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
            'evaluado_en' => $datos['calidad'] === 'pendiente' ? null : ($acopio->evaluado_en ?? Carbon::now()),
            'metodo' => $datos['metodo'],
            'frase_dictada' => $request->has('frase_dictada')
                ? (trim((string) ($datos['frase_dictada'] ?? '')) !== '' ? trim((string) ($datos['frase_dictada'] ?? '')) : null)
                : $acopio->frase_dictada,
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

        if ($acopio->lote_id !== null) {
            return back()->withErrors(['calidad' => __('lat.quality_locked_in_batch')]);
        }

        $datos = $request->validate([
            'calidad' => ['required', 'in:buena,acida,descartada'],
        ]);

        $acopio->loadMissing('proveedor:id,nombre');
        $provNombre = $acopio->proveedor?->nombre ?? __('lat.producer_default');
        $litrosFmt = number_format((float) $acopio->litros, 1);
        $usuarioId = $request->user()->id;

        if ($datos['calidad'] === 'descartada') {
            $litros = (float) $acopio->litros;
            $acopio->delete();

            $this->tinaWorkflow->notificar(
                $planta->id,
                'calidad_descartada',
                'tinas',
                __('lat.notif_quality_discarded_title'),
                __('lat.notif_quality_discarded_msg', ['producer' => $provNombre, 'liters' => $litrosFmt]),
                ['litros' => $litros],
                $usuarioId
            );

            return back()->with('exito', __('lat.quality_classified_discarded'));
        }

        if ($datos['calidad'] === 'acida') {
            $acopio->update([
                'calidad' => 'acida',
                'destino' => 'quesillo_4_dias',
                'evaluado_en' => Carbon::now(),
            ]);

            $this->tinaWorkflow->notificar(
                $planta->id,
                'calidad_acida',
                'tinas',
                __('lat.notif_quality_acid_title'),
                __('lat.notif_quality_acid_msg', ['producer' => $provNombre, 'liters' => $litrosFmt]),
                ['acopio_id' => $acopio->id, 'litros' => (float) $acopio->litros],
                $usuarioId
            );

            return back()->with('exito', __('lat.quality_classified_acid'));
        }

        $acopio->update([
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => Carbon::now(),
        ]);

        $this->tinaWorkflow->notificar(
            $planta->id,
            'calidad_aprobada',
            'tinas',
            __('lat.notif_quality_good_title'),
            __('lat.notif_quality_good_msg', ['producer' => $provNombre, 'liters' => $litrosFmt]),
            ['acopio_id' => $acopio->id, 'litros' => (float) $acopio->litros],
            $usuarioId
        );

        return back()->with('exito', __('lat.quality_classified_good'));
    }

    public function crearTina(Request $request): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $this->normalizarCodigoTinaInput($request);
        if ($request->has('tipo')) {
            $request->merge(['tipo' => trim((string) $request->input('tipo'))]);
        }

        $datos = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:30',
                'regex:/^(TINA|VAT)-[A-Z0-9][A-Z0-9-]*$/',
                Rule::unique('tinas', 'codigo')->where('planta_id', $planta->id),
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'capacidad_litros' => ['required', 'numeric', 'min:20', 'max:10000'],
            'tipo' => ['required', 'string', 'min:2', 'max:80'],
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
        $this->normalizarCodigoTinaInput($request);
        if ($request->has('tipo')) {
            $request->merge(['tipo' => trim((string) $request->input('tipo'))]);
        }

        $datos = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:30',
                'regex:/^(TINA|VAT)-[A-Z0-9][A-Z0-9-]*$/',
                Rule::unique('tinas', 'codigo')->where('planta_id', $planta->id)->ignore($tina->id),
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'capacidad_litros' => ['required', 'numeric', 'min:20', 'max:10000'],
            'tipo' => ['required', 'string', 'min:2', 'max:80'],
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
            return back()->withErrors(['tina' => __('lat.vat_delete_active_error')]);
        }

        $tina->delete();

        return back()->with('exito', __('lat.vat_deleted'));
    }

    public function iniciarLoteEnTina(Request $request, int $tinaId): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $tina = Tina::dePlanta($planta->id)->findOrFail($tinaId);

        if (! $tina->activa) {
            return back()->withErrors(['tina' => __('lat.vat_inactive_error')]);
        }

        if ($tina->loteActivo()->exists()) {
            return back()->withErrors(['tina' => __('lat.vat_already_active_error')]);
        }

        $datos = $request->validate([
            'acopio_ids' => ['required', 'array', 'min:1'],
            'acopio_ids.*' => ['integer'],
            'receta_base' => ['nullable', 'string', 'max:40'],
            'receta_nombre' => ['nullable', 'string', 'max:80'],
            'cuajo_ml' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'sal_kg' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'cultivo_g' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'temperatura_actual' => ['nullable', 'numeric', 'min:4', 'max:95'],
            'duracion_minutos' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        $this->tinaWorkflow->iniciarLoteConAcopios($planta, $tina, $datos, $request->user()->id);

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
            'cuajo_ml' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'sal_kg' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'cultivo_g' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'duracion_minutos' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        $cambios = [];

        if (isset($datos['litros'])) {
            $litros = (float) $datos['litros'];
            $cambios['litros'] = $litros;

            if (
                abs($litros - (float) $lote->litros) > 0.001
                && ! isset($datos['cuajo_ml'])
                && ! isset($datos['sal_kg'])
                && ! isset($datos['cultivo_g'])
            ) {
                $recetasBase = $planta->obtenerRecetasBase();
                $claveReceta = ($lote->receta_nombre && isset($recetasBase[$lote->receta_nombre]))
                    ? $lote->receta_nombre
                    : ($lote->tina && isset($recetasBase[$lote->tina->tipo]) ? $lote->tina->tipo : 'principal_queso');
                $receta = $this->tinaWorkflow->dosificarConRecetaBase($litros, $recetasBase[$claveReceta]);
                $cambios['cuajo_ml'] = $receta['cuajo_ml'];
                $cambios['sal_kg'] = $receta['sal_kg'];
                $cambios['cultivo_g'] = $receta['cultivo_g'];
            }
        }

        if (isset($datos['cuajo_ml'])) {
            $cambios['cuajo_ml'] = round((float) $datos['cuajo_ml'], 2);
        }

        if (isset($datos['sal_kg'])) {
            $cambios['sal_kg'] = round((float) $datos['sal_kg'], 2);
        }

        if (isset($datos['cultivo_g'])) {
            $cambios['cultivo_g'] = round((float) $datos['cultivo_g'], 2);
        }

        if (isset($datos['duracion_minutos']) && (int) $datos['duracion_minutos'] !== (int) $lote->duracion_minutos) {
            $cambios['duracion_minutos'] = (int) $datos['duracion_minutos'];
            $cambios['iniciado_en'] = Carbon::now();
            $cambios['alerta_fin_enviada'] = false;
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

            $this->tinaWorkflow->notificar(
                $planta->id,
                'tina_finalizada',
                'tinas',
                __('lat.notif_batch_finished_title', ['vat' => $lote->tina?->codigo ?? 'TINA']),
                __('lat.notif_batch_finished_msg', [
                    'vat' => $lote->tina?->nombre ?? '',
                    'batch' => $lote->codigo,
                    'molds' => (int) $datos['moldes'],
                    'liters' => number_format((float) $lote->litros, 1),
                ]),
                ['lote_id' => $lote->id, 'tina_id' => $lote->tina_id],
                $request->user()->id
            );

            return back()->with('exito', __('lat.batch_finished'));
        }

        if ($cambios !== []) {
            $lote->update($cambios);
        }

        return back()->with('exito', __('lat.vat_updated'));
    }

    public function actualizarRecetasBase(Request $request): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);

        $reglas = [
            'recetas' => ['required', 'array', 'min:1'],
        ];

        foreach (Tina::TIPOS_PREESTABLECIDOS as $clave) {
            $reglas["recetas.{$clave}"] = ['nullable', 'array'];
            $reglas["recetas.{$clave}.cuajo_x_100l"] = ["required_with:recetas.{$clave}", 'numeric', 'min:0', 'max:500'];
            $reglas["recetas.{$clave}.sal_x_100l"] = ["required_with:recetas.{$clave}", 'numeric', 'min:0', 'max:500'];
            $reglas["recetas.{$clave}.cultivo_x_100l"] = ["required_with:recetas.{$clave}", 'numeric', 'min:0', 'max:500'];
            $reglas["recetas.{$clave}.litros_x_molde"] = ['nullable', 'numeric', 'min:0.5', 'max:500'];
            $reglas["recetas.{$clave}.temp_inicial"] = ["required_with:recetas.{$clave}", 'numeric', 'min:4', 'max:95'];
            $reglas["recetas.{$clave}.duracion_minutos"] = ["required_with:recetas.{$clave}", 'integer', 'min:1', 'max:1440'];
        }

        $datos = $request->validate($reglas);
        $recetasActuales = $planta->obtenerRecetasBase();
        $hayCambios = false;

        foreach (Tina::TIPOS_PREESTABLECIDOS as $clave) {
            if (! isset($datos['recetas'][$clave]) || ! is_array($datos['recetas'][$clave])) {
                continue;
            }

            $entrada = $datos['recetas'][$clave];
            $recetasActuales[$clave] = [
                'cuajo_x_100l' => round((float) $entrada['cuajo_x_100l'], 2),
                'sal_x_100l' => round((float) $entrada['sal_x_100l'], 2),
                'cultivo_x_100l' => round((float) $entrada['cultivo_x_100l'], 2),
                'litros_x_molde' => isset($entrada['litros_x_molde']) && $entrada['litros_x_molde'] !== ''
                    ? round((float) $entrada['litros_x_molde'], 1)
                    : (float) ($recetasActuales[$clave]['litros_x_molde'] ?? 9.0),
                'temp_inicial' => round((float) $entrada['temp_inicial'], 1),
                'duracion_minutos' => (int) $entrada['duracion_minutos'],
            ];
            $hayCambios = true;
        }

        if (! $hayCambios) {
            return back()->withErrors(['recetas' => __('lat.vat_milk_invalid_error')]);
        }

        $planta->update(['recetas_base' => $recetasActuales]);

        return back()->with('exito', __('lat.base_recipes_updated'));
    }

    public function consultarNotificaciones(Request $request): JsonResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $this->tinaWorkflow->verificarCronometrosVencidos($planta->id);

        $afterId = max(0, (int) $request->query('after_id', 0));
        $resultado = $this->tinaWorkflow->obtenerNotificacionesParaUsuario($planta->id, $request->user()->id, $afterId);

        return response()->json($resultado);
    }

    public function marcarNotificacionesLeidas(Request $request): JsonResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $usuarioId = $request->user()->id;

        $datos = $request->validate([
            'notificacion_id' => ['nullable', 'integer'],
        ]);

        if (! empty($datos['notificacion_id'])) {
            $notif = NotificacionPanel::dePlanta($planta->id)
                ->findOrFail((int) $datos['notificacion_id']);
            $notif->marcarLeidaPor($usuarioId);
        } else {
            $recientes = NotificacionPanel::dePlanta($planta->id)
                ->latest('id')
                ->limit(30)
                ->get();

            foreach ($recientes as $notif) {
                $notif->marcarLeidaPor($usuarioId);
            }
        }

        $actual = $this->tinaWorkflow->obtenerNotificacionesParaUsuario($planta->id, $usuarioId);

        return response()->json([
            'ok' => true,
            'no_leidas' => $actual['no_leidas'],
        ]);
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
