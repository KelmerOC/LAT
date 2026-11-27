<?php

namespace App\Services;

use App\Models\Planta;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Genera archivos Excel (.xlsx / OOXML) en memoria sin dependencias externas.
 */
class ExcelExportService
{
    public function generarBalanceXlsx(
        Planta $planta,
        string $desde,
        string $hasta,
        Collection $liquidaciones,
        Collection $acopios,
        Collection $movimientos,
        Collection $rankingPlantas
    ): string {
        return $this->buildWorkbookFromSheets([
            [
                'name' => $this->sanitizeSheetName(__('lat.excel_sheet_settlement')),
                'xml' => $this->buildLiquidacionSheet($planta, $desde, $hasta, $liquidaciones),
            ],
            [
                'name' => $this->sanitizeSheetName(__('lat.excel_sheet_intake')),
                'xml' => $this->buildAcopiosSheet($planta, $desde, $hasta, $acopios),
            ],
            [
                'name' => $this->sanitizeSheetName(__('lat.excel_sheet_movements')),
                'xml' => $this->buildMovimientosSheet($planta, $desde, $hasta, $movimientos),
            ],
            [
                'name' => $this->sanitizeSheetName(__('lat.excel_sheet_plants')),
                'xml' => $this->buildPlantasSheet($desde, $hasta, $rankingPlantas),
            ],
        ]);
    }

    /**
     * Genera un libro .xlsx de una sola hoja para Liquidación Quincenal.
     */
    public function generarSoloLiquidacionXlsx(Planta $planta, string $desde, string $hasta, Collection $liquidaciones): string
    {
        return $this->buildWorkbookFromSheets([
            [
                'name' => $this->sanitizeSheetName(__('lat.excel_sheet_settlement')),
                'xml' => $this->buildLiquidacionSheet($planta, $desde, $hasta, $liquidaciones),
            ],
        ]);
    }

    /**
     * Genera un libro .xlsx de una sola hoja para Ventas de productos lácteos.
     */
    public function generarSoloVentasXlsx(Planta $planta, string $desde, string $hasta, Collection $ventas): string
    {
        return $this->buildWorkbookFromSheets([
            [
                'name' => $this->sanitizeSheetName('Ventas'),
                'xml' => $this->buildVentasSheet($planta, $desde, $hasta, $ventas),
            ],
        ]);
    }

    /**
     * Genera un libro .xlsx de una sola hoja para Sueldos y Pagos operativos.
     */
    public function generarSoloSueldosXlsx(Planta $planta, string $desde, string $hasta, Collection $sueldos): string
    {
        return $this->buildWorkbookFromSheets([
            [
                'name' => $this->sanitizeSheetName('Sueldos y Pagos'),
                'xml' => $this->buildSueldosSheet($planta, $desde, $hasta, $sueldos),
            ],
        ]);
    }

    /**
     * Ensambla un paquete OOXML (.xlsx) completo a partir de sus hojas XML.
     *
     * @param  array<int, array{name: string, xml: string}>  $sheets
     */
    private function buildWorkbookFromSheets(array $sheets): string
    {
        $entries = [
            '[Content_Types].xml' => $this->buildContentTypes(count($sheets)),
            '_rels/.rels' => $this->buildRootRels(),
            'xl/workbook.xml' => $this->buildWorkbookXml($sheets),
            'xl/_rels/workbook.xml.rels' => $this->buildWorkbookRels(count($sheets)),
            'xl/styles.xml' => $this->buildStylesXml(),
        ];

        foreach ($sheets as $idx => $sheet) {
            $entries['xl/worksheets/sheet'.($idx + 1).'.xml'] = $sheet['xml'];
        }

        return $this->packZip($entries);
    }

