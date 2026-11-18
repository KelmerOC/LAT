<?php

namespace App\Services;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\Planta;
use App\Models\Proveedor;
use Illuminate\Support\Collection;

/**
 * ============================================================================
 * CAPA DE SERVICIO: LÓGICA DE NEGOCIO, CÁLCULOS LÁCTEOS Y ESTADÍSTICAS
 * ============================================================================
 * Aplica el principio de Responsabilidad Única (SRP) y separación en capas.
 * Desacopla cálculos térmicos, recetas, liquidación sin N+1 y rankings.
 */
class LacteoService
{
    /** Parámetros técnicos estandarizados de campo (Anta / Cusco) */
    public const CUAJO_ML_X_LITRO  = 0.025;  // 2.5 ml por cada 100 L
    public const SAL_KG_X_LITRO    = 0.018;  // 1.8 kg por cada 100 L
    public const CULTIVO_G_X_LITRO = 0.010;  // 1.0 g por cada 100 L
    public const LITROS_X_MOLDE    = 9.0;    // ~11 moldes por cada 100 L

    public const TEMP_PASTEURIZACION  = 72.0;
    public const TEMP_INOCULACION_MIN = 36.0;
    public const TEMP_INOCULACION_MAX = 40.0;

    /**
     * Calcula la dosificación estequiométrica de insumos según litros de leche.
     */
    public function dosificarReceta(float $litros): array
    {
        $litros = max(0, $litros);

        return [
            'litros'           => round($litros, 1),
            'cuajo_ml'         => round($litros * self::CUAJO_ML_X_LITRO, 2),
            'sal_kg'           => round($litros * self::SAL_KG_X_LITRO, 2),
            'cultivo_g'        => round($litros * self::CULTIVO_G_X_LITRO, 2),
            'moldes_estimados' => (int) floor($litros / self::LITROS_X_MOLDE),
        ];
    }

    /**
     * Monitoreo térmico: Curva de enfriamiento desde 72 °C hasta 36-40 °C.
     */
    public function evaluarTermica(float $temperatura): array
    {
        $rangoTotal = self::TEMP_PASTEURIZACION - self::TEMP_INOCULACION_MAX;
        $enfriado = self::TEMP_PASTEURIZACION - $temperatura;
        $porcentaje = $rangoTotal > 0 ? ($enfriado / $rangoTotal) * 100 : 100;
        $porcentaje = max(0, min(100, round($porcentaje)));

        $estado = match (true) {
            $temperatura >= (self::TEMP_PASTEURIZACION - 0.5) => 'pasteurizando',
            $temperatura > self::TEMP_INOCULACION_MAX         => 'enfriando',
            $temperatura >= self::TEMP_INOCULACION_MIN        => 'lista_para_cuajo',
            default                                           => 'muy_fria',
        };

        return [
            'temperatura' => round($temperatura, 1),
            'porcentaje'  => (int) $porcentaje,
            'estado'      => $estado,
            'apta'        => ($estado === 'lista_para_cuajo'),
        ];
    }

