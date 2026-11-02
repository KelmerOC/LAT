<?php

namespace App\Services;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Proveedor;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * ============================================================================
 * CAPA DE SERVICIO: LÓGICA DE NEGOCIO Y CÁLCULOS MATEMÁTICOS LÁCTEOS
 * ============================================================================
 * Aplica el principio de Responsabilidad Única (SRP) y separación en capas.
 * Desacopla la lógica matemática y algoritmos de optimización del controlador.
 */
class LacteoService
{
    /** Parámetros técnicos estandarizados de campo (Anta / Cusco) */
    public const CUAJO_ML_X_LITRO = 0.025;   // 2.5 ml por cada 100 L
    public const SAL_KG_X_LITRO   = 0.018;   // 1.8 kg por cada 100 L
    public const CULTIVO_G_X_LITRO = 0.010;  // 1.0 g por cada 100 L
    public const LITROS_X_MOLDE    = 9.0;    // ~11 moldes por cada 100 L

    public const TEMP_PASTEURIZACION = 72.0;
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
     * CÁLCULO QUINCENAL MASIVO SIN N+1 (OPTIMIZACIÓN DE CONSULTAS)
     * Resuelve el reporte en exactamente 3 consultas SQL independientes.
     */
    public function liquidarQuincena(string $desde, string $hasta, float $precioLitro = 1.65): Collection
    {
        // 1. Obtener ganaderos activos con proyección estricta de columnas
        $proveedores = Proveedor::activos()
            ->select('id', 'codigo', 'nombre', 'sector')
            ->ordenadosPorNombre()
            ->get();

        if ($proveedores->isEmpty()) {
            return collect();
        }

        $ids = $proveedores->pluck('id')->all();

        // 2. Consulta agrupada: Litros totales entregados en el período
        $litrosMap = Acopio::whereIn('proveedor_id', $ids)
            ->delPeriodo($desde, $hasta)
            ->groupBy('proveedor_id')
            ->selectRaw('proveedor_id, SUM(litros) as total_litros')
            ->pluck('total_litros', 'proveedor_id');

        // 3. Consulta agrupada: Adelantos pendientes en efectivo o queso
        $adelantosMap = Liquidacion::whereIn('proveedor_id', $ids)
            ->whereIn('tipo', ['adelanto_efectivo', 'adelanto_queso'])
            ->where('descontado', false)
            ->groupBy('proveedor_id')
            ->selectRaw('proveedor_id, SUM(monto) as total_adelantos')
            ->pluck('total_adelantos', 'proveedor_id');

        // Integración en memoria en orden O(N)
        return $proveedores->map(function ($p) use ($litrosMap, $adelantosMap, $precioLitro) {
            $litros = (float) ($litrosMap[$p->id] ?? 0);
            $bruto = round($litros * $precioLitro, 2);
            $adelanto = (float) ($adelantosMap[$p->id] ?? 0);
            $comision = 0.00; // S/ 0.00 comisión solidaria
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
     * Genera el contenido CSV formal para entidades bancarias y SUNAT.
     */
    public function exportarCsv(Collection $liquidaciones): string
    {
        $salida = "CODIGO,GANADERO,SECTOR,LITROS,PRECIO_LITRO,BRUTO,ADELANTOS,COMISION_LAT,NETO_A_PAGAR\n";
        foreach ($liquidaciones as $fila) {
            $salida .= sprintf(
                "\"%s\",\"%s\",\"%s\",%.2f,%.2f,%.2f,%.2f,%.2f,%.2f\n",
                $fila->codigo,
                $fila->nombre,
                $fila->sector ?? 'Anta',
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