    private function buildLiquidacionSheet(Planta $planta, string $desde, string $hasta, Collection $liquidaciones): string
    {
        $rows = [];
        $rows[] = $this->rowXml(1, [
            $this->cellText('A1', __('lat.excel_s1_title', ['plant' => $planta->nombre]), 1),
        ], 26);
        $rows[] = $this->rowXml(2, [
            $this->cellText('A2', __('lat.excel_s1_subtitle', [
                'location' => $planta->ubicacion,
                'from' => $desde,
                'to' => $hasta,
                'date' => Carbon::now()->format('d/m/Y H:i'),
            ]), 2),
        ], 18);
        $rows[] = $this->rowXml(3, [], 8);

        $headers = [
            __('lat.excel_col_code'),
            __('lat.excel_col_producer'),
            __('lat.excel_col_sector'),
            __('lat.excel_col_liters_delivered'),
            __('lat.excel_col_price_liter'),
            __('lat.excel_col_gross_amount'),
            __('lat.excel_col_advances_deducted'),
            __('lat.excel_col_lat_commission'),
            __('lat.excel_col_net_payable'),
        ];
        $rows[] = $this->headerRowXml(4, $headers);

        $r = 5;
        $sumLitros = 0.0;
        $sumBruto = 0.0;
        $sumAdelantos = 0.0;
        $sumNeto = 0.0;

        foreach ($liquidaciones as $idx => $fila) {
            $zebra = ($idx % 2 === 1);
            $sTxt = $zebra ? 5 : 4;
            $sNum = $zebra ? 7 : 6;
            $sCur = $zebra ? 9 : 8;

            $sumLitros += (float) $fila->litros;
            $sumBruto += (float) $fila->bruto;
            $sumAdelantos += (float) $fila->adelantos;
            $sumNeto += (float) $fila->neto;

            $rows[] = $this->rowXml($r, [
                $this->cellText("A{$r}", (string) $fila->codigo, $sTxt),
                $this->cellText("B{$r}", (string) $fila->nombre, $sTxt),
                $this->cellText("C{$r}", (string) ($fila->sector ?? $planta->ubicacion), $sTxt),
                $this->cellNum("D{$r}", (float) $fila->litros, $sNum),
                $this->cellNum("E{$r}", (float) $fila->precio_litro, $sCur),
                $this->cellFormula("F{$r}", "ROUND(D{$r}*E{$r},2)", (float) $fila->bruto, $sCur),
                $this->cellNum("G{$r}", (float) $fila->adelantos, $sCur),
                $this->cellNum("H{$r}", (float) $fila->comision, $sCur),
                $this->cellFormula("I{$r}", "MAX(0,F{$r}-G{$r}-H{$r})", (float) $fila->neto, $sCur),
            ], 20);
            $r++;
        }

        $lastDataRow = $r - 1;
        if ($lastDataRow < 5) {
            $rows[] = $this->rowXml($r, [$this->cellText("A{$r}", __('lat.excel_s1_empty'), 4)], 20);
            $autoFilterRef = null;
        } else {
            $rows[] = $this->rowXml($r, [
                $this->cellText("A{$r}", __('lat.excel_totals'), 10),
                $this->cellText("B{$r}", __('lat.excel_producers_count', ['count' => $lastDataRow - 4]), 10),
                $this->cellText("C{$r}", '', 10),
                $this->cellFormula("D{$r}", "SUM(D5:D{$lastDataRow})", $sumLitros, 12),
                $this->cellText("E{$r}", '-', 10),
                $this->cellFormula("F{$r}", "SUM(F5:F{$lastDataRow})", $sumBruto, 11),
                $this->cellFormula("G{$r}", "SUM(G5:G{$lastDataRow})", $sumAdelantos, 11),
                $this->cellFormula("H{$r}", "SUM(H5:H{$lastDataRow})", 0.0, 11),
                $this->cellFormula("I{$r}", "SUM(I5:I{$lastDataRow})", $sumNeto, 11),
            ], 22);
            $autoFilterRef = "A4:I{$lastDataRow}";
        }

        return $this->wrapWorksheet($rows, [14, 28, 22, 18, 18, 18, 22, 18, 20], 4, $autoFilterRef);
    }

    private function buildVentasSheet(Planta $planta, string $desde, string $hasta, Collection $ventas): string
    {
        $productos = [
            'queso_andino' => 'Queso andino',
            'quesillo_fresco' => 'Quesillo fresco',
            'yogur_natural' => 'Yogur natural',
            'mantequilla' => 'Mantequilla',
            'otro' => 'Otro producto',
        ];
        $canales = [
            'feria_local' => 'Feria local',
            'tienda_bodega' => 'Tienda / Bodega',
            'cliente_directo' => 'Cliente directo',
            'planta_interno' => 'Planta interno',
            'otro' => 'Otro canal',
        ];

        $filas = $ventas->map(function (object $venta) use ($productos, $canales): array {
            $cantidad = max(0, (int) ($venta->cantidad_unidades ?? 0));
            $monto = round((float) ($venta->monto ?? 0), 2);
            if ($cantidad === 0 && $monto > 0) {
                $cantidad = 1;
            }
            $pUnit = $venta->precio_unitario !== null
                ? round((float) $venta->precio_unitario, 2)
                : ($cantidad > 0 ? round($monto / $cantidad, 2) : 0.0);

            $prodClave = (string) ($venta->producto_categoria ?? '');
            $canalClave = (string) ($venta->canal_referencia ?? '');

            return [
                'fecha' => $venta->fecha?->format('d/m/Y') ?? (string) $venta->fecha,
                'col2' => $productos[$prodClave] ?? ($prodClave !== '' ? ucfirst(str_replace('_', ' ', $prodClave)) : 'Producto lácteo'),
                'col3' => $canales[$canalClave] ?? ($canalClave !== '' ? $canalClave : ($venta->proveedor?->nombre ?? '-')),
                'concepto' => (string) $venta->concepto,
                'cantidad' => $cantidad,
                'tarifa' => $pUnit,
                'total' => $monto,
                'usar_formula' => $cantidad > 0 && $pUnit > 0.0 && abs(round($cantidad * $pUnit, 2) - $monto) < 0.01,
            ];
        })->all();

        return $this->buildTablaConceptoTarifaSheet(
            "REPORTE DE VENTAS · {$planta->nombre}",
            "Ubicación: {$planta->ubicacion} | Período: {$desde} al {$hasta} | Generado: ".Carbon::now()->format('d/m/Y H:i'),
            ['Fecha', 'Producto', 'Canal/Cliente', 'Concepto', 'Cantidad', 'P.Unit.', 'Total'],
            $filas,
            'Sin ventas registradas en este período.',
            [13, 20, 24, 36, 14, 16, 18]
        );
    }

