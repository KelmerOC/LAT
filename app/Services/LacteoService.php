<?php

namespace App\Services;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\Tina;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Servicio de lógica de negocio para acopio, control térmico, liquidaciones y analítica.
 */
class LacteoService
{
    public const CUAJO_ML_X_LITRO = 0.025;

    public const SAL_KG_X_LITRO = 0.018;

    public const CULTIVO_G_X_LITRO = 0.010;

    public const LITROS_X_MOLDE = 9.0;

    public const TEMP_PASTEURIZACION = 72.0;

    public const TEMP_INOCULACION_MIN = 36.0;

    public const TEMP_INOCULACION_MAX = 40.0;

    /**
     * Calcula la dosificación estequiométrica de insumos según litros de leche.
     *
     * @return array{litros: float, cuajo_ml: float, sal_kg: float, cultivo_g: float, moldes_estimados: int}
     */
    public function dosificarReceta(float $litros): array
    {
        $litros = max(0.0, $litros);

        return [
            'litros' => round($litros, 1),
            'cuajo_ml' => round($litros * self::CUAJO_ML_X_LITRO, 2),
            'sal_kg' => round($litros * self::SAL_KG_X_LITRO, 2),
            'cultivo_g' => round($litros * self::CULTIVO_G_X_LITRO, 2),
            'moldes_estimados' => (int) floor($litros / self::LITROS_X_MOLDE),
        ];
    }

    /**
     * Evalúa el estado térmico de la leche en la curva de 72 °C hacia 36-40 °C.
     *
     * @return array{temperatura: float, porcentaje: int, estado: string, apta: bool}
     */
    public function evaluarTermica(float $temperatura): array
    {
        $estado = match (true) {
            $temperatura >= (self::TEMP_PASTEURIZACION - 0.5) => 'pasteurizando',
            $temperatura > self::TEMP_INOCULACION_MAX => 'enfriando',
            $temperatura >= self::TEMP_INOCULACION_MIN => 'lista_para_cuajo',
            default => 'muy_fria',
        };

        if ($estado === 'muy_fria') {
            $porcentaje = 0;
        } else {
            $rangoTotal = self::TEMP_PASTEURIZACION - self::TEMP_INOCULACION_MAX;
            $enfriado = self::TEMP_PASTEURIZACION - $temperatura;
            $calculado = $rangoTotal > 0 ? ($enfriado / $rangoTotal) * 100 : 100;
            $porcentaje = (int) max(0, min(100, round($calculado)));
        }

        return [
            'temperatura' => round($temperatura, 1),
            'porcentaje' => $porcentaje,
            'estado' => $estado,
            'apta' => ($estado === 'lista_para_cuajo'),
        ];
    }

    /**
     * Aplica filtro de rango sobre columnas DATE sin usar DATE() para conservar índices B-Tree.
     */
    public function aplicarRangoFecha(Builder $query, string $desde, string $hasta, string $columna = 'fecha'): Builder
    {
        $driver = $query->getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            return $query->where($columna, '>=', $desde)
                ->where($columna, '<=', $hasta.' 23:59:59');
        }

        if ($desde === $hasta) {
            return $query->where($columna, $desde);
        }

