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
        $sheets = [
            [
                'name' => 'Liquidacion Quincenal',
                'xml' => $this->buildLiquidacionSheet($planta, $desde, $hasta, $liquidaciones),
            ],
            [
                'name' => 'Detalle Acopios',
                'xml' => $this->buildAcopiosSheet($planta, $desde, $hasta, $acopios),
            ],
            [
                'name' => 'Movimientos y Ventas',
                'xml' => $this->buildMovimientosSheet($planta, $desde, $hasta, $movimientos),
            ],
            [
                'name' => 'Comparativa Plantas',
                'xml' => $this->buildPlantasSheet($desde, $hasta, $rankingPlantas),
            ],
        ];

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
            $this->cellText('A1', 'REPORTE DE LIQUIDACIÓN QUINCENAL · '.$planta->nombre, 1),
        ], 26);
        $rows[] = $this->rowXml(2, [
            $this->cellText('A2', "Ubicación: {$planta->ubicacion} | Período: {$desde} al {$hasta} | Comisión LAT: S/ 0.00 | Generado: ".Carbon::now()->format('d/m/Y H:i'), 2),
        ], 18);
        $rows[] = $this->rowXml(3, [], 8);

        $headers = [
            'Código',
            'Ganadero / Productor',
            'Sector / Comunidad',
            'Litros Entregados',
            'Precio / Litro (S/)',
            'Monto Bruto (S/)',
            'Adelantos Descontados (S/)',
            'Comisión LAT (S/)',
            'Neto a Pagar (S/)',
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
                $this->cellNum("F{$r}", (float) $fila->bruto, $sCur),
                $this->cellNum("G{$r}", (float) $fila->adelantos, $sCur),
                $this->cellNum("H{$r}", (float) $fila->comision, $sCur),
                $this->cellNum("I{$r}", (float) $fila->neto, $sCur),
            ], 20);
            $r++;
        }

        $lastDataRow = $r - 1;
        if ($lastDataRow < 5) {
            $rows[] = $this->rowXml($r, [$this->cellText("A{$r}", 'Sin registros en este período.', 4)], 20);
            $autoFilterRef = null;
        } else {
            $rows[] = $this->rowXml($r, [
                $this->cellText("A{$r}", 'TOTALES', 10),
                $this->cellText("B{$r}", ($lastDataRow - 4).' productores', 10),
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

    private function buildAcopiosSheet(Planta $planta, string $desde, string $hasta, Collection $acopios): string
    {
        $rows = [];
        $rows[] = $this->rowXml(1, [$this->cellText('A1', 'DETALLE DE ACOPIOS DIARIOS · '.$planta->nombre, 1)], 26);
        $rows[] = $this->rowXml(2, [$this->cellText('A2', "Período: {$desde} al {$hasta} | Registros de campo (Voz y Manual) y Triage 74°", 2)], 18);
        $rows[] = $this->rowXml(3, [], 8);

        $rows[] = $this->headerRowXml(4, [
            'Fecha', 'Hora', 'Código', 'Productor', 'Sector', 'Litros (L)', 'Método', 'Calidad Sanitaria', 'Destino Operativo',
        ]);

        $r = 5;
        $sumLitros = 0.0;
        foreach ($acopios as $idx => $acopio) {
            $zebra = ($idx % 2 === 1);
            $sTxt = $zebra ? 5 : 4;
            $sNum = $zebra ? 7 : 6;

            $sumLitros += (float) $acopio->litros;
            $calidadTxt = match ($acopio->calidad) {
                'buena' => 'Aprobada (Óptima)',
                'acida' => 'Con acidez (74°)',
                default => 'Pendiente',
            };
            $destinoTxt = match ($acopio->calidad) {
                'acida' => 'Quesillo fresco (4 días)',
                'pendiente' => 'Pendiente de prueba',
                default => 'Tina principal (Queso/Yogur)',
            };

            $rows[] = $this->rowXml($r, [
                $this->cellText("A{$r}", $acopio->fecha?->format('d/m/Y') ?? (string) $acopio->fecha, $sTxt),
                $this->cellText("B{$r}", substr((string) $acopio->hora, 0, 5), $sTxt),
                $this->cellText("C{$r}", (string) ($acopio->proveedor?->codigo ?? '-'), $sTxt),
                $this->cellText("D{$r}", (string) ($acopio->proveedor?->nombre ?? 'Productor'), $sTxt),
                $this->cellText("E{$r}", (string) ($acopio->proveedor?->sector ?? '-'), $sTxt),
                $this->cellNum("F{$r}", (float) $acopio->litros, $sNum),
                $this->cellText("G{$r}", $acopio->metodo === 'voz' ? 'Dictado por voz' : 'Manual / Táctil', $sTxt),
                $this->cellText("H{$r}", $calidadTxt, $sTxt),
                $this->cellText("I{$r}", $destinoTxt, $sTxt),
            ], 20);
            $r++;
        }

        $lastDataRow = $r - 1;
        if ($lastDataRow < 5) {
            $rows[] = $this->rowXml($r, [$this->cellText("A{$r}", 'Sin acopios en este período.', 4)], 20);
            $autoFilterRef = null;
        } else {
            $rows[] = $this->rowXml($r, [
                $this->cellText("A{$r}", 'TOTAL LITROS', 10),
                $this->cellText("B{$r}", '', 10),
                $this->cellText("C{$r}", '', 10),
                $this->cellText("D{$r}", ($lastDataRow - 4).' entregas', 10),
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
        $rows[] = $this->rowXml(1, [$this->cellText('A1', 'MOVIMIENTOS FINANCIEROS, ADELANTOS Y VENTAS · '.$planta->nombre, 1)], 26);
        $rows[] = $this->rowXml(2, [$this->cellText('A2', "Período: {$desde} al {$hasta} | Ley N.° 29733: Ayuda del día sin DNI", 2)], 18);
        $rows[] = $this->rowXml(3, [], 8);

        $rows[] = $this->headerRowXml(4, [
            'Fecha', 'Tipo de Movimiento', 'Productor / Referencia', 'Concepto / Descripción', 'Unidades / Moldes', 'Monto (S/)',
        ]);

        $r = 5;
        $sumIngresos = 0.0;
        $sumEgresos = 0.0;
        $sumUnidadesIngresos = 0;
        $sumUnidadesEgresos = 0;

        $tipoLabels = [
            'adelanto_efectivo' => 'Adelanto en efectivo',
            'adelanto_queso' => 'Adelanto en queso',
            'ayuda_del_dia' => 'Ayuda del día (sin DNI)',
            'gasto_operativo' => 'Gasto operativo',
            'venta_producto' => 'Venta de producto lácteo',
            'quincena' => 'Liquidación quincenal',
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
                $this->cellText("C{$r}", (string) ($mov->proveedor?->nombre ?? 'Planta / Faena externa'), $sTxt),
                $this->cellText("D{$r}", (string) $mov->concepto, $sTxt),
                $this->cellNum("E{$r}", $unidades, $sInt),
                $this->cellNum("F{$r}", $monto, $sCur),
            ], 20);
            $r++;
        }

        $lastDataRow = $r - 1;
        if ($lastDataRow < 5) {
            $rows[] = $this->rowXml($r, [$this->cellText("A{$r}", 'Sin movimientos en este período.', 4)], 20);
            $autoFilterRef = null;
        } else {
            $filaIngresos = $r;
            $filaEgresos = $r + 1;
            $filaNeto = $r + 2;

            $rows[] = $this->rowXml($filaIngresos, [
                $this->cellText("A{$filaIngresos}", 'TOTAL INGRESOS', 10),
                $this->cellText("B{$filaIngresos}", 'Ventas de producto', 10),
                $this->cellText("C{$filaIngresos}", '', 10),
                $this->cellText("D{$filaIngresos}", ($lastDataRow - 4).' movimientos totales', 10),
                $this->cellFormula("E{$filaIngresos}", "SUMIF(B5:B{$lastDataRow},\"Venta de producto lácteo\",E5:E{$lastDataRow})", $sumUnidadesIngresos, 12),
                $this->cellFormula("F{$filaIngresos}", "SUMIF(B5:B{$lastDataRow},\"Venta de producto lácteo\",F5:F{$lastDataRow})", round($sumIngresos, 2), 11),
            ], 22);

            $rows[] = $this->rowXml($filaEgresos, [
                $this->cellText("A{$filaEgresos}", 'TOTAL EGRESOS', 10),
                $this->cellText("B{$filaEgresos}", 'Gastos, ayudas y adelantos', 10),
                $this->cellText("C{$filaEgresos}", '', 10),
                $this->cellText("D{$filaEgresos}", '', 10),
                $this->cellFormula("E{$filaEgresos}", "SUMIF(B5:B{$lastDataRow},\"<>Venta de producto lácteo\",E5:E{$lastDataRow})", $sumUnidadesEgresos, 12),
                $this->cellFormula("F{$filaEgresos}", "SUMIF(B5:B{$lastDataRow},\"<>Venta de producto lácteo\",F5:F{$lastDataRow})", round($sumEgresos, 2), 11),
            ], 22);

            $rows[] = $this->rowXml($filaNeto, [
                $this->cellText("A{$filaNeto}", 'BALANCE NETO', 10),
                $this->cellText("B{$filaNeto}", 'Ingresos menos egresos', 10),
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
        $rows[] = $this->rowXml(1, [$this->cellText('A1', 'ESTADÍSTICAS COMPARATIVAS DE PLANTAS (¿QUIÉN VENDE MÁS?)', 1)], 26);
        $rows[] = $this->rowXml(2, [$this->cellText('A2', "Período evaluado: {$desde} al {$hasta} | Ranking ordenado por ventas totales", 2)], 18);
        $rows[] = $this->rowXml(3, [], 8);

        $rows[] = $this->headerRowXml(4, [
            'Puesto', 'Código', 'Planta Láctea', 'Ubicación', 'Ventas Totales (S/)', 'Participación (%)', 'Unidades Vendidas', 'Litros Acopiados', 'Moldes Producidos',
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
            $rows[] = $this->rowXml($r, [$this->cellText("A{$r}", 'Sin plantas registradas en este período.', 4)], 20);
            $autoFilterRef = null;
        } else {
            $etiquetaTotal = $rankingPlantas->count() === 1 ? 'TOTAL PLANTA' : 'TOTAL RED DE PLANTAS';
            $rows[] = $this->rowXml($r, [
                $this->cellText("A{$r}", 'RED', 10),
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
        return '<c r="'.$ref.'" t="inlineStr" s="'.$style.'"><is><t xml:space="preserve">'.$this->esc($value).'</t></is></c>';
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
                throw new RuntimeException("No se pudo comprimir la entrada '{$name}' del archivo XLSX.");
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