    private function buildSueldosSheet(Planta $planta, string $desde, string $hasta, Collection $sueldos): string
    {
        $categorias = [
            'sueldo_personal' => 'Sueldo de personal',
            'ayuda_del_dia' => __('lat.mov_ayuda_del_dia'),
            'adelanto_efectivo' => __('lat.mov_adelanto_efectivo'),
            'adelanto_queso' => __('lat.mov_adelanto_queso'),
            'gasto_operativo' => __('lat.mov_gasto_operativo'),
        ];

        $filas = $sueldos->map(function (object $mov) use ($categorias): array {
            $cantidad = max(0, (int) ($mov->cantidad_unidades ?? 0));
            $monto = round((float) ($mov->monto ?? 0), 2);
            if ($cantidad === 0 && $monto > 0) {
                $cantidad = 1;
            }
            $tarifa = $mov->precio_unitario !== null
                ? round((float) $mov->precio_unitario, 2)
                : ($cantidad > 0 ? round($monto / $cantidad, 2) : 0.0);

            $beneficiario = trim((string) ($mov->canal_referencia ?? ''));
            if ($beneficiario === '') {
                $beneficiario = (string) ($mov->proveedor?->nombre ?? __('lat.excel_external_labor'));
            }

            return [
                'fecha' => $mov->fecha?->format('d/m/Y') ?? (string) $mov->fecha,
                'col2' => $categorias[$mov->tipo] ?? (string) $mov->tipo,
                'col3' => $beneficiario,
                'concepto' => (string) $mov->concepto,
                'cantidad' => $cantidad,
                'tarifa' => $tarifa,
                'total' => $monto,
                'usar_formula' => $cantidad > 0 && $tarifa > 0.0 && abs(round($cantidad * $tarifa, 2) - $monto) < 0.01,
            ];
        })->all();

        return $this->buildTablaConceptoTarifaSheet(
            "SUELDOS, PAGOS Y ADELANTOS · {$planta->nombre}",
            "Ubicación: {$planta->ubicacion} | Período: {$desde} al {$hasta} | Generado: ".Carbon::now()->format('d/m/Y H:i'),
            ['Fecha', 'Categoría', 'Beneficiario', 'Concepto', 'Días/Cant.', 'Tarifa', 'Total'],
            $filas,
            'Sin sueldos ni pagos registrados en este período.',
            [13, 24, 24, 36, 14, 16, 18]
        );
    }