        return $query->whereBetween($columna, [$desde, $hasta]);
    }

    /**
     * Aplica límite superior sobre columnas DATE sin funciones SQL.
     */
    public function aplicarHastaFecha(Builder $query, string $hasta, string $columna = 'fecha'): Builder
    {
        $driver = $query->getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            return $query->where($columna, '<=', $hasta.' 23:59:59');
        }

        return $query->where($columna, '<=', $hasta);
    }

    /**
     * Calcula la liquidación quincenal por productor aislada por planta.
     */
    public function liquidarQuincena(string $desde, string $hasta, float $precioLitro = 1.65, ?int $plantaId = null): Collection
    {
        $proveedores = Proveedor::query()
            ->select('id', 'planta_id', 'codigo', 'nombre', 'sector', 'activo')
            ->when($plantaId !== null, fn ($q) => $q->where('planta_id', $plantaId))
            ->where(function ($q) use ($desde, $hasta, $plantaId): void {
                $q->where('activo', true)
                    ->orWhereHas('acopios', function ($sub) use ($desde, $hasta, $plantaId): void {
                        $sub->delPeriodo($desde, $hasta)
                            ->when($plantaId !== null, fn ($p) => $p->where('planta_id', $plantaId));
                    })
                    ->orWhereHas('liquidaciones', function ($sub) use ($hasta, $plantaId): void {
                        $sub->whereIn('tipo', ['adelanto_efectivo', 'adelanto_queso'])
                            ->where('descontado', false)
                            ->when($plantaId !== null, fn ($p) => $p->where('planta_id', $plantaId));
                        $this->aplicarHastaFecha($sub, $hasta);
                    });
            })
            ->ordenadosPorNombre()
            ->get();

        if ($proveedores->isEmpty()) {
            return collect();
        }

        $ids = $proveedores->pluck('id')->all();

        $acopiosPorProveedor = Acopio::whereIn('proveedor_id', $ids)
            ->delPeriodo($desde, $hasta)
            ->when($plantaId !== null, fn ($q) => $q->where('planta_id', $plantaId))
            ->groupBy('proveedor_id')
            ->selectRaw('proveedor_id, SUM(litros) as total_litros, SUM(litros * COALESCE(precio_litro, ?)) as total_bruto', [$precioLitro])
            ->get()
            ->keyBy('proveedor_id');

        $adelantosQuery = Liquidacion::whereIn('proveedor_id', $ids)
            ->whereIn('tipo', ['adelanto_efectivo', 'adelanto_queso'])
            ->where('descontado', false)
            ->when($plantaId !== null, fn ($q) => $q->where('planta_id', $plantaId));
        $this->aplicarHastaFecha($adelantosQuery, $hasta);

        $adelantosMap = $adelantosQuery
            ->groupBy('proveedor_id')
            ->selectRaw('proveedor_id, SUM(monto) as total_adelantos')
            ->pluck('total_adelantos', 'proveedor_id');

        return $proveedores->map(function (Proveedor $p) use ($acopiosPorProveedor, $adelantosMap, $precioLitro): object {
            $acopio = $acopiosPorProveedor->get($p->id);
            $litros = (float) ($acopio->total_litros ?? 0);
            $rawBruto = (float) ($acopio->total_bruto ?? 0);
            $bruto = round($rawBruto, 2);
            $adelanto = (float) ($adelantosMap[$p->id] ?? 0);
            $comision = 0.00;
            $neto = max(0, round($bruto - $adelanto, 2));

            return (object) [
                'proveedor_id' => $p->id,
                'codigo' => $p->codigo,
                'nombre' => $p->nombre,
                'sector' => $p->sector,
                'litros' => $litros,
                'precio_litro' => $litros > 0 ? round($rawBruto / $litros, 2) : $precioLitro,
                'bruto' => $bruto,
                'adelantos' => $adelanto,
                'comision' => $comision,
                'neto' => $neto,
            ];
        });
    }

    /**
     * Estadísticas operativas agregadas directamente en SQL para evitar hidratación masiva.
     *
     * @return array<string, mixed>
     */
    public function obtenerEstadisticas(int $plantaActivaId, string $desde, string $hasta, bool $incluirRed = true): array
    {
        $rankingData = $this->obtenerRankingPlantas($desde, $hasta, $incluirRed ? null : $plantaActivaId);
        $rankingPlantas = $rankingData['ranking_plantas'];
        $moldesPorPlanta = $rankingData['moldes_por_planta'];

        $resumenAcopioQuery = Acopio::where('planta_id', $plantaActivaId);
        $this->aplicarRangoFecha($resumenAcopioQuery, $desde, $hasta);
        $resumenAcopio = $resumenAcopioQuery
            ->groupBy('calidad', 'metodo')
            ->selectRaw('calidad, metodo, SUM(litros) as total_litros, COUNT(*) as total_entregas')
            ->get();

        $litrosBuena = (float) $resumenAcopio->where('calidad', 'buena')->sum('total_litros');
        $litrosAcida = (float) $resumenAcopio->where('calidad', 'acida')->sum('total_litros');
        $litrosPendiente = (float) $resumenAcopio->where('calidad', 'pendiente')->sum('total_litros');
        $totalLitrosPlanta = $litrosBuena + $litrosAcida + $litrosPendiente;

        $vozCount = (int) $resumenAcopio->where('metodo', 'voz')->sum('total_entregas');
        $manualCount = (int) $resumenAcopio->where('metodo', 'manual')->sum('total_entregas');
        $totalEntregas = max(1, $vozCount + $manualCount);

        $topQuery = Acopio::with('proveedor:id,codigo,nombre,sector')
            ->where('planta_id', $plantaActivaId);
        $this->aplicarRangoFecha($topQuery, $desde, $hasta);
        $topProductores = $topQuery
            ->groupBy('proveedor_id')
            ->selectRaw('proveedor_id, SUM(litros) as total_litros, COUNT(*) as entregas')
            ->orderByDesc('total_litros')
            ->limit(6)
            ->get()
            ->map(function (Acopio $row) use ($totalLitrosPlanta): object {
                $prov = $row->proveedor;
                $litros = round((float) $row->total_litros, 1);

                return (object) [
                    'codigo' => $prov?->codigo ?? __('lat.no_code'),
                    'nombre' => $prov?->nombre ?? __('lat.producer_default'),
                    'sector' => $prov?->sector ?? '',
                    'litros' => $litros,
                    'entregas' => (int) $row->entregas,
                    'porcentaje' => $totalLitrosPlanta > 0 ? round(($litros / $totalLitrosPlanta) * 100, 1) : 0.0,
                ];
            });

        $movQuery = Liquidacion::where('planta_id', $plantaActivaId);
        $this->aplicarRangoFecha($movQuery, $desde, $hasta);
        $movsPorTipo = $movQuery
            ->groupBy('tipo')
            ->selectRaw('tipo, SUM(monto) as total_monto, SUM(cantidad_unidades) as total_unidades')
            ->get()
            ->keyBy('tipo');

        $ventasRow = $movsPorTipo->get('venta_producto');
        $gastosMonto = (float) ($movsPorTipo->get('ayuda_del_dia')?->total_monto ?? 0)
            + (float) ($movsPorTipo->get('gasto_operativo')?->total_monto ?? 0);
        $adelantosMonto = (float) ($movsPorTipo->get('adelanto_efectivo')?->total_monto ?? 0)
            + (float) ($movsPorTipo->get('adelanto_queso')?->total_monto ?? 0);

        return [
            'ranking_plantas' => $rankingPlantas,
            'planta_lider' => $rankingPlantas->first(),
            'total_ventas_red' => $rankingData['total_ventas_red'],
            'top_productores' => $topProductores,
            'calidad' => [
                'buena_litros' => round($litrosBuena, 1),
                'acida_litros' => round($litrosAcida, 1),
                'pendiente_litros' => round($litrosPendiente, 1),
                'buena_pct' => $totalLitrosPlanta > 0 ? round(($litrosBuena / $totalLitrosPlanta) * 100, 1) : 0.0,
                'acida_pct' => $totalLitrosPlanta > 0 ? round(($litrosAcida / $totalLitrosPlanta) * 100, 1) : 0.0,
            ],
            'metodos' => [
                'voz_count' => $vozCount,
                'manual_count' => $manualCount,
                'voz_pct' => round(($vozCount / $totalEntregas) * 100, 1),
            ],
            'totales_planta' => [
                'litros_periodo' => round($totalLitrosPlanta, 1),
                'ventas_monto' => round((float) ($ventasRow?->total_monto ?? 0), 2),
                'ventas_unidades' => (int) ($ventasRow?->total_unidades ?? 0),
                'gastos_monto' => round($gastosMonto, 2),
                'adelantos_monto' => round($adelantosMonto, 2),
                'moldes_producidos' => (int) ($moldesPorPlanta[$plantaActivaId] ?? 0),
            ],
        ];
    }

    /**
     * Genera las métricas, series temporales y eventos recientes para el dashboard.
     *
     * @param  array<string, mixed>|null  $statsPrecalculadas
     * @param  Collection<int, Tina>|null  $tinasPrecalculadas
     * @return array<string, mixed>
     */
    public function obtenerDatosDashboard(
        int $plantaId,
        string $rango = '7d',
        bool $incluirRed = true,
        ?array $statsPrecalculadas = null,
        ?Collection $tinasPrecalculadas = null
    ): array {
        $hoy = Carbon::today();
        [$inicio, $fin, $etiquetaRango] = match ($rango) {
            'hoy' => [$hoy->copy(), $hoy->copy(), __('lat.range_today')],
            'quincena' => [
                $hoy->day <= 15 ? $hoy->copy()->startOfMonth() : $hoy->copy()->startOfMonth()->addDays(15),
                $hoy->day <= 15 ? $hoy->copy()->startOfMonth()->addDays(14) : $hoy->copy()->endOfMonth(),
                __('lat.range_fortnight'),
            ],
            'mes' => [$hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth(), __('lat.range_month')],
            default => [$hoy->copy()->subDays(6), $hoy->copy(), __('lat.range_7d')],
        };

        $dias = max(1, (int) $inicio->diffInDays($fin) + 1);
        $prevFin = $inicio->copy()->subDay();
        $prevInicio = $prevFin->copy()->subDays($dias - 1);

        $desde = $inicio->toDateString();
        $hasta = $fin->toDateString();
        $prevDesde = $prevInicio->toDateString();
        $prevHasta = $prevFin->toDateString();

        $acopiosQuery = Acopio::with('proveedor:id,codigo,nombre,sector')->where('planta_id', $plantaId);
        $this->aplicarRangoFecha($acopiosQuery, $desde, $hasta);
        $acopiosActual = $acopiosQuery->get();

        $acopiosPrevQuery = Acopio::where('planta_id', $plantaId);
        $this->aplicarRangoFecha($acopiosPrevQuery, $prevDesde, $prevHasta);
        $acopiosPrevLitros = (float) $acopiosPrevQuery->sum('litros');

        $litrosTotal = round((float) $acopiosActual->sum('litros'), 1);
        $litrosBuena = round((float) $acopiosActual->where('calidad', 'buena')->sum('litros'), 1);
        $litrosAcida = round((float) $acopiosActual->where('calidad', 'acida')->sum('litros'), 1);
        $litrosPendiente = round((float) $acopiosActual->where('calidad', 'pendiente')->sum('litros'), 1);
        $buenaPct = $litrosTotal > 0 ? round(($litrosBuena / $litrosTotal) * 100, 1) : 0.0;

        $movsQuery = Liquidacion::with('proveedor:id,nombre')->where('planta_id', $plantaId);
        $this->aplicarRangoFecha($movsQuery, $desde, $hasta);
        $movsActual = $movsQuery->get();

        $ventasActual = round((float) $movsActual->where('tipo', 'venta_producto')->sum('monto'), 2);
        $unidadesActual = (int) $movsActual->where('tipo', 'venta_producto')->sum('cantidad_unidades');
        $gastosActual = round((float) $movsActual->whereIn('tipo', ['ayuda_del_dia', 'gasto_operativo'])->sum('monto'), 2);

        $ventasPrevQuery = Liquidacion::where('planta_id', $plantaId)->where('tipo', 'venta_producto');
        $this->aplicarRangoFecha($ventasPrevQuery, $prevDesde, $prevHasta);
        $ventasPrev = (float) $ventasPrevQuery->sum('monto');

        $lotesQuery = Lote::where('planta_id', $plantaId);
        $this->aplicarRangoFecha($lotesQuery, $desde, $hasta);
        $lotesActual = $lotesQuery->get();

        $moldesActual = (int) $lotesActual->sum('moldes_producidos');
        $lotesPrevQuery = Lote::where('planta_id', $plantaId);
        $this->aplicarRangoFecha($lotesPrevQuery, $prevDesde, $prevHasta);
        $moldesPrev = (int) $lotesPrevQuery->sum('moldes_producidos');

        $acopiosPorDia = $acopiosActual->groupBy(fn (Acopio $a) => $a->fecha?->format('Y-m-d') ?? substr((string) $a->fecha, 0, 10));
        $movsPorDia = $movsActual->groupBy(fn (Liquidacion $m) => $m->fecha?->format('Y-m-d') ?? substr((string) $m->fecha, 0, 10));
        $lotesPorDia = $lotesActual->groupBy(fn (Lote $l) => $l->fecha?->format('Y-m-d') ?? substr((string) $l->fecha, 0, 10));

        $labels = [];
        $serieAcopioBuena = [];
        $serieAcopioAcida = [];
        $serieVentas = [];
        $serieGastos = [];
        $serieMoldes = [];

        foreach (CarbonPeriod::create($inicio, $fin) as $dia) {
            $key = $dia->format('Y-m-d');
            $labels[] = $dia->format('d/m');

            $grupoAc = $acopiosPorDia->get($key, collect());
            $serieAcopioBuena[] = round((float) $grupoAc->where('calidad', 'buena')->sum('litros'), 1);
            $serieAcopioAcida[] = round((float) $grupoAc->where('calidad', 'acida')->sum('litros'), 1);

            $grupoMov = $movsPorDia->get($key, collect());
            $serieVentas[] = round((float) $grupoMov->where('tipo', 'venta_producto')->sum('monto'), 2);
            $serieGastos[] = round((float) $grupoMov->whereIn('tipo', ['ayuda_del_dia', 'gasto_operativo'])->sum('monto'), 2);

            $grupoLote = $lotesPorDia->get($key, collect());
            $serieMoldes[] = (int) $grupoLote->sum('moldes_producidos');
        }

        $tinas = $tinasPrecalculadas ?? Tina::dePlanta($plantaId)
            ->with('loteActivo')
            ->orderBy('codigo')
            ->get();

        $tinasResumen = $tinas->map(function (Tina $tina): object {
            $lote = $tina->loteActivo;
            $ocupacion = $lote && $tina->capacidad_litros > 0
                ? min(100, (int) round(($lote->litros / $tina->capacidad_litros) * 100))
                : 0;
            $termica = $lote ? $this->evaluarTermica($lote->temperatura_actual) : null;

            return (object) [
                'codigo' => $tina->codigo,
                'nombre' => $tina->nombre,
                'capacidad' => (float) $tina->capacidad_litros,
                'activa' => (bool) $tina->activa,
                'lote' => $lote?->codigo,
                'litros' => (float) ($lote?->litros ?? 0),
                'ocupacion' => $ocupacion,
                'temp' => $lote?->temperatura_actual,
                'estado' => $termica['estado'] ?? 'libre',
                'apta_cuajo' => $termica['apta'] ?? false,
            ];
        });

        $eventos = collect();
        foreach (Acopio::with('proveedor:id,nombre')->where('planta_id', $plantaId)->latest('id')->limit(4)->get() as $ac) {
            $metodoClave = 'lat.method_'.$ac->metodo;
            $metodoTrad = __($metodoClave);
            $metodoLabel = $metodoTrad !== $metodoClave ? $metodoTrad : $ac->metodo;

            $eventos->push((object) [
                'tipo' => $ac->calidad === 'acida' ? 'alerta' : 'acopio',
                'titulo' => ($ac->proveedor?->nombre ?? __('lat.producer_default')).' · '.number_format($ac->litros, 1).' L',
                'detalle' => $ac->calidad === 'acida' ? __('lat.quality_diverted') : __('lat.quality_approved', ['method' => $metodoLabel]),
                'timestamp' => $ac->created_at ?? Carbon::parse($ac->fecha),
                'fecha_txt' => ($ac->fecha?->format('d/m') ?? '').' '.substr((string) $ac->hora, 0, 5),
            ]);
        }
        foreach (Liquidacion::with('proveedor:id,nombre')->where('planta_id', $plantaId)->latest('id')->limit(4)->get() as $mv) {
            $eventos->push((object) [
                'tipo' => $mv->tipo === 'venta_producto' ? 'venta' : 'movimiento',
                'titulo' => $mv->concepto,
                'detalle' => ($mv->tipo === 'venta_producto' ? '+ S/ ' : 'S/ ').number_format($mv->monto, 2).($mv->proveedor ? ' · '.$mv->proveedor->nombre : ''),
                'timestamp' => $mv->created_at ?? Carbon::parse($mv->fecha),
                'fecha_txt' => $mv->fecha?->format('d/m/Y') ?? '',
            ]);
        }

        $topProductores = $this->calcularTopProductores($acopiosActual, (float) $acopiosActual->sum('litros'));

        $rankingPlantas = collect();
        if ($incluirRed) {
            if ($statsPrecalculadas !== null && isset($statsPrecalculadas['ranking_plantas']) && $rango === 'quincena') {
                $rankingPlantas = $statsPrecalculadas['ranking_plantas'];
            } else {
                $rankingPlantas = $this->obtenerRankingPlantas($desde, $hasta)['ranking_plantas'];
            }
        }

        return [
            'rango' => $rango,
            'etiqueta_rango' => $etiquetaRango,
            'desde' => $desde,
            'hasta' => $hasta,
            'kpis' => [
                'litros' => [
                    'valor' => $litrosTotal,
                    'delta_pct' => $this->calcularDeltaPct($litrosTotal, $acopiosPrevLitros),
                    'entregas' => $acopiosActual->count(),
                ],
                'ventas' => [
                    'valor' => $ventasActual,
                    'delta_pct' => $this->calcularDeltaPct($ventasActual, $ventasPrev),
                    'unidades' => $unidadesActual,
                    'gastos' => $gastosActual,
                ],
                'produccion' => [
                    'moldes' => $moldesActual,
                    'delta_pct' => $this->calcularDeltaPct((float) $moldesActual, (float) $moldesPrev),
                    'tinas_total' => $tinasResumen->count(),
                    'tinas_en_uso' => $tinasResumen->whereNotNull('lote')->count(),
                ],
                'calidad' => [
                    'buena_pct' => $buenaPct,
                    'buena_litros' => $litrosBuena,
                    'acida_litros' => $litrosAcida,
                    'pendientes' => $acopiosActual->where('calidad', 'pendiente')->count(),
                ],
            ],
            'series' => [
                'labels' => $labels,
                'acopio_buena' => $serieAcopioBuena,
                'acopio_acida' => $serieAcopioAcida,
                'ventas' => $serieVentas,
                'gastos' => $serieGastos,
                'moldes' => $serieMoldes,
            ],
            'calidad_distribucion' => [
                'buena' => $litrosBuena,
                'acida' => $litrosAcida,
                'pendiente' => $litrosPendiente,
            ],
            'tinas_resumen' => $tinasResumen,
            'top_productores' => $topProductores,
            'ranking_plantas' => $rankingPlantas,
            'actividad_reciente' => $eventos->sortByDesc('timestamp')->values()->take(7),
        ];
    }

    /**
     * @return array{ranking_plantas: Collection<int, object>, total_ventas_red: float, moldes_por_planta: Collection<int, int|string>}
     */
    private function obtenerRankingPlantas(string $desde, string $hasta, ?int $soloPlantaId = null): array
    {
        $plantas = Planta::query()
            ->when($soloPlantaId !== null, fn ($q) => $q->where('id', $soloPlantaId))
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre', 'ubicacion', 'precio_litro', 'activa']);

        $ventasQuery = Liquidacion::where('tipo', 'venta_producto')
            ->when($soloPlantaId !== null, fn ($q) => $q->where('planta_id', $soloPlantaId));
        $this->aplicarRangoFecha($ventasQuery, $desde, $hasta);
        $ventasPorPlanta = $ventasQuery
            ->groupBy('planta_id')
            ->selectRaw('planta_id, SUM(monto) as total_ventas, SUM(cantidad_unidades) as total_unidades, COUNT(*) as num_ventas')
            ->get()
            ->keyBy('planta_id');

        $acopiosQuery = Acopio::query()
            ->when($soloPlantaId !== null, fn ($q) => $q->where('planta_id', $soloPlantaId));
        $this->aplicarRangoFecha($acopiosQuery, $desde, $hasta);
        $acopiosPorPlanta = $acopiosQuery
            ->groupBy('planta_id')
            ->selectRaw('planta_id, SUM(litros) as total_litros, COUNT(*) as num_entregas')
            ->get()
            ->keyBy('planta_id');

        $moldesQuery = Lote::query()
            ->when($soloPlantaId !== null, fn ($q) => $q->where('planta_id', $soloPlantaId));
        $this->aplicarRangoFecha($moldesQuery, $desde, $hasta);
        $moldesPorPlanta = $moldesQuery
            ->groupBy('planta_id')
            ->selectRaw('planta_id, SUM(moldes_producidos) as total_moldes')
            ->pluck('total_moldes', 'planta_id');

        $totalVentasRed = (float) $ventasPorPlanta->sum('total_ventas');

        $rankingPlantas = $plantas->map(function (Planta $planta) use ($ventasPorPlanta, $acopiosPorPlanta, $moldesPorPlanta, $totalVentasRed): object {
            $v = $ventasPorPlanta->get($planta->id);
            $a = $acopiosPorPlanta->get($planta->id);
            $ventasMonto = round((float) ($v->total_ventas ?? 0), 2);
            $litros = round((float) ($a->total_litros ?? 0), 1);

            return (object) [
                'planta_id' => $planta->id,
                'codigo' => $planta->codigo,
                'nombre' => $planta->nombre,
                'ubicacion' => $planta->ubicacion,
                'activa' => (bool) $planta->activa,
                'ventas_monto' => $ventasMonto,
                'ventas_unidades' => (int) ($v->total_unidades ?? 0),
                'num_ventas' => (int) ($v->num_ventas ?? 0),
                'litros_acopiados' => $litros,
                'num_entregas' => (int) ($a->num_entregas ?? 0),
                'moldes_producidos' => (int) ($moldesPorPlanta[$planta->id] ?? 0),
                'participacion_pct' => $totalVentasRed > 0 ? round(($ventasMonto / $totalVentasRed) * 100, 1) : 0.0,
            ];
        })->sortByDesc(fn (object $p): array => [$p->ventas_monto, $p->litros_acopiados])->values();

        return [
            'ranking_plantas' => $rankingPlantas,
            'total_ventas_red' => round($totalVentasRed, 2),
            'moldes_por_planta' => $moldesPorPlanta,
        ];
    }

    private function calcularTopProductores(Collection $acopios, float $totalLitros): Collection
    {
        return $acopios->groupBy('proveedor_id')->map(function (Collection $grupo) use ($totalLitros): object {
            $prov = $grupo->first()?->proveedor;
            $litros = round((float) $grupo->sum('litros'), 1);

            return (object) [
                'codigo' => $prov?->codigo ?? __('lat.no_code'),
                'nombre' => $prov?->nombre ?? __('lat.producer_default'),
                'sector' => $prov?->sector ?? '',
                'litros' => $litros,
                'entregas' => $grupo->count(),
                'porcentaje' => $totalLitros > 0 ? round(($litros / $totalLitros) * 100, 1) : 0.0,
            ];
        })->sortByDesc('litros')->values()->take(6);
    }

    private function calcularDeltaPct(float $actual, float $anterior): ?float
    {
        if ($anterior <= 0.0) {
            return $actual > 0 ? 100.0 : null;
        }

        return round((($actual - $anterior) / $anterior) * 100, 1);
    }
}