    /**
     * CÁLCULO QUINCENAL MASIVO SIN N+1 (AISLADO POR PLANTA)
     * Resuelve el reporte en exactamente 3 consultas SQL independientes.
     */
    public function liquidarQuincena(string $desde, string $hasta, float $precioLitro = 1.65, ?int $plantaId = null): Collection
    {
        $proveedoresQuery = Proveedor::activos()
            ->select('id', 'planta_id', 'codigo', 'nombre', 'sector')
            ->ordenadosPorNombre();

        if ($plantaId !== null) {
            $proveedoresQuery->where('planta_id', $plantaId);
        }

        $proveedores = $proveedoresQuery->get();

        if ($proveedores->isEmpty()) {
            return collect();
        }

        $ids = $proveedores->pluck('id')->all();

        // Consulta agrupada: Litros totales entregados en el período
        $litrosMap = Acopio::whereIn('proveedor_id', $ids)
            ->delPeriodo($desde, $hasta)
            ->when($plantaId !== null, fn ($q) => $q->where('planta_id', $plantaId))
            ->groupBy('proveedor_id')
            ->selectRaw('proveedor_id, SUM(litros) as total_litros')
            ->pluck('total_litros', 'proveedor_id');

        // Consulta agrupada: Adelantos pendientes en efectivo o queso
        $adelantosMap = Liquidacion::whereIn('proveedor_id', $ids)
            ->whereIn('tipo', ['adelanto_efectivo', 'adelanto_queso'])
            ->where('descontado', false)
            ->when($plantaId !== null, fn ($q) => $q->where('planta_id', $plantaId))
            ->groupBy('proveedor_id')
            ->selectRaw('proveedor_id, SUM(monto) as total_adelantos')
            ->pluck('total_adelantos', 'proveedor_id');

        return $proveedores->map(function ($p) use ($litrosMap, $adelantosMap, $precioLitro) {
            $litros = (float) ($litrosMap[$p->id] ?? 0);
            $bruto = round($litros * $precioLitro, 2);
            $adelanto = (float) ($adelantosMap[$p->id] ?? 0);
            $comision = 0.00;
            $neto = max(0, round($bruto - $adelanto, 2));

            return (object) [
                'proveedor_id' => $p->id,
                'codigo'       => $p->codigo,
                'nombre'       => $p->nombre,
                'sector'       => $p->sector,
                'litros'       => $litros,
                'precio_litro' => $precioLitro,
                'bruto'        => $bruto,
                'adelantos'    => $adelanto,
                'comision'     => $comision,
                'neto'         => $neto,
            ];
        });
    }

    /**
     * ESTADÍSTICAS COMPARATIVAS DE PLANTAS ("¿QUIÉN VENDE MÁS?") Y MÉTRICAS DE PLANTA
     */
    public function obtenerEstadisticas(int $plantaActivaId, string $desde, string $hasta): array
    {
        $plantas = Planta::orderBy('nombre')->get(['id', 'codigo', 'nombre', 'ubicacion', 'precio_litro', 'activa']);

        // Agregados por planta en el período en el rango de fechas (compatible MySQL y SQLite)
        $ventasPorPlanta = Liquidacion::where('tipo', 'venta_producto')
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta)
            ->groupBy('planta_id')
            ->selectRaw('planta_id, SUM(monto) as total_ventas, SUM(cantidad_unidades) as total_unidades, COUNT(*) as num_ventas')
            ->get()
            ->keyBy('planta_id');