    /**
     * Construye una hoja tabular de 7 columnas (A..G) con fórmula Total = E*F cuando coincide o valor exacto, y fila SUM.
     *
     * @param  list<string>  $headers
     * @param  array<int, array{fecha: string, col2: string, col3: string, concepto: string, cantidad: int, tarifa: float, total: float, usar_formula?: bool}>  $filas
     * @param  list<int>  $widths
     */
    private function buildTablaConceptoTarifaSheet(
        string $titulo,
        string $subtitulo,
        array $headers,
        array $filas,
        string $emptyLabel,
        array $widths
    ): string {
        $rows = [];
        $rows[] = $this->rowXml(1, [$this->cellText('A1', $titulo, 1)], 26);
        $rows[] = $this->rowXml(2, [$this->cellText('A2', $subtitulo, 2)], 18);
        $rows[] = $this->rowXml(3, [], 8);
        $rows[] = $this->headerRowXml(4, $headers);

        $r = 5;
        $sumCant = 0;
        $sumTotal = 0.0;

        foreach (array_values($filas) as $idx => $fila) {
            $zebra = ($idx % 2 === 1);
            $sTxt = $zebra ? 5 : 4;
            $sInt = $zebra ? 14 : 13;
            $sCur = $zebra ? 9 : 8;

            $sumCant += $fila['cantidad'];
            $sumTotal += $fila['total'];

            $totalCell = ! empty($fila['usar_formula'])
                ? $this->cellFormula("G{$r}", "E{$r}*F{$r}", $fila['total'], $sCur)
                : $this->cellNum("G{$r}", $fila['total'], $sCur);

            $rows[] = $this->rowXml($r, [
                $this->cellText("A{$r}", $fila['fecha'], $sTxt),
                $this->cellText("B{$r}", $fila['col2'], $sTxt),
                $this->cellText("C{$r}", $fila['col3'], $sTxt),
                $this->cellText("D{$r}", $fila['concepto'], $sTxt),
                $this->cellNum("E{$r}", $fila['cantidad'], $sInt),
                $this->cellNum("F{$r}", $fila['tarifa'], $sCur),
                $totalCell,
            ], 20);
            $r++;
        }

        $lastDataRow = $r - 1;
        if ($lastDataRow < 5) {
            $rows[] = $this->rowXml($r, [$this->cellText("A{$r}", $emptyLabel, 4)], 20);
            $autoFilterRef = null;
        } else {
            $rows[] = $this->rowXml($r, [
                $this->cellText("A{$r}", __('lat.excel_totals'), 10),
                $this->cellText("B{$r}", ($lastDataRow - 4).' registros', 10),
                $this->cellText("C{$r}", '', 10),
                $this->cellText("D{$r}", '', 10),
                $this->cellFormula("E{$r}", "SUM(E5:E{$lastDataRow})", $sumCant, 12),
                $this->cellText("F{$r}", '-', 10),
                $this->cellFormula("G{$r}", "SUM(G5:G{$lastDataRow})", round($sumTotal, 2), 11),
            ], 22);
            $autoFilterRef = "A4:G{$lastDataRow}";
        }

        return $this->wrapWorksheet($rows, $widths, 4, $autoFilterRef);
    }

    private function buildAcopiosSheet(Planta $planta, string $desde, string $hasta, Collection $acopios): string
    {
        $rows = [];
        $rows[] = $this->rowXml(1, [$this->cellText('A1', __('lat.excel_s2_title', ['plant' => $planta->nombre]), 1)], 26);
        $rows[] = $this->rowXml(2, [$this->cellText('A2', __('lat.excel_s2_subtitle', ['from' => $desde, 'to' => $hasta]), 2)], 18);
        $rows[] = $this->rowXml(3, [], 8);

        $rows[] = $this->headerRowXml(4, [
            __('lat.excel_col_date'),
            __('lat.excel_col_time'),
            __('lat.excel_col_code'),
            __('lat.excel_col_producer_short'),
            __('lat.excel_col_sector_short'),
            __('lat.excel_col_liters_unit'),
            __('lat.excel_col_method'),
            __('lat.excel_col_sanitary_quality'),
            __('lat.excel_col_operational_dest'),
        ]);

        $r = 5;
        $sumLitros = 0.0;
        foreach ($acopios as $idx => $acopio) {
            $zebra = ($idx % 2 === 1);
            $sTxt = $zebra ? 5 : 4;
            $sNum = $zebra ? 7 : 6;

            $sumLitros += (float) $acopio->litros;
            $calidadTxt = match ($acopio->calidad) {
                'buena' => __('lat.excel_quality_good'),
                'acida' => __('lat.excel_quality_acid'),
                'descartada' => __('lat.excel_quality_discarded'),
                default => __('lat.excel_quality_pending'),
            };
            $destinoTxt = match ($acopio->calidad) {
                'acida' => __('lat.excel_dest_acid'),
                'descartada' => __('lat.excel_dest_discarded').(! empty($acopio->motivo_descarte) ? ' · '.$acopio->motivo_descarte : ''),
                'pendiente' => __('lat.excel_dest_pending'),
                default => __('lat.excel_dest_good'),
            };

            $rows[] = $this->rowXml($r, [
                $this->cellText("A{$r}", $acopio->fecha?->format('d/m/Y') ?? (string) $acopio->fecha, $sTxt),
                $this->cellText("B{$r}", substr((string) $acopio->hora, 0, 5), $sTxt),
                $this->cellText("C{$r}", (string) ($acopio->proveedor?->codigo ?? '-'), $sTxt),
                $this->cellText("D{$r}", (string) ($acopio->proveedor?->nombre ?? __('lat.producer_default')), $sTxt),
                $this->cellText("E{$r}", (string) ($acopio->proveedor?->sector ?? '-'), $sTxt),
                $this->cellNum("F{$r}", (float) $acopio->litros, $sNum),
                $this->cellText("G{$r}", $acopio->metodo === 'voz' ? __('lat.excel_method_voice') : __('lat.excel_method_manual'), $sTxt),
                $this->cellText("H{$r}", $calidadTxt, $sTxt),
                $this->cellText("I{$r}", $destinoTxt, $sTxt),
            ], 20);
            $r++;
        }

        $lastDataRow = $r - 1;
        if ($lastDataRow < 5) {
            $rows[] = $this->rowXml($r, [$this->cellText("A{$r}", __('lat.excel_s2_empty'), 4)], 20);
            $autoFilterRef = null;
        } else {
            $rows[] = $this->rowXml($r, [
                $this->cellText("A{$r}", __('lat.excel_total_liters'), 10),
                $this->cellText("B{$r}", '', 10),
                $this->cellText("C{$r}", '', 10),
                $this->cellText("D{$r}", __('lat.excel_deliveries_count', ['count' => $lastDataRow - 4]), 10),
                $this->cellText("E{$r}", '', 10),
                $this->cellFormula("F{$r}", "SUM(F5:F{$lastDataRow})", $sumLitros, 12),
                $this->cellText("G{$r}", '', 10),
                $this->cellText("H{$r}", '', 10),
                $this->cellText("I{$r}", '', 10),
            ], 22);
            $autoFilterRef = "A4:I{$lastDataRow}";
        }

        return $this->wrapWorksheet($rows, [13, 10, 12, 26, 18, 14, 18, 20, 26], 4, $autoFilterRef);
    }

