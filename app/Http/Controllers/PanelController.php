<?php

namespace App\Http\Controllers;

use App\Models\Acopio;
use App\Models\AdelantoAplicacion;
use App\Models\InformeActividadPlanta;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\NotificacionPanel;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\ReporteExcel;
use App\Models\Tina;
use App\Models\User;
use App\Services\ExcelExportService;
use App\Services\InformePlantaService;
use App\Services\LacteoService;
use App\Services\TinaWorkflowService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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
use Illuminate\Validation\ValidationException;

/**
 * Controlador principal de operaciones de planta: acopio, calidad, tinas, finanzas, Excel y offline.
 */
class PanelController extends Controller
{
    public function __construct(
        protected LacteoService $service,
        protected ExcelExportService $excel,
        protected TinaWorkflowService $tinaWorkflow,
        protected InformePlantaService $informes,
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

    private function normalizarFechaFiltro(mixed $valor, string $default = ''): string
    {
        $texto = trim((string) ($valor ?? ''));
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $texto, $m) !== 1) {
            return $default;
        }

        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $texto : $default;
    }

    private function aplicarFiltrosFechaQuery(Builder $query, string $desde, string $hasta): void
    {
        if ($desde !== '' && $hasta !== '') {
            $this->service->aplicarRangoFecha($query, $desde, $hasta);
        } elseif ($desde !== '') {
            $query->where('fecha', '>=', $desde);
        } elseif ($hasta !== '') {
            $this->service->aplicarHastaFecha($query, $hasta);
        }
    }

    /**
     * @return array{desde: string, hasta: string, proveedor_id: int|string, sector: string, estado_pago: string}
     */
    private function resolverFiltrosLiquidacion(Request $request, string $defDesde, string $defHasta): array
    {
        $desde = $this->normalizarFechaFiltro($request->query('liq_desde'), $defDesde);
        $hasta = $this->normalizarFechaFiltro($request->query('liq_hasta'), $defHasta);
        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $provRaw = $request->query('liq_proveedor_id', '');
        $estadoRaw = trim((string) $request->query('liq_estado', $request->query('liq_estado_pago', '')));

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'proveedor_id' => is_numeric($provRaw) && (int) $provRaw > 0 ? (int) $provRaw : '',
            'sector' => trim((string) $request->query('liq_sector', '')),
            'estado_pago' => in_array($estadoRaw, Liquidacion::ESTADOS_PAGO, true) ? $estadoRaw : '',
        ];
    }

    /**
     * @return array{desde: string, hasta: string, producto: string, canal: string, proveedor_id: int|string}
     */
    private function resolverFiltrosVentas(Request $request): array
    {
        $desde = $this->normalizarFechaFiltro($request->query('v_desde'));
        $hasta = $this->normalizarFechaFiltro($request->query('v_hasta'));
        if ($desde !== '' && $hasta !== '' && $desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $provRaw = $request->query('v_proveedor_id', '');

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'producto' => trim((string) $request->query('v_producto', '')),
            'canal' => trim((string) $request->query('v_canal', '')),
            'proveedor_id' => is_numeric($provRaw) && (int) $provRaw > 0 ? (int) $provRaw : '',
        ];
    }

    /**
     * @return array{desde: string, hasta: string, tipo: string, proveedor_id: int|string}
     */
    private function resolverFiltrosSueldos(Request $request): array
    {
        $desde = $this->normalizarFechaFiltro($request->query('s_desde'));
        $hasta = $this->normalizarFechaFiltro($request->query('s_hasta'));
        if ($desde !== '' && $hasta !== '' && $desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $tipoRaw = trim((string) $request->query('s_tipo', ''));
        $provRaw = $request->query('s_proveedor_id', '');

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'tipo' => in_array($tipoRaw, Liquidacion::TIPOS_SUELDOS_PAGOS, true) ? $tipoRaw : '',
            'proveedor_id' => is_numeric($provRaw) && (int) $provRaw > 0 ? (int) $provRaw : '',
        ];
    }

    /**
     * @param  array{desde?: string, hasta?: string, producto?: string, canal?: string, proveedor_id?: int|string}  $filtros
     * @return Collection<int, Liquidacion>
     */
    private function consultarMovimientosVentas(int $plantaId, array $filtros, int $limit = 120): Collection
    {
        $query = Liquidacion::with('proveedor:id,nombre')
            ->dePlanta($plantaId)
            ->whereIn('tipo', Liquidacion::TIPOS_VENTA);

        $this->aplicarFiltrosFechaQuery($query, $filtros['desde'] ?? '', $filtros['hasta'] ?? '');

        return $query
            ->when(($filtros['producto'] ?? '') !== '', fn ($q) => $q->where('producto_categoria', (string) $filtros['producto']))
            ->when(($filtros['canal'] ?? '') !== '', fn ($q) => $q->where('canal_referencia', (string) $filtros['canal']))
            ->when(($filtros['proveedor_id'] ?? '') !== '', fn ($q) => $q->where('proveedor_id', (int) $filtros['proveedor_id']))
            ->latest('fecha')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array{desde?: string, hasta?: string, tipo?: string, proveedor_id?: int|string}  $filtros
     * @return Collection<int, Liquidacion>
     */
    private function consultarMovimientosSueldos(int $plantaId, array $filtros, int $limit = 120): Collection
    {
        $query = Liquidacion::with('proveedor:id,nombre')
            ->dePlanta($plantaId)
            ->whereIn('tipo', Liquidacion::TIPOS_SUELDOS_PAGOS);

        $this->aplicarFiltrosFechaQuery($query, $filtros['desde'] ?? '', $filtros['hasta'] ?? '');

        return $query
            ->when(($filtros['tipo'] ?? '') !== '', fn ($q) => $q->where('tipo', (string) $filtros['tipo']))
            ->when(($filtros['proveedor_id'] ?? '') !== '', fn ($q) => $q->where('proveedor_id', (int) $filtros['proveedor_id']))
            ->latest('fecha')
            ->latest('id')
            ->limit($limit)
            ->get();
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
        $disponibles = $acopios->filter(
            fn (Acopio $acopio): bool => $acopio->lote_id === null && $acopio->calidad !== 'descartada'
        );

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

        $proveedores = ($puedeVerAcopio || $puedeVerCalidad || $puedeVerFinanzas)
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
        if ($puedeVerAcopio || $puedeVerTinas || $puedeVerDashboard || ($puedeVerCalidad && ! $hayFiltrosAcopio)) {
            $acopiosQuery = Acopio::with(['proveedor:id,codigo,nombre,sector', 'evaluador:id,name'])
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

        $calVistaRaw = (string) $request->query('cal_vista', 'hoy');
        $calEstadoRaw = (string) $request->query('cal_estado', '');
        $calProvRaw = $request->query('cal_proveedor_id', '');
        $filtrosCalidad = [
            'vista' => in_array($calVistaRaw, ['hoy', 'pendientes', 'todos'], true) ? $calVistaRaw : 'hoy',
            'fecha_desde' => $this->normalizarFechaFiltro($request->query('cal_fecha_desde')),
            'fecha_hasta' => $this->normalizarFechaFiltro($request->query('cal_fecha_hasta')),
            'estado' => in_array($calEstadoRaw, ['pendiente', 'buena', 'acida', 'descartada'], true) ? $calEstadoRaw : '',
            'proveedor_id' => is_numeric($calProvRaw) && (int) $calProvRaw > 0 ? (int) $calProvRaw : '',
        ];
        if (
            $filtrosCalidad['fecha_desde'] !== ''
            && $filtrosCalidad['fecha_hasta'] !== ''
            && $filtrosCalidad['fecha_desde'] > $filtrosCalidad['fecha_hasta']
        ) {
            [$filtrosCalidad['fecha_desde'], $filtrosCalidad['fecha_hasta']] = [
                $filtrosCalidad['fecha_hasta'],
                $filtrosCalidad['fecha_desde'],
            ];
        }
        $hayFiltrosCalidad = $filtrosCalidad['vista'] !== 'hoy'
            || $filtrosCalidad['fecha_desde'] !== ''
            || $filtrosCalidad['fecha_hasta'] !== ''
            || $filtrosCalidad['estado'] !== ''
            || $filtrosCalidad['proveedor_id'] !== '';

        $acopiosCalidad = collect();
        $pendientesOtrosDias = 0;

        if ($puedeVerCalidad) {
            if (! $hayFiltrosCalidad && ! $hayFiltrosAcopio) {
                $acopiosCalidad = $acopiosHoy;
            } else {
                $calQuery = Acopio::with(['proveedor:id,codigo,nombre,sector', 'evaluador:id,name'])
                    ->dePlanta($plantaId);

                if ($filtrosCalidad['vista'] === 'pendientes') {
                    $calQuery->where('calidad', 'pendiente');
                } elseif (
                    $filtrosCalidad['vista'] === 'hoy'
                    && $filtrosCalidad['fecha_desde'] === ''
                    && $filtrosCalidad['fecha_hasta'] === ''
                ) {
                    $calQuery->delDia($hoy);
                }

                $this->aplicarFiltrosFechaQuery($calQuery, $filtrosCalidad['fecha_desde'], $filtrosCalidad['fecha_hasta']);

                if ($filtrosCalidad['estado'] !== '' && $filtrosCalidad['vista'] !== 'pendientes') {
                    $calQuery->where('calidad', $filtrosCalidad['estado']);
                }

                if ($filtrosCalidad['proveedor_id'] !== '') {
                    $calQuery->where('proveedor_id', (int) $filtrosCalidad['proveedor_id']);
                }

                $acopiosCalidad = $calQuery->orderByDesc('fecha')->orderByDesc('id')->limit(200)->get();
            }

            $pendientesOtrosDias = Acopio::dePlanta($plantaId)
                ->where('calidad', 'pendiente')
                ->where('fecha', '<', $hoy)
                ->count();

            $pendientesHoy = ! $hayFiltrosAcopio
                ? $acopiosHoy->where('calidad', 'pendiente')->count()
                : (! $hayFiltrosCalidad && $filtrosCalidad['vista'] === 'hoy'
                    ? $acopiosCalidad->where('calidad', 'pendiente')->count()
                    : Acopio::dePlanta($plantaId)->delDia($hoy)->where('calidad', 'pendiente')->count());

            $totalPendientesCalidad = $pendientesHoy + $pendientesOtrosDias;
        } else {
            $totalPendientesCalidad = $acopiosHoy->where('calidad', 'pendiente')->count();
        }

        $litrosHoy = (float) $acopiosHoy->sum('litros');

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

        $filtrosLiquidacion = $this->resolverFiltrosLiquidacion($request, $desde, $hasta);
        $filtrosVentas = $this->resolverFiltrosVentas($request);
        $filtrosSueldos = $this->resolverFiltrosSueldos($request);
        $sectoresLiquidacion = $proveedores->pluck('sector')->filter()->unique()->sort()->values();

        $liquidaciones = collect();
        $movimientos = collect();
        $ventasMovimientos = collect();
        $sueldosMovimientos = collect();
        $reportesExcel = collect();
        $contadoresPlanta = collect();
        $destinatariosContables = collect();

        if ($puedeVerFinanzas) {
            $liquidaciones = $this->service->liquidarQuincena(
                $filtrosLiquidacion['desde'],
                $filtrosLiquidacion['hasta'],
                $precioLitro,
                $plantaId,
                $filtrosLiquidacion
            );

            $hayFiltrosVentas = collect($filtrosVentas)->contains(fn ($v): bool => $v !== '' && $v !== null);
            $hayFiltrosSueldos = collect($filtrosSueldos)->contains(fn ($v): bool => $v !== '' && $v !== null);

            if (! $hayFiltrosVentas && ! $hayFiltrosSueldos) {
                $movimientos = Liquidacion::with('proveedor:id,nombre')
                    ->dePlanta($plantaId)
                    ->where('tipo', '!=', 'quincena')
                    ->latest('fecha')
                    ->latest('id')
                    ->limit(120)
                    ->get();
                $ventasMovimientos = $movimientos->where('tipo', 'venta_producto')->values();
                $sueldosMovimientos = $movimientos->filter(
                    fn (Liquidacion $m): bool => in_array($m->tipo, Liquidacion::TIPOS_SUELDOS_PAGOS, true)
                )->values();
            } else {
                $ventasMovimientos = $this->consultarMovimientosVentas($plantaId, $filtrosVentas);
                $sueldosMovimientos = $this->consultarMovimientosSueldos($plantaId, $filtrosSueldos);
                $movimientos = $ventasMovimientos->merge($sueldosMovimientos)
                    ->sortByDesc(fn (Liquidacion $m): array => [$m->fecha?->format('Y-m-d') ?? '', $m->id])
                    ->values();
            }

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

            $destinatariosContables = $contadoresBase
                ->filter(function (User $u) use ($usuario, $plantaId): bool {
                    if (! $u->activo || $u->id === $usuario->id) {
                        return false;
                    }

                    $esMismaPlanta = (int) $u->planta_id === $plantaId;

                    if ($usuario->cargo === 'contador_externo') {
                        return $u->cargo === 'administracion'
                            || ($esMismaPlanta && $u->cargo === 'encargado_planta');
                    }

                    if ($usuario->esAdmin()) {
                        return $u->cargo === 'administracion'
                            || ($esMismaPlanta && in_array($u->cargo, ['contador_externo', 'encargado_planta'], true));
                    }

                    if ($usuario->cargo === 'encargado_planta') {
                        return $u->cargo === 'administracion'
                            || ($esMismaPlanta && $u->cargo === 'contador_externo');
                    }

                    if ($usuario->tieneAlgunPermiso(['finanzas.exportar', 'finanzas.enviar_excel'])) {
                        return $u->cargo === 'administracion'
                            || ($esMismaPlanta && in_array($u->cargo, ['contador_externo', 'encargado_planta'], true));
                    }

                    return false;
                })
                ->values();
        }

        $infPlantaRaw = $request->query('inf_planta_id', '');
        $filtrosInformes = [
            'planta_id' => is_numeric($infPlantaRaw) && (int) $infPlantaRaw > 0 ? (int) $infPlantaRaw : '',
            'fecha_desde' => $this->normalizarFechaFiltro($request->query('inf_fecha_desde')),
            'fecha_hasta' => $this->normalizarFechaFiltro($request->query('inf_fecha_hasta')),
            'origen' => mb_substr(trim((string) $request->query('inf_origen', '')), 0, 30),
        ];
        if (
            $filtrosInformes['fecha_desde'] !== ''
            && $filtrosInformes['fecha_hasta'] !== ''
            && $filtrosInformes['fecha_desde'] > $filtrosInformes['fecha_hasta']
        ) {
            [$filtrosInformes['fecha_desde'], $filtrosInformes['fecha_hasta']] = [
                $filtrosInformes['fecha_hasta'],
                $filtrosInformes['fecha_desde'],
            ];
        }
        $informesPlanta = $usuario->esAdmin()
            ? $this->informes->consultarHistorial($filtrosInformes, 40)
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
        $notificacionesPanel = $this->tinaWorkflow->obtenerNotificacionesParaUsuario($plantaId, $usuario);
        $modoOperativoAdmin = $usuario->modoOperativoActivo();

        return view('panel', compact(
            'usuario',
            'plantaActiva',
            'plantas',
            'plantasPapelera',
            'usuarios',
            'proveedores',
            'acopiosHoy',
            'acopiosCalidad',
            'filtrosCalidad',
            'hayFiltrosCalidad',
            'pendientesOtrosDias',
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
            'filtrosLiquidacion',
            'sectoresLiquidacion',
            'movimientos',
            'filtrosVentas',
            'filtrosSueldos',
            'ventasMovimientos',
            'sueldosMovimientos',
            'reportesExcel',
            'contadoresPlanta',
            'destinatariosContables',
            'filtrosInformes',
            'informesPlanta',
            'modoOperativoAdmin',
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

    public function alternarModoOperativo(Request $request): RedirectResponse
    {
        $usuario = $request->user();
        if (! $usuario || ! $usuario->esAdmin()) {
            abort(403, __('lat.admin_forbidden'));
        }

        $activar = $request->boolean('activar');
        $request->session()->put('admin_modo_operativo', $activar);

        $planta = $this->resolverPlantaActiva($request);
        $this->informes->registrarActividad(
            $planta->id,
            $usuario->id,
            'admin',
            'modo_operativo',
            $activar
                ? 'Modo operativo de planta activado por Administración.'
                : 'Modo operativo de planta desactivado por Administración.',
            ['activar' => $activar]
        );

        return back()->with('exito', $activar
            ? __('lat.admin_ops_enabled')
            : __('lat.admin_ops_disabled'));
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

        $acopio->loadMissing('proveedor:id,nombre');
        $proveedorNombre = $acopio->proveedor?->nombre ?? __('lat.producer_default');

        $this->informes->registrarActividad(
            $planta->id,
            $request->user()->id,
            'acopio',
            'acopio_creado',
            "Acopio ({$acopio->metodo}): {$proveedorNombre} · ".number_format((float) $acopio->litros, 1).' L.',
            ['acopio_id' => $acopio->id, 'litros' => (float) $acopio->litros, 'metodo' => $acopio->metodo]
        );

        if ($acopio->metodo === 'voz') {
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

        if ($sincronizados !== []) {
            $this->informes->registrarActividad(
                $planta->id,
                $request->user()->id,
                'acopio',
                'acopio_sync_offline',
                'Sincronización offline: '.count($sincronizados).' entrega(s) registrada(s).',
                ['cantidad' => count($sincronizados)]
            );
        }

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

        if ($acopio->lote_id !== null) {
            return back()->withErrors(['acopio' => __('lat.intake_locked_in_batch')]);
        }

        $puedeEvaluarCalidad = $request->user()->tienePermiso('calidad.evaluar');

        $datos = $request->validate([
            'proveedor_id' => ['required', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'litros' => ['required', 'numeric', 'min:1', 'max:5000'],
            'calidad' => [$puedeEvaluarCalidad ? 'required' : 'nullable', 'in:pendiente,buena,acida,descartada'],
            'metodo' => ['required', 'in:voz,manual'],
            'frase_dictada' => ['nullable', 'string', 'max:255'],
            'fecha' => ['nullable', 'date', 'before_or_equal:today'],
            'hora' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
        ]);

        if (! $puedeEvaluarCalidad && ! empty($datos['calidad']) && $datos['calidad'] !== $acopio->calidad) {
            abort(403, __('lat.intake_quality_permission_denied'));
        }

        $calidadFinal = $puedeEvaluarCalidad
            ? (string) $datos['calidad']
            : (string) $acopio->calidad;

        $destino = match ($calidadFinal) {
            'acida' => 'quesillo_4_dias',
            'buena' => 'queso_yogur',
            'descartada' => 'descartada',
            default => 'pendiente',
        };

        $acopio->update([
            'proveedor_id' => $datos['proveedor_id'],
            'litros' => $datos['litros'],
            'calidad' => $calidadFinal,
            'destino' => $destino,
            'evaluado_en' => $calidadFinal === 'pendiente' ? null : ($acopio->evaluado_en ?? Carbon::now()),
            'evaluado_por' => $calidadFinal === 'pendiente' ? null : ($acopio->evaluado_por ?? $request->user()->id),
            'motivo_descarte' => $calidadFinal === 'descartada'
                ? ($acopio->motivo_descarte ?: __('lat.quality_discard_default_reason'))
                : null,
            'metodo' => $datos['metodo'],
            'frase_dictada' => $request->has('frase_dictada')
                ? (trim((string) ($datos['frase_dictada'] ?? '')) !== '' ? trim((string) ($datos['frase_dictada'] ?? '')) : null)
                : $acopio->frase_dictada,
            'fecha' => ! empty($datos['fecha']) ? $datos['fecha'] : $acopio->fecha,
            'hora' => ! empty($datos['hora'])
                ? (strlen($datos['hora']) === 5 ? $datos['hora'].':00' : $datos['hora'])
                : $acopio->hora,
        ]);

        $this->informes->registrarActividad(
            $planta->id,
            $request->user()->id,
            'acopio',
            'acopio_actualizado',
            "Acopio #{$acopio->id} actualizado: ".number_format((float) $acopio->litros, 1)." L ({$acopio->calidad}).",
            ['acopio_id' => $acopio->id, 'litros' => (float) $acopio->litros]
        );

        return back()->with('exito', __('lat.intake_updated'));
    }

    public function eliminarAcopio(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $acopio = Acopio::dePlanta($planta->id)->findOrFail($id);

        if ($acopio->lote_id !== null) {
            return back()->withErrors(['acopio' => __('lat.intake_locked_in_batch_delete')]);
        }

        $acopioId = $acopio->id;
        $litros = (float) $acopio->litros;

        $eliminado = DB::transaction(function () use ($planta, $id): bool {
            $bloqueado = Acopio::dePlanta($planta->id)->where('id', $id)->lockForUpdate()->firstOrFail();
            if ($bloqueado->lote_id !== null) {
                return false;
            }
            $bloqueado->delete();

            return true;
        });

        if (! $eliminado) {
            return back()->withErrors(['acopio' => __('lat.intake_locked_in_batch_delete')]);
        }

        $this->informes->registrarActividad(
            $planta->id,
            $request->user()->id,
            'acopio',
            'acopio_eliminado',
            "Acopio #{$acopioId} eliminado (".number_format($litros, 1).' L).',
            ['acopio_id' => $acopioId, 'litros' => $litros]
        );

        return back()->with('exito', __('lat.intake_deleted'));
    }

    public function evaluarCalidad(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);

        $datos = $request->validate([
            'calidad' => ['required', 'in:buena,acida,descartada'],
            'motivo_descarte' => ['nullable', 'string', 'max:255'],
        ]);

        $usuarioId = $request->user()->id;
        $rolesDestino = ['encargado_planta', 'control_tina'];

        $acopio = DB::transaction(function () use ($planta, $id, $datos, $usuarioId): ?Acopio {
            $registro = Acopio::dePlanta($planta->id)->where('id', $id)->lockForUpdate()->firstOrFail();

            if ($registro->lote_id !== null) {
                return null;
            }

            if ($datos['calidad'] === 'descartada') {
                $motivo = trim((string) ($datos['motivo_descarte'] ?? ''));
                if ($motivo === '') {
                    $motivo = __('lat.quality_discard_default_reason');
                }

                $registro->update([
                    'calidad' => 'descartada',
                    'destino' => 'descartada',
                    'evaluado_en' => Carbon::now(),
                    'evaluado_por' => $usuarioId,
                    'motivo_descarte' => $motivo,
                ]);
            } elseif ($datos['calidad'] === 'acida') {
                $registro->update([
                    'calidad' => 'acida',
                    'destino' => 'quesillo_4_dias',
                    'evaluado_en' => Carbon::now(),
                    'evaluado_por' => $usuarioId,
                    'motivo_descarte' => null,
                ]);
            } else {
                $registro->update([
                    'calidad' => 'buena',
                    'destino' => 'queso_yogur',
                    'evaluado_en' => Carbon::now(),
                    'evaluado_por' => $usuarioId,
                    'motivo_descarte' => null,
                ]);
            }

            return $registro;
        });

        if (! $acopio instanceof Acopio) {
            return back()->withErrors(['calidad' => __('lat.quality_locked_in_batch')]);
        }

        $acopio->loadMissing('proveedor:id,nombre');
        $provNombre = $acopio->proveedor?->nombre ?? __('lat.producer_default');
        $litrosFmt = number_format((float) $acopio->litros, 1);

        if ($datos['calidad'] === 'descartada') {
            $litros = (float) $acopio->litros;
            $motivo = (string) $acopio->motivo_descarte;

            $this->tinaWorkflow->notificar(
                $planta->id,
                'calidad_descartada',
                'tinas',
                __('lat.notif_quality_discarded_title'),
                __('lat.notif_quality_discarded_msg', ['producer' => $provNombre, 'liters' => $litrosFmt]),
                ['acopio_id' => $acopio->id, 'litros' => $litros, 'motivo' => $motivo],
                $usuarioId,
                rolesDestino: $rolesDestino
            );

            $this->informes->registrarActividad(
                $planta->id,
                $usuarioId,
                'calidad',
                'calidad_descartada',
                "Leche descartada en prueba sanitaria: {$provNombre} ({$litrosFmt} L) · {$motivo}.",
                ['acopio_id' => $acopio->id, 'litros' => $litros, 'motivo' => $motivo]
            );

            return back()->with('exito', __('lat.quality_classified_discarded'));
        }

        if ($datos['calidad'] === 'acida') {
            $this->tinaWorkflow->notificar(
                $planta->id,
                'calidad_acida',
                'tinas',
                __('lat.notif_quality_acid_title'),
                __('lat.notif_quality_acid_msg', ['producer' => $provNombre, 'liters' => $litrosFmt]),
                ['acopio_id' => $acopio->id, 'litros' => (float) $acopio->litros],
                $usuarioId,
                rolesDestino: $rolesDestino
            );

            $this->informes->registrarActividad(
                $planta->id,
                $usuarioId,
                'calidad',
                'calidad_acida',
                "Leche ácida derivada a quesillo: {$provNombre} ({$litrosFmt} L).",
                ['acopio_id' => $acopio->id, 'litros' => (float) $acopio->litros]
            );

            return back()->with('exito', __('lat.quality_classified_acid'));
        }

        $this->tinaWorkflow->notificar(
            $planta->id,
            'calidad_aprobada',
            'tinas',
            __('lat.notif_quality_good_title'),
            __('lat.notif_quality_good_msg', ['producer' => $provNombre, 'liters' => $litrosFmt]),
            ['acopio_id' => $acopio->id, 'litros' => (float) $acopio->litros],
            $usuarioId,
            rolesDestino: $rolesDestino
        );

        $this->informes->registrarActividad(
            $planta->id,
            $usuarioId,
            'calidad',
            'calidad_buena',
            "Leche aprobada para tina: {$provNombre} ({$litrosFmt} L).",
            ['acopio_id' => $acopio->id, 'litros' => (float) $acopio->litros]
        );

        return back()->with('exito', __('lat.quality_classified_good'));
    }

    public function crearTina(Request $request): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $this->normalizarCodigoTinaInput($request);
        if ($request->has('tipo')) {
            $request->merge(['tipo' => Tina::normalizarTipo((string) $request->input('tipo'))]);
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
            $request->merge(['tipo' => Tina::normalizarTipo((string) $request->input('tipo'))]);
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

        $lote = $this->tinaWorkflow->iniciarLoteConAcopios($planta, $tina, $datos, $request->user()->id);

        $this->informes->registrarActividad(
            $planta->id,
            $request->user()->id,
            'tinas',
            'lote_iniciado',
            "Lote {$lote->codigo} iniciado en {$tina->codigo} (".number_format((float) $lote->litros, 1).' L).',
            ['lote_id' => $lote->id, 'tina_id' => $tina->id, 'litros' => (float) $lote->litros]
        );

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
                $request->user()->id,
                rolesDestino: ['encargado_planta', 'control_tina']
            );

            $this->informes->registrarActividad(
                $planta->id,
                $request->user()->id,
                'tinas',
                'lote_finalizado',
                "Lote {$lote->codigo} finalizado en ".($lote->tina?->codigo ?? 'TINA').' con '.(int) $datos['moldes'].' moldes.',
                ['lote_id' => $lote->id, 'tina_id' => $lote->tina_id, 'moldes' => (int) $datos['moldes']]
            );

            return back()->with('exito', __('lat.batch_finished'));
        }

        if ($cambios !== []) {
            $lote->update($cambios);

            $this->informes->registrarActividad(
                $planta->id,
                $request->user()->id,
                'tinas',
                'lote_actualizado',
                "Lote {$lote->codigo} actualizado (".number_format((float) $lote->litros, 1).' L, '.number_format((float) $lote->temperatura_actual, 1).' °C).',
                ['lote_id' => $lote->id, 'tina_id' => $lote->tina_id]
            );
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
        $usuario = $request->user();
        $this->tinaWorkflow->verificarCronometrosVencidos($planta->id);

        if ($usuario->esAdmin()) {
            $this->informes->verificarYGenerarInformes24h(false);
        }

        $afterId = max(0, (int) $request->query('after_id', 0));
        $resultado = $this->tinaWorkflow->obtenerNotificacionesParaUsuario($planta->id, $usuario, $afterId);

        return response()->json($resultado);
    }

    public function marcarNotificacionesLeidas(Request $request): JsonResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $usuario = $request->user();
        $usuarioId = $usuario->id;

        $datos = $request->validate([
            'notificacion_id' => ['nullable', 'integer'],
        ]);

        $scopeQuery = NotificacionPanel::query();
        if ($usuario->esAdmin()) {
            $scopeQuery->where(function ($q) use ($planta, $usuarioId): void {
                $q->where('planta_id', $planta->id)
                    ->orWhere('alcance_global', true)
                    ->orWhere('destinatario_id', $usuarioId);
            });
        } else {
            $scopeQuery->where('planta_id', $planta->id)
                ->where('alcance_global', false);
        }

        if (! empty($datos['notificacion_id'])) {
            $notif = $scopeQuery->findOrFail((int) $datos['notificacion_id']);
            if (! $notif->esVisiblePara($usuario, $planta->id)) {
                abort(404);
            }
            $notif->marcarLeidaPor($usuarioId);
        } else {
            $recientes = $scopeQuery->latest('id')->limit(45)->get();

            foreach ($recientes as $notif) {
                if ($notif->esVisiblePara($usuario, $planta->id)) {
                    $notif->marcarLeidaPor($usuarioId);
                }
            }
        }

        $actual = $this->tinaWorkflow->obtenerNotificacionesParaUsuario($planta->id, $usuario);

        return response()->json([
            'ok' => true,
            'no_leidas' => $actual['no_leidas'],
        ]);
    }

    public function enviarNotificacionContable(Request $request): RedirectResponse
    {
        $usuario = $request->user();
        $autorizado = in_array($usuario->cargo, ['administracion', 'encargado_planta', 'contador_externo'], true)
            || $usuario->tieneAlgunPermiso(['finanzas.exportar', 'finanzas.enviar_excel']);

        if (! $autorizado) {
            abort(403, __('lat.forbidden'));
        }

        $planta = $this->resolverPlantaActiva($request);

        $datos = $request->validate([
            'destinatario_id' => ['required', 'integer', 'exists:users,id'],
            'asunto' => ['required', 'string', 'max:120'],
            'mensaje' => ['required', 'string', 'max:300'],
        ]);

        $destinatario = User::activos()
            ->where('id', (int) $datos['destinatario_id'])
            ->where(function ($q) use ($planta): void {
                $q->where('planta_id', $planta->id)->orWhere('cargo', 'administracion');
            })
            ->whereIn('cargo', ['administracion', 'encargado_planta', 'contador_externo'])
            ->first();

        if (! $destinatario || $destinatario->id === $usuario->id) {
            return back()->withErrors(['destinatario_id' => 'Destinatario contable no válido para esta planta.']);
        }

        $this->tinaWorkflow->notificar(
            $planta->id,
            'comunicacion_contable',
            'finanzas',
            $datos['asunto'],
            $datos['mensaje'],
            ['emisor' => $usuario->name],
            $usuario->id,
            $destinatario->id,
            null,
            'verde',
            $destinatario->esAdmin()
        );

        $this->informes->registrarActividad(
            $planta->id,
            $usuario->id,
            'finanzas',
            'notificacion_contable',
            "Aviso contable enviado a {$destinatario->name}: {$datos['asunto']}.",
            ['destinatario_id' => $destinatario->id]
        );

        return back()->with('exito', "Aviso contable enviado a {$destinatario->name}.");
    }

    public function guardarLiquidacionGrid(Request $request): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        [$defDesde, $defHasta] = $this->periodoActual();

        $datos = $request->validate([
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
            'filas' => ['required', 'array', 'min:1'],
            'filas.*.proveedor_id' => ['required', 'integer', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'filas.*.litros' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'filas.*.precio_litro' => ['required', 'numeric', 'min:0.5', 'max:20'],
            'filas.*.adelantos' => ['required', 'numeric', 'min:0', 'max:100000'],
            'filas.*.estado_pago' => ['required', Rule::in(Liquidacion::ESTADOS_PAGO)],
        ]);

        $desde = ! empty($datos['fecha_desde']) ? Carbon::parse((string) $datos['fecha_desde'])->toDateString() : $defDesde;
        $hasta = ! empty($datos['fecha_hasta']) ? Carbon::parse((string) $datos['fecha_hasta'])->toDateString() : $defHasta;
        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $provIds = collect($datos['filas'])->pluck('proveedor_id')->map('intval')->unique()->all();
        $proveedoresPlanta = Proveedor::dePlanta($planta->id)->whereIn('id', $provIds)->get(['id', 'nombre'])->keyBy('id');
        $hoy = Carbon::today()->toDateString();
        $fechaAjuste = $hoy <= $hasta ? $hoy : $hasta;
        $fechaQuincena = ($hoy >= $desde && $hoy <= $hasta) ? $hoy : $hasta;

        DB::transaction(function () use ($datos, $planta, $proveedoresPlanta, $desde, $hasta, $fechaAjuste, $fechaQuincena): void {
            foreach ($datos['filas'] as $fila) {
                $provId = (int) $fila['proveedor_id'];
                if (! $proveedoresPlanta->has($provId)) {
                    continue;
                }

                $acopioResumen = Acopio::dePlanta($planta->id)
                    ->where('proveedor_id', $provId)
                    ->delPeriodo($desde, $hasta)
                    ->liquidables()
                    ->selectRaw('COUNT(*) as total, COALESCE(SUM(litros), 0) as sum_litros, COALESCE(SUM(litros * COALESCE(precio_litro, ?)), 0) as sum_bruto', [(float) $planta->precio_litro])
                    ->first();

                $totalLitrosAcopio = round((float) ($acopioResumen->sum_litros ?? 0), 2);
                $litrosSolicitados = isset($fila['litros']) && $fila['litros'] !== ''
                    ? round((float) $fila['litros'], 2)
                    : $totalLitrosAcopio;
                $esManualLitros = abs($litrosSolicitados - $totalLitrosAcopio) >= 0.01;
                $totalLitrosProv = $esManualLitros ? $litrosSolicitados : $totalLitrosAcopio;

                $precioSolicitado = round((float) $fila['precio_litro'], 2);
                $precioActualAvg = $totalLitrosAcopio > 0
                    ? round(((float) $acopioResumen->sum_bruto) / $totalLitrosAcopio, 2)
                    : round((float) $planta->precio_litro, 2);

                if ((int) ($acopioResumen->total ?? 0) > 0 && abs($precioSolicitado - $precioActualAvg) >= 0.01) {
                    Acopio::dePlanta($planta->id)
                        ->where('proveedor_id', $provId)
                        ->delPeriodo($desde, $hasta)
                        ->liquidables()
                        ->update(['precio_litro' => $precioSolicitado]);
                    $precioEfectivo = $precioSolicitado;
                    $brutoProv = round($totalLitrosProv * $precioEfectivo, 2);
                } else {
                    $precioEfectivo = $esManualLitros ? $precioSolicitado : $precioActualAvg;
                    $brutoProv = $esManualLitros
                        ? round($totalLitrosProv * $precioEfectivo, 2)
                        : round((float) ($acopioResumen->sum_bruto ?? 0), 2);
                }

                $quincenaQuery = Liquidacion::dePlanta($planta->id)
                    ->where('proveedor_id', $provId)
                    ->where('tipo', 'quincena');
                $this->service->aplicarRangoFecha($quincenaQuery, $desde, $hasta);
                $quincena = $quincenaQuery->orderByDesc('id')->lockForUpdate()->first();

                $aplicacionesActuales = $quincena !== null
                    ? AdelantoAplicacion::where('quincena_liquidacion_id', $quincena->id)
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('adelanto_liquidacion_id')
                    : collect();

                $idsVinculados = $aplicacionesActuales->keys()->map('intval')->all();

                $candidatosQuery = Liquidacion::dePlanta($planta->id)
                    ->where('proveedor_id', $provId)
                    ->whereIn('tipo', Liquidacion::TIPOS_ADELANTO)
                    ->where(function ($q) use ($hasta, $idsVinculados): void {
                        $q->where(function ($sub) use ($hasta): void {
                            $sub->where('descontado', false);
                            $this->service->aplicarHastaFecha($sub, $hasta);
                        });
                        if ($idsVinculados !== []) {
                            $q->orWhereIn('id', $idsVinculados);
                        }
                    });
                $candidatos = $candidatosQuery
                    ->orderBy('fecha')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $aplicadoOtrasMap = $candidatos->isNotEmpty()
                    ? AdelantoAplicacion::whereIn('adelanto_liquidacion_id', $candidatos->pluck('id')->all())
                        ->when($quincena !== null, fn ($q) => $q->where('quincena_liquidacion_id', '!=', $quincena->id))
                        ->groupBy('adelanto_liquidacion_id')
                        ->selectRaw('adelanto_liquidacion_id, SUM(monto_aplicado) as total_aplicado')
                        ->pluck('total_aplicado', 'adelanto_liquidacion_id')
                    : collect();

                $disponibleTotal = 0.0;
                $disponiblesPorId = [];
                foreach ($candidatos as $adel) {
                    $yaAplicadoOtras = (float) ($aplicadoOtrasMap[$adel->id] ?? 0.0);
                    $disp = max(0.0, round((float) $adel->monto - $yaAplicadoOtras, 2));
                    $disponiblesPorId[$adel->id] = $disp;
                    $disponibleTotal = round($disponibleTotal + $disp, 2);
                }

                $adelantoSolicitado = round((float) $fila['adelantos'], 2);
                $excedente = round($adelantoSolicitado - $disponibleTotal, 2);
                if ($excedente >= 0.01) {
                    $nuevoAjuste = Liquidacion::create([
                        'planta_id' => $planta->id,
                        'proveedor_id' => $provId,
                        'fecha' => $fechaAjuste,
                        'tipo' => 'adelanto_efectivo',
                        'concepto' => 'Ajuste de adelanto en liquidación',
                        'litros' => 0,
                        'cantidad_unidades' => 0,
                        'monto' => $excedente,
                        'comision' => 0.00,
                        'descontado' => false,
                        'estado_pago' => $fila['estado_pago'],
                    ]);
                    $candidatos->push($nuevoAjuste);
                    $disponiblesPorId[$nuevoAjuste->id] = $excedente;
                }

                if ($quincena === null) {
                    $quincena = Liquidacion::create([
                        'planta_id' => $planta->id,
                        'proveedor_id' => $provId,
                        'fecha' => $fechaQuincena,
                        'tipo' => 'quincena',
                        'concepto' => "Liquidación quincenal {$desde} al {$hasta}",
                        'canal_referencia' => $esManualLitros ? 'manual_litros' : null,
                        'litros' => $totalLitrosProv,
                        'cantidad_unidades' => 0,
                        'precio_unitario' => $precioEfectivo,
                        'monto' => max(0, round($brutoProv - $adelantoSolicitado, 2)),
                        'comision' => 0.00,
                        'descontado' => true,
                        'estado_pago' => $fila['estado_pago'],
                    ]);
                }

                $adelantosTocadosIds = [];
                $totalAplicadoEnQuincena = 0.0;

                if ($fila['estado_pago'] === 'pagado') {
                    $restantePorAplicar = $brutoProv > 0.0
                        ? round(min($adelantoSolicitado, $brutoProv), 2)
                        : 0.0;

                    foreach ($candidatos as $adel) {
                        $disp = $disponiblesPorId[$adel->id] ?? 0.0;
                        $aplicar = $restantePorAplicar >= 0.01 && $disp >= 0.01
                            ? round(min($disp, $restantePorAplicar), 2)
                            : 0.0;

                        if ($aplicar >= 0.01) {
                            AdelantoAplicacion::updateOrCreate(
                                [
                                    'quincena_liquidacion_id' => $quincena->id,
                                    'adelanto_liquidacion_id' => $adel->id,
                                ],
                                [
                                    'planta_id' => $planta->id,
                                    'monto_aplicado' => $aplicar,
                                ]
                            );
                            $totalAplicadoEnQuincena = round($totalAplicadoEnQuincena + $aplicar, 2);
                            $restantePorAplicar = round($restantePorAplicar - $aplicar, 2);
                            $adelantosTocadosIds[] = $adel->id;
                        } elseif ($aplicacionesActuales->has($adel->id)) {
                            $aplicacionesActuales->get($adel->id)->delete();
                            $adelantosTocadosIds[] = $adel->id;
                        }
                    }
                } else {
                    if ($aplicacionesActuales->isNotEmpty()) {
                        AdelantoAplicacion::where('quincena_liquidacion_id', $quincena->id)->delete();
                        $adelantosTocadosIds = $idsVinculados;
                    }
                }

                $adelantosTocadosIds = array_values(array_unique($adelantosTocadosIds));
                if ($adelantosTocadosIds !== []) {
                    $totalesAplicados = AdelantoAplicacion::whereIn('adelanto_liquidacion_id', $adelantosTocadosIds)
                        ->groupBy('adelanto_liquidacion_id')
                        ->selectRaw('adelanto_liquidacion_id, SUM(monto_aplicado) as total_aplicado')
                        ->pluck('total_aplicado', 'adelanto_liquidacion_id');

                    foreach ($candidatos->whereIn('id', $adelantosTocadosIds) as $adelTocado) {
                        $sumAplicado = (float) ($totalesAplicados[$adelTocado->id] ?? 0.0);
                        $estaSaldado = $sumAplicado >= ((float) $adelTocado->monto - 0.009);
                        if ((bool) $adelTocado->descontado !== $estaSaldado) {
                            $adelTocado->update(['descontado' => $estaSaldado]);
                        }
                    }
                }

                $montoDescontadoFinal = $fila['estado_pago'] === 'pagado'
                    ? $totalAplicadoEnQuincena
                    : $adelantoSolicitado;

                $quincena->update([
                    'canal_referencia' => $esManualLitros ? 'manual_litros' : null,
                    'litros' => $totalLitrosProv,
                    'precio_unitario' => $precioEfectivo,
                    'monto' => max(0, round($brutoProv - $montoDescontadoFinal, 2)),
                    'comision' => 0.00,
                    'descontado' => true,
                    'estado_pago' => $fila['estado_pago'],
                ]);
            }
        });

        $this->informes->registrarActividad(
            $planta->id,
            $request->user()->id,
            'finanzas',
            'liquidacion_grid_guardada',
            "Liquidación quincenal ({$desde} al {$hasta}) actualizada para ".count($datos['filas']).' productor(es).',
            ['desde' => $desde, 'hasta' => $hasta, 'filas' => count($datos['filas'])]
        );

        return back()->with('exito', 'Cambios de liquidación quincenal guardados correctamente.');
    }

    /**
     * Devuelve las filas calculadas de liquidación quincenal en JSON para actualización dinámica.
     */
    public function obtenerDatosLiquidacion(Request $request): JsonResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        [$defDesde, $defHasta] = $this->periodoActual();
        $filtros = $this->resolverFiltrosLiquidacion($request, $defDesde, $defHasta);

        $filas = $this->service->liquidarQuincena(
            $filtros['desde'],
            $filtros['hasta'],
            (float) $planta->precio_litro,
            $planta->id,
            $filtros
        );

        return response()->json([
            'desde' => $filtros['desde'],
            'hasta' => $filtros['hasta'],
            'filas' => $filas->values()->all(),
            'totales' => [
                'litros' => round((float) $filas->sum('litros'), 2),
                'bruto' => round((float) $filas->sum('bruto'), 2),
                'adelantos' => round((float) $filas->sum('adelantos'), 2),
                'neto' => round((float) $filas->sum('neto'), 2),
            ],
        ]);
    }

    /**
     * Limpia ajustes manuales de litros del período para recalcular directamente desde los acopios.
     */
    public function sincronizarLiquidacionAcopios(Request $request): JsonResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        [$defDesde, $defHasta] = $this->periodoActual();
        $filtros = $this->resolverFiltrosLiquidacion($request, $defDesde, $defHasta);

        $quincenasQuery = Liquidacion::dePlanta($planta->id)
            ->where('tipo', 'quincena')
            ->where('canal_referencia', 'manual_litros')
            ->when(! empty($filtros['proveedor_id']), fn ($q) => $q->where('proveedor_id', (int) $filtros['proveedor_id']));
        $this->service->aplicarRangoFecha($quincenasQuery, $filtros['desde'], $filtros['hasta']);
        $quincenasQuery->update(['canal_referencia' => null]);

        $filas = $this->service->liquidarQuincena(
            $filtros['desde'],
            $filtros['hasta'],
            (float) $planta->precio_litro,
            $planta->id,
            $filtros
        );

        foreach ($filas as $fila) {
            $qRowQuery = Liquidacion::dePlanta($planta->id)
                ->where('proveedor_id', $fila->proveedor_id)
                ->where('tipo', 'quincena');
            $this->service->aplicarRangoFecha($qRowQuery, $filtros['desde'], $filtros['hasta']);
            $qRowQuery->update([
                'litros' => $fila->litros,
                'precio_unitario' => $fila->precio_litro,
                'monto' => $fila->neto,
            ]);
        }

        return response()->json([
            'desde' => $filtros['desde'],
            'hasta' => $filtros['hasta'],
            'filas' => $filas->values()->all(),
            'totales' => [
                'litros' => round((float) $filas->sum('litros'), 2),
                'bruto' => round((float) $filas->sum('bruto'), 2),
                'adelantos' => round((float) $filas->sum('adelantos'), 2),
                'neto' => round((float) $filas->sum('neto'), 2),
            ],
        ]);
    }

    /**
     * Valida y calcula los datos base de un movimiento financiero (venta o sueldo/pago).
     *
     * @return array{datos: array<string, mixed>, cantidad: int, precio_unitario: ?float, monto: float}
     */
    private function validarYCalcularMovimiento(Request $request, int $plantaId): array
    {
        $tiposPermitidos = array_merge(Liquidacion::TIPOS_VENTA, Liquidacion::TIPOS_SUELDOS_PAGOS);

        $datos = $request->validate([
            'tipo' => ['required', Rule::in($tiposPermitidos)],
            'proveedor_id' => ['nullable', Rule::exists('proveedores', 'id')->where('planta_id', $plantaId)],
            'fecha' => ['nullable', 'date', 'before_or_equal:today'],
            'producto_categoria' => ['nullable', 'string', 'max:60'],
            'canal_referencia' => ['nullable', 'string', 'max:60'],
            'concepto' => ['required', 'string', 'max:160'],
            'cantidad_unidades' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'precio_unitario' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'monto' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'estado_pago' => ['nullable', Rule::in(Liquidacion::ESTADOS_PAGO)],
        ]);

        $cantidad = (int) ($datos['cantidad_unidades'] ?? 0);
        $precioUnitario = isset($datos['precio_unitario']) && $datos['precio_unitario'] !== ''
            ? round((float) $datos['precio_unitario'], 2)
            : null;
        $montoInput = isset($datos['monto']) && $datos['monto'] !== ''
            ? (float) $datos['monto']
            : null;

        if (($montoInput === null || $montoInput <= 0.0) && $cantidad > 0 && $precioUnitario !== null && $precioUnitario > 0.0) {
            $monto = round($cantidad * $precioUnitario, 2);
        } elseif ($montoInput !== null && $montoInput >= 0.5) {
            $monto = round($montoInput, 2);
        } else {
            throw ValidationException::withMessages([
                'monto' => __('validation.min.numeric', ['attribute' => 'monto', 'min' => 0.5]),
            ]);
        }

        return [
            'datos' => $datos,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'monto' => $monto,
        ];
    }

    public function registrarMovimiento(Request $request): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $validado = $this->validarYCalcularMovimiento($request, $planta->id);
        $datos = $validado['datos'];

        $mov = Liquidacion::create([
            'planta_id' => $planta->id,
            'proveedor_id' => ! empty($datos['proveedor_id']) ? $datos['proveedor_id'] : null,
            'fecha' => ! empty($datos['fecha']) ? $datos['fecha'] : Carbon::today()->toDateString(),
            'tipo' => $datos['tipo'],
            'producto_categoria' => ! empty($datos['producto_categoria']) ? trim((string) $datos['producto_categoria']) : null,
            'canal_referencia' => ! empty($datos['canal_referencia']) ? trim((string) $datos['canal_referencia']) : null,
            'concepto' => $datos['concepto'],
            'litros' => 0,
            'cantidad_unidades' => $validado['cantidad'],
            'precio_unitario' => $validado['precio_unitario'],
            'monto' => $validado['monto'],
            'comision' => 0.00,
            'descontado' => ! in_array($datos['tipo'], ['adelanto_efectivo', 'adelanto_queso'], true),
            'estado_pago' => $datos['estado_pago'] ?? 'pendiente',
        ]);

        $this->informes->registrarActividad(
            $planta->id,
            $request->user()->id,
            'finanzas',
            'movimiento_creado',
            "Movimiento ({$mov->tipo}): {$mov->concepto} · S/ ".number_format((float) $mov->monto, 2).'.',
            ['liquidacion_id' => $mov->id, 'tipo' => $mov->tipo, 'monto' => (float) $mov->monto]
        );

        return back()->with('exito', __('lat.movement_created'));
    }

    public function actualizarMovimiento(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $mov = Liquidacion::dePlanta($planta->id)->findOrFail($id);

        if ($mov->tipo === 'quincena') {
            return back()->withErrors(['movimiento' => __('lat.movement_quincena_locked')]);
        }

        $validado = $this->validarYCalcularMovimiento($request, $planta->id);
        $datos = $validado['datos'];
        $aplicado = (float) $mov->aplicacionesComoAdelanto()->sum('monto_aplicado');
        $esAdelantoNuevo = in_array($datos['tipo'], Liquidacion::TIPOS_ADELANTO, true);

        if ($aplicado > 0.009) {
            if (! $esAdelantoNuevo || $validado['monto'] < ($aplicado - 0.009)) {
                return back()->withErrors(['monto' => __('lat.movement_advance_applied_locked')]);
            }
            $descontado = $validado['monto'] <= ($aplicado + 0.009);
        } elseif ($esAdelantoNuevo) {
            $descontado = ($mov->tipo === $datos['tipo']) ? (bool) $mov->descontado : false;
        } else {
            $descontado = true;
        }

        $mov->update([
            'tipo' => $datos['tipo'],
            'proveedor_id' => ! empty($datos['proveedor_id']) ? $datos['proveedor_id'] : null,
            'fecha' => ! empty($datos['fecha']) ? $datos['fecha'] : $mov->fecha,
            'producto_categoria' => $request->has('producto_categoria')
                ? (! empty($datos['producto_categoria']) ? trim((string) $datos['producto_categoria']) : null)
                : $mov->producto_categoria,
            'canal_referencia' => $request->has('canal_referencia')
                ? (! empty($datos['canal_referencia']) ? trim((string) $datos['canal_referencia']) : null)
                : $mov->canal_referencia,
            'concepto' => $datos['concepto'],
            'cantidad_unidades' => $validado['cantidad'],
            'precio_unitario' => $request->has('precio_unitario') ? $validado['precio_unitario'] : $mov->precio_unitario,
            'monto' => $validado['monto'],
            'descontado' => $descontado,
            'estado_pago' => $datos['estado_pago'] ?? ($mov->estado_pago ?? 'pendiente'),
        ]);

        $this->informes->registrarActividad(
            $planta->id,
            $request->user()->id,
            'finanzas',
            'movimiento_actualizado',
            "Movimiento #{$mov->id} actualizado ({$mov->tipo}): {$mov->concepto} · S/ ".number_format((float) $mov->monto, 2).'.',
            ['liquidacion_id' => $mov->id, 'tipo' => $mov->tipo, 'monto' => (float) $mov->monto]
        );

        return back()->with('exito', __('lat.movement_updated'));
    }

    public function eliminarMovimiento(Request $request, int $id): RedirectResponse
    {
        $planta = $this->resolverPlantaActiva($request);
        $mov = Liquidacion::dePlanta($planta->id)->findOrFail($id);

        if ($mov->tipo === 'quincena') {
            return back()->withErrors(['movimiento' => __('lat.movement_quincena_locked')]);
        }

        if ($mov->aplicacionesComoAdelanto()->exists()) {
            return back()->withErrors(['movimiento' => __('lat.movement_advance_applied_locked')]);
        }

        $movId = $mov->id;
        $tipo = $mov->tipo;
        $concepto = $mov->concepto;
        $monto = (float) $mov->monto;
        $mov->delete();

        $this->informes->registrarActividad(
            $planta->id,
            $request->user()->id,
            'finanzas',
            'movimiento_eliminado',
            "Movimiento #{$movId} eliminado ({$tipo}): {$concepto} · S/ ".number_format($monto, 2).'.',
            ['liquidacion_id' => $movId, 'tipo' => $tipo, 'monto' => $monto]
        );

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
        [$defDesde, $defHasta] = $this->periodoActual();
        $puedeVerRed = $request->user()->tienePermiso('dashboard.ver_red');
        $solo = (string) $request->query('solo', 'completo');
        $slug = Str::slug($planta->codigo ?: 'lat');
        $fechaSufijo = Carbon::now()->format('Ymd');

        if ($solo === 'liquidacion') {
            $filtros = $this->resolverFiltrosLiquidacion($request, $defDesde, $defHasta);
            $liquidaciones = $this->service->liquidarQuincena(
                $filtros['desde'],
                $filtros['hasta'],
                (float) $planta->precio_litro,
                $planta->id,
                $filtros
            );
            $xlsx = $this->excel->generarSoloLiquidacionXlsx($planta, $filtros['desde'], $filtros['hasta'], $liquidaciones);
            $filename = "liquidacion_lat_{$slug}_{$fechaSufijo}.xlsx";
        } elseif ($solo === 'ventas') {
            $filtros = $this->resolverFiltrosVentas($request);
            $ventas = $this->consultarMovimientosVentas($planta->id, $filtros, 500);
            $labelDesde = $filtros['desde'] !== '' ? $filtros['desde'] : $defDesde;
            $labelHasta = $filtros['hasta'] !== '' ? $filtros['hasta'] : $defHasta;
            $xlsx = $this->excel->generarSoloVentasXlsx($planta, $labelDesde, $labelHasta, $ventas);
            $filename = "ventas_lat_{$slug}_{$fechaSufijo}.xlsx";
        } elseif ($solo === 'sueldos') {
            $filtros = $this->resolverFiltrosSueldos($request);
            $sueldos = $this->consultarMovimientosSueldos($planta->id, $filtros, 500);
            $labelDesde = $filtros['desde'] !== '' ? $filtros['desde'] : $defDesde;
            $labelHasta = $filtros['hasta'] !== '' ? $filtros['hasta'] : $defHasta;
            $xlsx = $this->excel->generarSoloSueldosXlsx($planta, $labelDesde, $labelHasta, $sueldos);
            $filename = "sueldos_pagos_lat_{$slug}_{$fechaSufijo}.xlsx";
        } else {
            $xlsx = $this->generarBinarioExcelPeriodo($planta, $defDesde, $defHasta, $puedeVerRed);
            $filename = "balance_lat_{$slug}_{$fechaSufijo}.xlsx";
        }

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
            'tipo_reporte' => ['required', 'in:liquidacion_proveedores,ventas_productos,sueldos_pagos,balance_quincenal,ventas_productor,acopios_filtrados'],
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

        if ($datos['tipo_reporte'] === 'liquidacion_proveedores') {
            $liquidaciones = $this->service->liquidarQuincena(
                $desde,
                $hasta,
                (float) $planta->precio_litro,
                $planta->id,
                $provId !== null ? ['proveedor_id' => $provId] : null
            );
            $xlsx = $this->excel->generarSoloLiquidacionXlsx($planta, $desde, $hasta, $liquidaciones);
        } elseif ($datos['tipo_reporte'] === 'ventas_productos') {
            $ventas = $this->consultarMovimientosVentas($planta->id, [
                'desde' => $desde,
                'hasta' => $hasta,
                'producto' => '',
                'canal' => '',
                'proveedor_id' => $provId ?? '',
            ], 500);
            $xlsx = $this->excel->generarSoloVentasXlsx($planta, $desde, $hasta, $ventas);
        } elseif ($datos['tipo_reporte'] === 'sueldos_pagos') {
            $sueldos = $this->consultarMovimientosSueldos($planta->id, [
                'desde' => $desde,
                'hasta' => $hasta,
                'tipo' => '',
                'proveedor_id' => $provId ?? '',
            ], 500);
            $xlsx = $this->excel->generarSoloSueldosXlsx($planta, $desde, $hasta, $sueldos);
        } else {
            $xlsx = $this->generarBinarioExcelPeriodo(
                $planta,
                $desde,
                $hasta,
                $destinatario->tienePermiso('dashboard.ver_red'),
                $provId
            );
        }

        $slug = Str::slug($planta->codigo ?: 'lat');
        $nombreArchivo = "reporte_{$datos['tipo_reporte']}_{$slug}_".Carbon::now()->format('Ymd_His').'.xlsx';
        $rutaArchivo = 'reportes_excel/'.Str::uuid().'.xlsx';

        Storage::disk('local')->put($rutaArchivo, $xlsx);

        $reporte = ReporteExcel::create([
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

        $this->tinaWorkflow->notificar(
            $planta->id,
            'comunicacion_contable',
            'finanzas',
            "Excel enviado · {$planta->codigo}",
            "{$remitente->name} te envió {$nombreArchivo}".(! empty($datos['notas']) ? " ({$datos['notas']})" : ''),
            ['reporte_id' => $reporte->id, 'tipo_reporte' => $datos['tipo_reporte']],
            $remitente->id,
            $destinatario->id,
            null,
            'verde',
            $destinatario->esAdmin()
        );

        $this->informes->registrarActividad(
            $planta->id,
            $remitente->id,
            'finanzas',
            'reporte_excel_enviado',
            "Reporte Excel ({$datos['tipo_reporte']}) enviado a {$destinatario->name}.",
            ['reporte_id' => $reporte->id, 'destinatario_id' => $destinatario->id]
        );

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

        if ($reporte->remitente_id !== null && (int) $reporte->remitente_id !== (int) $usuario->id) {
            $this->tinaWorkflow->notificar(
                $planta->id,
                'comunicacion_contable',
                'finanzas',
                "Excel revisado · {$planta->codigo}",
                "{$usuario->name} marcó como revisado {$reporte->nombre_archivo}.",
                ['reporte_id' => $reporte->id],
                $usuario->id,
                (int) $reporte->remitente_id,
                null,
                'verde',
                false
            );
        }

        return back()->with('exito', 'Reporte Excel marcado como revisado.');
    }

    public function enviarInformePlantaManual(Request $request): RedirectResponse
    {
        $usuario = $request->user();
        if (! $usuario || $usuario->esAdmin() || ($usuario->cargo !== 'encargado_planta' && ! $usuario->tienePermiso('admin.usuarios'))) {
            abort(403, __('lat.forbidden'));
        }

        $planta = $this->resolverPlantaActiva($request);
        $hasta = Carbon::now();
        $informe = $this->informes->generarInformePlanta(
            $planta,
            $hasta->copy()->subDay(),
            $hasta,
            'manual_admin_planta',
            $usuario
        );

        return back()->with('exito', "Informe de actividad #{$informe->id} enviado a Administración.");
    }

    public function descargarInformePlantaTxt(Request $request, int $id): Response
    {
        $usuario = $request->user();
        if (! $usuario || ! $usuario->esAdmin()) {
            abort(403, __('lat.admin_forbidden'));
        }

        $informe = InformeActividadPlanta::with('planta:id,codigo')->findOrFail($id);

        return response($informe->contenido_texto, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$informe->nombreArchivoTxt().'"',
        ]);
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
