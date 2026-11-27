<?php

namespace App\Services;

use App\Models\Acopio;
use App\Models\ActividadPlanta;
use App\Models\InformeActividadPlanta;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\Planta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Registra la bitácora operativa por planta y genera informes consolidados cada 24h o bajo demanda.
 */
class InformePlantaService
{
    public function __construct(
        protected TinaWorkflowService $tinaWorkflow,
        protected LacteoService $lacteo
    ) {}

    /**
     * Registra una acción relevante en la bitácora de planta sin interrumpir el flujo si la tabla aún no existe.
     *
     * @param  array<string, mixed>  $meta
     */
    public function registrarActividad(
        int $plantaId,
        ?int $userId,
        string $modulo,
        string $accion,
        string $detalle,
        array $meta = []
    ): void {
        try {
            ActividadPlanta::create([
                'planta_id' => $plantaId,
                'user_id' => $userId,
                'modulo' => mb_substr($modulo, 0, 30),
                'accion' => mb_substr($accion, 0, 60),
                'detalle' => mb_substr($detalle, 0, 320),
                'meta' => $meta !== [] ? $meta : null,
                'created_at' => Carbon::now(),
            ]);
        } catch (Throwable) {
            return;
        }
    }

    /**
     * Compila el informe de actividad y métricas operativas de una planta y notifica a Administración.
     */
    public function generarInformePlanta(
        Planta $planta,
        Carbon $desde,
        Carbon $hasta,
        string $origen = 'automatico_24h',
        ?User $emisor = null
    ): InformeActividadPlanta {
        $actividades = ActividadPlanta::with('usuario:id,name,usuario,cargo')
            ->dePlanta($planta->id)
            ->where('created_at', '>=', $desde)
            ->where('created_at', '<=', $hasta)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $fechaDesde = $desde->toDateString();
        $fechaHasta = $hasta->toDateString();

        $acopioQuery = Acopio::dePlanta($planta->id);
        $this->lacteo->aplicarRangoFecha($acopioQuery, $fechaDesde, $fechaHasta);
        $acopioResumen = $acopioQuery
            ->selectRaw('COUNT(*) as entregas, COALESCE(SUM(litros), 0) as total_litros')
            ->selectRaw("COALESCE(SUM(CASE WHEN calidad = 'buena' THEN litros ELSE 0 END), 0) as litros_buena")
            ->selectRaw("COALESCE(SUM(CASE WHEN calidad = 'acida' THEN litros ELSE 0 END), 0) as litros_acida")
            ->first();

        $lotesQuery = Lote::dePlanta($planta->id);
        $this->lacteo->aplicarRangoFecha($lotesQuery, $fechaDesde, $fechaHasta);
        $lotesResumen = $lotesQuery
            ->selectRaw('COUNT(*) as total_lotes, COALESCE(SUM(moldes_producidos), 0) as total_moldes')
            ->first();

        $movsQuery = Liquidacion::dePlanta($planta->id);
        $this->lacteo->aplicarRangoFecha($movsQuery, $fechaDesde, $fechaHasta);
        $movsResumen = $movsQuery
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'venta_producto' THEN monto ELSE 0 END), 0) as ventas_monto")
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'venta_producto' THEN cantidad_unidades ELSE 0 END), 0) as ventas_unidades")
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo IN ('sueldo_personal', 'ayuda_del_dia', 'gasto_operativo') THEN monto ELSE 0 END), 0) as gastos_sueldos_monto")
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo IN ('adelanto_efectivo', 'adelanto_queso') THEN monto ELSE 0 END), 0) as adelantos_monto")
            ->first();

        $entregas = (int) ($acopioResumen->entregas ?? 0);
        $litrosTotal = round((float) ($acopioResumen->total_litros ?? 0), 1);
        $litrosBuena = round((float) ($acopioResumen->litros_buena ?? 0), 1);
        $litrosAcida = round((float) ($acopioResumen->litros_acida ?? 0), 1);
        $totalLotes = (int) ($lotesResumen->total_lotes ?? 0);
        $totalMoldes = (int) ($lotesResumen->total_moldes ?? 0);
        $ventasMonto = round((float) ($movsResumen->ventas_monto ?? 0), 2);
        $ventasUnidades = (int) ($movsResumen->ventas_unidades ?? 0);
        $gastosSueldosMonto = round((float) ($movsResumen->gastos_sueldos_monto ?? 0), 2);
        $adelantosMonto = round((float) ($movsResumen->adelantos_monto ?? 0), 2);

        $metricas = [
            'entregas' => $entregas,
            'litros_total' => $litrosTotal,
            'litros_buena' => $litrosBuena,
            'litros_acida' => $litrosAcida,
            'lotes' => $totalLotes,
            'moldes_producidos' => $totalMoldes,
            'ventas_monto' => $ventasMonto,
            'ventas_unidades' => $ventasUnidades,
            'gastos_sueldos_monto' => $gastosSueldosMonto,
            'adelantos_monto' => $adelantosMonto,
            'total_acciones' => $actividades->count(),
        ];

        $emisorLabel = $emisor !== null
            ? "{$emisor->name} (@{$emisor->usuario})"
            : 'Sistema automático (cada 24h)';

        $lineas = [
            '========================================================================',
            "INFORME DE ACTIVIDAD DE PLANTA · {$planta->codigo} - {$planta->nombre}",
            '========================================================================',
            "Planta:           [{$planta->codigo}] {$planta->nombre}",
            "Ubicación:        {$planta->ubicacion}",
            "Período:          {$desde->format('Y-m-d H:i')} al {$hasta->format('Y-m-d H:i')}",
            "Origen:           {$origen}",
            "Emisor:           {$emisorLabel}",
            'Generado en:      '.Carbon::now()->format('Y-m-d H:i:s'),
            '',
            '--- RESUMEN OPERATIVO Y FINANCIERO ---',
            sprintf('Acopio:           %d entregas | %.1f L totales (Buena: %.1f L | Ácida: %.1f L)', $entregas, $litrosTotal, $litrosBuena, $litrosAcida),
            sprintf('Tinas y Lotes:    %d lotes | %d moldes producidos', $totalLotes, $totalMoldes),
            sprintf('Ventas:           S/ %.2f (%d unidades)', $ventasMonto, $ventasUnidades),
            sprintf('Sueldos/Gastos:   S/ %.2f | Adelantos: S/ %.2f', $gastosSueldosMonto, $adelantosMonto),
            sprintf('Acciones bitác.:  %d eventos registrados', $actividades->count()),
            '',
            '--- BITÁCORA CRONOLÓGICA DE ACTIVIDADES ---',
        ];

        if ($actividades->isEmpty()) {
            $lineas[] = '(Sin acciones operativas registradas en la bitácora durante este período.)';
        } else {
            foreach ($actividades as $act) {
                $fechaTxt = $act->created_at?->format('Y-m-d H:i') ?? $desde->format('Y-m-d H:i');
                $moduloTxt = strtoupper((string) $act->modulo);
                $usuarioTxt = $act->usuario !== null
                    ? "{$act->usuario->name} ({$act->usuario->usuario})"
                    : 'Sistema';
                $lineas[] = "[{$fechaTxt}] [{$moduloTxt}] {$usuarioTxt}: {$act->detalle}";
            }
        }

        $resumenTitulo = mb_substr(
            "{$planta->codigo} · {$planta->nombre} ({$desde->format('d/m H:i')} - {$hasta->format('d/m H:i')})",
            0,
            160
        );

        $informe = InformeActividadPlanta::create([
            'planta_id' => $planta->id,
            'generado_por' => $emisor?->id,
            'origen' => mb_substr($origen, 0, 30),
            'periodo_inicio' => $desde,
            'periodo_fin' => $hasta,
            'resumen_titulo' => $resumenTitulo,
            'contenido_texto' => implode("\n", $lineas),
            'total_acciones' => $actividades->count(),
            'metricas' => $metricas,
        ]);

        $this->tinaWorkflow->notificar(
            plantaId: $planta->id,
            tipo: 'informe_planta',
            modulo: 'estadisticas',
            titulo: "Informe de planta · {$planta->codigo}",
            mensaje: "{$planta->nombre}: {$actividades->count()} acciones, ".number_format($litrosTotal, 1).' L, ventas S/ '.number_format($ventasMonto, 2),
            datos: [
                'informe_id' => $informe->id,
                'planta_codigo' => $planta->codigo,
                'origen' => $origen,
            ],
            creadoPor: $emisor?->id,
            destinatarioId: null,
            rolesDestino: ['administracion'],
            colorTema: 'info',
            alcanceGlobal: true
        );

        return $informe;
    }

    /**
     * Genera informes automáticos de 24h para plantas activas sin reporte reciente (con throttle de 10 min en web).
     */
    public function verificarYGenerarInformes24h(bool $forzar = false): int
    {
        if (! $forzar) {
            $ultimoCheck = Cache::get('lat_informes_24h_checked_at');
            if ($ultimoCheck !== null && Carbon::parse($ultimoCheck)->gt(Carbon::now()->subMinutes(10))) {
                return 0;
            }
        }

        Cache::put('lat_informes_24h_checked_at', Carbon::now()->toIso8601String(), 600);

        $plantas = Planta::activas()->orderBy('id')->get();
        if ($plantas->isEmpty()) {
            return 0;
        }

        $ultimosPorPlanta = InformeActividadPlanta::query()
            ->where('origen', 'automatico_24h')
            ->whereIn('planta_id', $plantas->pluck('id')->all())
            ->groupBy('planta_id')
            ->selectRaw('planta_id, MAX(created_at) as ultimo_created_at')
            ->pluck('ultimo_created_at', 'planta_id');

        $ahora = Carbon::now();
        $limite24h = $ahora->copy()->subHours(24);
        $generados = 0;

        foreach ($plantas as $planta) {
            $ultimoRaw = $ultimosPorPlanta->get($planta->id);
            $ultimo = $ultimoRaw !== null ? Carbon::parse((string) $ultimoRaw) : null;

            if ($ultimo !== null && $ultimo->gt($limite24h)) {
                continue;
            }

            $desde = $ultimo !== null ? $ultimo->copy() : $ahora->copy()->subHours(24);
            $this->generarInformePlanta($planta, $desde, $ahora, 'automatico_24h');
            $generados++;
        }

        return $generados;
    }

    /**
     * Consulta el historial de informes generados con filtros opcionales para la vista de Administración.
     *
     * @param  array{planta_id?: int|string|null, fecha_desde?: string|null, fecha_hasta?: string|null, origen?: string|null}  $filtros
     * @return Collection<int, InformeActividadPlanta>
     */
    public function consultarHistorial(array $filtros = [], int $limit = 50): Collection
    {
        return InformeActividadPlanta::query()
            ->with(['planta:id,codigo,nombre', 'emisor:id,name,usuario'])
            ->when(! empty($filtros['planta_id']), fn ($q) => $q->where('planta_id', (int) $filtros['planta_id']))
            ->when(! empty($filtros['origen']), fn ($q) => $q->where('origen', (string) $filtros['origen']))
            ->when(! empty($filtros['fecha_desde']), fn ($q) => $q->where('created_at', '>=', Carbon::parse((string) $filtros['fecha_desde'])->startOfDay()))
            ->when(! empty($filtros['fecha_hasta']), fn ($q) => $q->where('created_at', '<=', Carbon::parse((string) $filtros['fecha_hasta'])->endOfDay()))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(max(1, min(200, $limit)))
            ->get();
    }
}