    private function buildMovimientosSheet(Planta $planta, string $desde, string $hasta, Collection $movimientos): string
    {
        $rows = [];
        $rows[] = $this->rowXml(1, [$this->cellText('A1', __('lat.excel_s3_title', ['plant' => $planta->nombre]), 1)], 26);
        $rows[] = $this->rowXml(2, [$this->cellText('A2', __('lat.excel_s3_subtitle', ['from' => $desde, 'to' => $hasta]), 2)], 18);
        $rows[] = $this->rowXml(3, [], 8);

        $rows[] = $this->headerRowXml(4, [
            __('lat.excel_col_date'),
            __('lat.excel_col_movement_type'),
            __('lat.excel_col_producer_ref'),
            __('lat.excel_col_concept'),
            __('lat.excel_col_units_molds'),
            __('lat.excel_col_amount'),
        ]);

        $r = 5;
        $sumIngresos = 0.0;
        $sumEgresos = 0.0;
        $sumUnidadesIngresos = 0;
        $sumUnidadesEgresos = 0;

        $tipoLabels = [
            'adelanto_efectivo' => __('lat.mov_adelanto_efectivo'),
            'adelanto_queso' => __('lat.mov_adelanto_queso'),
            'ayuda_del_dia' => __('lat.mov_ayuda_del_dia'),
            'sueldo_personal' => __('lat.mov_sueldo_personal'),
            'gasto_operativo' => __('lat.mov_gasto_operativo'),
            'venta_producto' => __('lat.mov_venta_producto'),
            'quincena' => __('lat.mov_quincena'),
        ];

        foreach ($movimientos as $idx => $mov) {
            $zebra = ($idx % 2 === 1);
            $sTxt = $zebra ? 5 : 4;
            $sInt = $zebra ? 14 : 13;
            $sCur = $zebra ? 9 : 8;

            $monto = (float) $mov->monto;
            $unidades = (int) ($mov->cantidad_unidades ?? 0);

            if ($mov->tipo === 'venta_producto') {
                $sumIngresos += $monto;
                $sumUnidadesIngresos += $unidades;
            } else {
                $sumEgresos += $monto;
                $sumUnidadesEgresos += $unidades;
            }

            $rows[] = $this->rowXml($r, [
                $this->cellText("A{$r}", $mov->fecha?->format('d/m/Y') ?? (string) $mov->fecha, $sTxt),
                $this->cellText("B{$r}", $tipoLabels[$mov->tipo] ?? $mov->tipo, $sTxt),
                $this->cellText("C{$r}", (string) ($mov->proveedor?->nombre ?? __('lat.excel_external_labor')), $sTxt),
                $this->cellText("D{$r}", (string) $mov->concepto, $sTxt),
                $this->cellNum("E{$r}", $unidades, $sInt),
                $this->cellNum("F{$r}", $monto, $sCur),
            ], 20);
            $r++;
        }

        $lastDataRow = $r - 1;
        if ($lastDataRow < 5) {
            $rows[] = $this->rowXml($r, [$this->cellText("A{$r}", __('lat.excel_s3_empty'), 4)], 20);
            $autoFilterRef = null;
        } else {
            $ventaFormula = str_replace('"', '""', $tipoLabels['venta_producto']);
            $filaIngresos = $r;
            $filaEgresos = $r + 1;
            $filaNeto = $r + 2;

            $rows[] = $this->rowXml($filaIngresos, [
                $this->cellText("A{$filaIngresos}", __('lat.excel_total_income'), 10),
                $this->cellText("B{$filaIngresos}", __('lat.excel_income_desc'), 10),
                $this->cellText("C{$filaIngresos}", '', 10),
                $this->cellText("D{$filaIngresos}", __('lat.excel_movements_count', ['count' => $lastDataRow - 4]), 10),
                $this->cellFormula("E{$filaIngresos}", "SUMIF(B5:B{$lastDataRow},\"{$ventaFormula}\",E5:E{$lastDataRow})", $sumUnidadesIngresos, 12),
                $this->cellFormula("F{$filaIngresos}", "SUMIF(B5:B{$lastDataRow},\"{$ventaFormula}\",F5:F{$lastDataRow})", round($sumIngresos, 2), 11),
            ], 22);

            $rows[] = $this->rowXml($filaEgresos, [
                $this->cellText("A{$filaEgresos}", __('lat.excel_total_expenses'), 10),
                $this->cellText("B{$filaEgresos}", __('lat.excel_expenses_desc'), 10),
                $this->cellText("C{$filaEgresos}", '', 10),
                $this->cellText("D{$filaEgresos}", '', 10),
                $this->cellFormula("E{$filaEgresos}", "SUMIF(B5:B{$lastDataRow},\"<>{$ventaFormula}\",E5:E{$lastDataRow})", $sumUnidadesEgresos, 12),
                $this->cellFormula("F{$filaEgresos}", "SUMIF(B5:B{$lastDataRow},\"<>{$ventaFormula}\",F5:F{$lastDataRow})", round($sumEgresos, 2), 11),
            ], 22);

            $rows[] = $this->rowXml($filaNeto, [
                $this->cellText("A{$filaNeto}", __('lat.excel_net_balance'), 10),
                $this->cellText("B{$filaNeto}", __('lat.excel_net_balance_desc'), 10),
                $this->cellText("C{$filaNeto}", '', 10),
                $this->cellText("D{$filaNeto}", '', 10),
                $this->cellText("E{$filaNeto}", '-', 10),
                $this->cellFormula("F{$filaNeto}", "F{$filaIngresos}-F{$filaEgresos}", round($sumIngresos - $sumEgresos, 2), 11),
            ], 22);

            $autoFilterRef = "A4:F{$lastDataRow}";
        }

        return $this->wrapWorksheet($rows, [13, 24, 24, 42, 18, 18], 4, $autoFilterRef);
    }