        $acopiosPorPlanta = Acopio::whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta)
            ->groupBy('planta_id')
            ->selectRaw('planta_id, SUM(litros) as total_litros, COUNT(*) as num_entregas')
            ->get()
            ->keyBy('planta_id');

        $moldesPorPlanta = Lote::whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta)
            ->groupBy('planta_id')
            ->selectRaw('planta_id, SUM(moldes_producidos) as total_moldes')
            ->pluck('total_moldes', 'planta_id');

        $totalVentasRed = (float) $ventasPorPlanta->sum('total_ventas');

        $rankingPlantas = $plantas->map(function ($planta) use ($ventasPorPlanta, $acopiosPorPlanta, $moldesPorPlanta, $totalVentasRed) {
            $v = $ventasPorPlanta->get($planta->id);
            $a = $acopiosPorPlanta->get($planta->id);
            $ventasMonto = round((float) ($v->total_ventas ?? 0), 2);
            $litros = round((float) ($a->total_litros ?? 0), 1);

            return (object) [
                'planta_id'        => $planta->id,
                'codigo'           => $planta->codigo,
                'nombre'           => $planta->nombre,
                'ubicacion'        => $planta->ubicacion,
                'activa'           => (bool) $planta->activa,
                'ventas_monto'     => $ventasMonto,
                'ventas_unidades'  => (int) ($v->total_unidades ?? 0),
                'num_ventas'       => (int) ($v->num_ventas ?? 0),
                'litros_acopiados' => $litros,
                'num_entregas'     => (int) ($a->num_entregas ?? 0),
                'moldes_producidos'=> (int) ($moldesPorPlanta[$planta->id] ?? 0),
                'participacion_pct'=> $totalVentasRed > 0 ? round(($ventasMonto / $totalVentasRed) * 100, 1) : 0.0,
            ];
        })->sortByDesc(fn ($p) => [$p->ventas_monto, $p->litros_acopiados])->values();

        // Estadísticas detalladas de la planta activa
        $acopiosPlanta = Acopio::with('proveedor:id,codigo,nombre,sector')
            ->where('planta_id', $plantaActivaId)
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta)
            ->get();

        $totalLitrosPlanta = (float) $acopiosPlanta->sum('litros');
        $litrosBuena = (float) $acopiosPlanta->where('calidad', 'buena')->sum('litros');
        $litrosAcida = (float) $acopiosPlanta->where('calidad', 'acida')->sum('litros');
        $litrosPendiente = (float) $acopiosPlanta->where('calidad', 'pendiente')->sum('litros');

        $vozCount = $acopiosPlanta->where('metodo', 'voz')->count();
        $manualCount = $acopiosPlanta->where('metodo', 'manual')->count();
        $totalEntregas = max(1, $acopiosPlanta->count());

        $topProductores = $acopiosPlanta->groupBy('proveedor_id')->map(function ($grupo) use ($totalLitrosPlanta) {
            $prov = $grupo->first()->proveedor;
            $litros = round((float) $grupo->sum('litros'), 1);

            return (object) [
                'codigo'     => $prov?->codigo ?? 'S/C',
                'nombre'     => $prov?->nombre ?? 'Productor',
                'sector'     => $prov?->sector ?? '',
                'litros'     => $litros,
                'entregas'   => $grupo->count(),
                'porcentaje' => $totalLitrosPlanta > 0 ? round(($litros / $totalLitrosPlanta) * 100, 1) : 0.0,
            ];
        })->sortByDesc('litros')->values()->take(6);

        $movsPlanta = Liquidacion::where('planta_id', $plantaActivaId)
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta)
            ->get();

        return [
            'ranking_plantas'   => $rankingPlantas,
            'planta_lider'      => $rankingPlantas->first(),
            'total_ventas_red'  => round($totalVentasRed, 2),
            'top_productores'   => $topProductores,
            'calidad'           => [
                'buena_litros'     => round($litrosBuena, 1),
                'acida_litros'     => round($litrosAcida, 1),
                'pendiente_litros' => round($litrosPendiente, 1),
                'buena_pct'        => $totalLitrosPlanta > 0 ? round(($litrosBuena / $totalLitrosPlanta) * 100, 1) : 0.0,
                'acida_pct'        => $totalLitrosPlanta > 0 ? round(($litrosAcida / $totalLitrosPlanta) * 100, 1) : 0.0,
            ],
            'metodos'           => [
                'voz_count'    => $vozCount,
                'manual_count' => $manualCount,
                'voz_pct'      => round(($vozCount / $totalEntregas) * 100, 1),
            ],
            'totales_planta'    => [
                'litros_periodo'    => round($totalLitrosPlanta, 1),
                'ventas_monto'      => round((float) $movsPlanta->where('tipo', 'venta_producto')->sum('monto'), 2),
                'ventas_unidades'   => (int) $movsPlanta->where('tipo', 'venta_producto')->sum('cantidad_unidades'),
                'gastos_monto'      => round((float) $movsPlanta->whereIn('tipo', ['ayuda_del_dia', 'gasto_operativo'])->sum('monto'), 2),
                'adelantos_monto'   => round((float) $movsPlanta->whereIn('tipo', ['adelanto_efectivo', 'adelanto_queso'])->sum('monto'), 2),
                'moldes_producidos' => (int) ($moldesPorPlanta[$plantaActivaId] ?? 0),
            ],
        ];
    }

    /**
     * Mantiene método CSV legado por compatibilidad si alguna prueba o ruta lo invoca.
     */
    public function exportarCsv(Collection $liquidaciones): string
    {
        $salida = "CODIGO,GANADERO,SECTOR,LITROS,PRECIO_LITRO,BRUTO,ADELANTOS,COMISION_LAT,NETO_A_PAGAR\n";
        foreach ($liquidaciones as $fila) {
            $salida .= sprintf(
                "\"%s\",\"%s\",\"%s\",%.2f,%.2f,%.2f,%.2f,%.2f,%.2f\n",
                $fila->codigo,
                $fila->nombre,
                $fila->sector ?? 'Cusco Provincia',
                $fila->litros,
                $fila->precio_litro,
                $fila->bruto,
                $fila->adelantos,
                $fila->comision,
                $fila->neto
            );
        }

        return $salida;
    }
}