    private function buildPlantasSheet(string $desde, string $hasta, Collection $rankingPlantas): string
    {
        $rows = [];
        $rows[] = $this->rowXml(1, [$this->cellText('A1', __('lat.excel_s4_title'), 1)], 26);
        $rows[] = $this->rowXml(2, [$this->cellText('A2', __('lat.excel_s4_subtitle', ['from' => $desde, 'to' => $hasta]), 2)], 18);
        $rows[] = $this->rowXml(3, [], 8);

        $rows[] = $this->headerRowXml(4, [
            __('lat.excel_col_rank'),
            __('lat.excel_col_code'),
            __('lat.excel_col_dairy_plant'),
            __('lat.excel_col_location'),
            __('lat.excel_col_total_sales'),
            __('lat.excel_col_share_pct'),
            __('lat.excel_col_units_sold'),
            __('lat.excel_col_liters_collected'),
            __('lat.excel_col_molds_produced'),
        ]);

        $r = 5;
        $sumVentas = 0.0;
        $sumLitros = 0.0;
        $sumMoldes = 0;

        foreach ($rankingPlantas->values() as $idx => $p) {
            $zebra = ($idx % 2 === 1);
            $sTxt = $zebra ? 5 : 4;
            $sNum = $zebra ? 7 : 6;
            $sCur = $zebra ? 9 : 8;
            $sInt = $zebra ? 14 : 13;

            $sumVentas += (float) $p->ventas_monto;
            $sumLitros += (float) $p->litros_acopiados;
            $sumMoldes += (int) $p->moldes_producidos;

            $rows[] = $this->rowXml($r, [
                $this->cellNum("A{$r}", $idx + 1, $sInt),
                $this->cellText("B{$r}", (string) $p->codigo, $sTxt),
                $this->cellText("C{$r}", (string) $p->nombre, $sTxt),
                $this->cellText("D{$r}", (string) $p->ubicacion, $sTxt),
                $this->cellNum("E{$r}", (float) $p->ventas_monto, $sCur),
                $this->cellNum("F{$r}", (float) $p->participacion_pct, $sNum),
                $this->cellNum("G{$r}", (int) $p->ventas_unidades, $sInt),
                $this->cellNum("H{$r}", (float) $p->litros_acopiados, $sNum),
                $this->cellNum("I{$r}", (int) $p->moldes_producidos, $sInt),
            ], 20);
            $r++;
        }

        $lastDataRow = $r - 1;
        if ($lastDataRow < 5) {
            $rows[] = $this->rowXml($r, [$this->cellText("A{$r}", __('lat.excel_s4_empty'), 4)], 20);
            $autoFilterRef = null;
        } else {
            $etiquetaTotal = $rankingPlantas->count() === 1 ? __('lat.excel_total_plant') : __('lat.excel_total_network');
            $rows[] = $this->rowXml($r, [
                $this->cellText("A{$r}", __('lat.excel_network_badge'), 10),
                $this->cellText("B{$r}", '', 10),
                $this->cellText("C{$r}", $etiquetaTotal, 10),
                $this->cellText("D{$r}", '', 10),
                $this->cellFormula("E{$r}", "SUM(E5:E{$lastDataRow})", $sumVentas, 11),
                $this->cellNum("F{$r}", $sumVentas > 0 ? 100.0 : 0.0, 12),
                $this->cellFormula("G{$r}", "SUM(G5:G{$lastDataRow})", (int) $rankingPlantas->sum('ventas_unidades'), 12),
                $this->cellFormula("H{$r}", "SUM(H5:H{$lastDataRow})", $sumLitros, 12),
                $this->cellFormula("I{$r}", "SUM(I5:I{$lastDataRow})", $sumMoldes, 12),
            ], 22);
            $autoFilterRef = "A4:I{$lastDataRow}";
        }

        return $this->wrapWorksheet($rows, [10, 12, 30, 24, 20, 16, 18, 18, 18], 4, $autoFilterRef);
    }

    private function sanitizeSheetName(string $name): string
    {
        $clean = str_replace([':', '\\', '/', '?', '*', '[', ']'], '', $name);

        return mb_substr(trim($clean), 0, 31);
    }

    /**
     * @param  array<int, string>  $headers
     */
    private function headerRowXml(int $rowNum, array $headers): string
    {
        $cells = [];
        foreach ($headers as $i => $text) {
            $col = $this->colLetter($i);
            $cells[] = $this->cellText("{$col}{$rowNum}", $text, 3);
        }

        return $this->rowXml($rowNum, $cells, 24);
    }

    /**
     * @param  array<int, string>  $cells
     */
    private function rowXml(int $rowNum, array $cells, int $height = 19): string
    {
        return '<row r="'.$rowNum.'" ht="'.$height.'" customHeight="1">'.implode('', $cells).'</row>';
    }

    private function cellText(string $ref, string $value, int $style): string
    {
        return '<c r="'.$ref.'" t="inlineStr" s="'.$style.'"><is><t xml:space="preserve">'.$this->esc($this->sanitizeFormulaInjection($value)).'</t></is></c>';
    }

    /**
     * Neutraliza prefijos de fórmula (=, +, -, @, tab, cr) contra Formula Injection (OWASP A03).
     */
    private function sanitizeFormulaInjection(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $primer = $value[0];
        if (in_array($primer, ['=', '+', '-', '@', "\t", "\r"], true)) {
            if (is_numeric($value) && $primer === '-') {
                return $value;
            }

            return "'".$value;
        }

        return $value;
    }

    private function cellNum(string $ref, float|int $value, int $style): string
    {
        return '<c r="'.$ref.'" s="'.$style.'"><v>'.$value.'</v></c>';
    }

    private function cellFormula(string $ref, string $formula, float|int $cached, int $style): string
    {
        return '<c r="'.$ref.'" s="'.$style.'"><f>'.$this->esc($formula).'</f><v>'.$cached.'</v></c>';
    }

    /**
     * @param  array<int, string>  $rows
     * @param  array<int, int>  $widths
     */
    private function wrapWorksheet(array $rows, array $widths, int $freezeRow, ?string $autoFilterRef = null): string
    {
        $colsXml = '<cols>';
        foreach ($widths as $i => $w) {
            $idx = $i + 1;
            $colsXml .= '<col min="'.$idx.'" max="'.$idx.'" width="'.$w.'" customWidth="1"/>';
        }
        $colsXml .= '</cols>';

        $autoFilterXml = $autoFilterRef !== null ? '<autoFilter ref="'.$autoFilterRef.'"/>' : '';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView tabSelected="1" workbookViewId="0">'
            .'<pane ySplit="'.$freezeRow.'" topLeftCell="A'.($freezeRow + 1).'" activePane="bottomLeft" state="frozen"/>'
            .'</sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="19"/>'
            .$colsXml
            .'<sheetData>'.implode('', $rows).'</sheetData>'
            .$autoFilterXml
            .'</worksheet>';
    }

    private function colLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(($index % 26) + 65).$letter;
            $index = intdiv($index, 26) - 1;
        }

        return $letter;
    }

    private function esc(string $text): string
    {
        $utf8 = mb_check_encoding($text, 'UTF-8')
            ? $text
            : mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        $clean = preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $utf8) ?? '';

        return htmlspecialchars($clean, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function buildContentTypes(int $sheetCount): string
    {
        $overrides = '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';

        for ($i = 1; $i <= $sheetCount; $i++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet'.$i.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .$overrides
            .'</Types>';
    }

    private function buildRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    /**
     * @param  array<int, array{name: string, xml: string}>  $sheets
     */
    private function buildWorkbookXml(array $sheets): string
    {
        $sheetNodes = '';
        foreach ($sheets as $i => $sheet) {
            $id = $i + 1;
            $sheetNodes .= '<sheet name="'.$this->esc($sheet['name']).'" sheetId="'.$id.'" r:id="rId'.$id.'"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'.$sheetNodes.'</sheets>'
            .'<calcPr fullCalcOnLoad="1"/>'
            .'</workbook>';
    }

    private function buildWorkbookRels(int $sheetCount): string
    {
        $rels = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $rels .= '<Relationship Id="rId'.$i.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$i.'.xml"/>';
        }
        $rels .= '<Relationship Id="rId'.($sheetCount + 1).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$rels
            .'</Relationships>';
    }

    private function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="3">'
            .'<numFmt numFmtId="164" formatCode="#,##0.00"/>'
            .'<numFmt numFmtId="165" formatCode="&quot;S/ &quot;#,##0.00"/>'
            .'<numFmt numFmtId="166" formatCode="#,##0"/>'
            .'</numFmts>'
            .'<fonts count="5">'
            .'<font><sz val="10"/><color rgb="FF0A2F4D"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="14"/><color rgb="FF0A2F4D"/><name val="Calibri"/></font>'
            .'<font><i/><sz val="10"/><color rgb="FF4B6276"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FF0A2F4D"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="5">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF0A2F4D"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF8FBFE"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFE1F0FA"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="3">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom></border>'
            .'<border><left style="thin"><color rgb="FF0A2F4D"/></left><right style="thin"><color rgb="FF0A2F4D"/></right><top style="medium"><color rgb="FF0A2F4D"/></top><bottom style="medium"><color rgb="FF0A2F4D"/></bottom></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="15">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="165" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="4" fillId="4" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="165" fontId="4" fillId="4" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="164" fontId="4" fillId="4" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="166" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="166" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'</cellXfs>'
            .'</styleSheet>';
    }

    /**
     * Empaqueta archivos XML en un contenedor PKZIP estándar sin requerir ext-zip.
     *
     * @param  array<string, string>  $files
     */
    private function packZip(array $files): string
    {
        $now = Carbon::now();
        $dosTime = (($now->hour & 0x1F) << 11)
            | (($now->minute & 0x3F) << 5)
            | (intdiv($now->second, 2) & 0x1F);
        $dosDate = ((max(0, $now->year - 1980) & 0x7F) << 9)
            | (($now->month & 0x0F) << 5)
            | ($now->day & 0x1F);

        $data = '';
        $central = '';
        $offset = 0;
        $count = 0;

        foreach ($files as $name => $content) {
            $nameLen = strlen($name);
            $uncompressed = strlen($content);
            $crc = crc32($content) & 0xFFFFFFFF;
            $compressed = gzdeflate($content, 6);
            if ($compressed === false) {
                throw new RuntimeException(__('lat.excel_zip_error', ['name' => $name]));
            }
            $compLen = strlen($compressed);

            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034B50,
                20,
                0x0800,
                8,
                $dosTime,
                $dosDate,
                $crc,
                $compLen,
                $uncompressed,
                $nameLen,
                0
            );

            $data .= $localHeader.$name.$compressed;

            $central .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014B50,
                20,
                20,
                0x0800,
                8,
                $dosTime,
                $dosDate,
                $crc,
                $compLen,
                $uncompressed,
                $nameLen,
                0,
                0,
                0,
                0,
                0,
                $offset
            ).$name;

            $offset += strlen($localHeader) + $nameLen + $compLen;
            $count++;
        }

        $centralSize = strlen($central);
        $eocd = pack(
            'VvvvvVVv',
            0x06054B50,
            0,
            0,
            $count,
            $count,
            $centralSize,
            $offset,
            0
        );

        return $data.$central.$eocd;
    }
}
