<?php

namespace Tests\Unit;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Services\ExcelExportService;
use App\Services\LacteoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class LacteoServiceTest extends TestCase
{
    use RefreshDatabase;

    private LacteoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LacteoService;
    }

    public function test_dosificar_receta_calcula_insumos_y_moldes_para_100_y_250_litros(): void
    {
        $receta100 = $this->service->dosificarReceta(100);
        $this->assertSame([
            'litros' => 100.0,
            'cuajo_ml' => 2.5,
            'sal_kg' => 1.8,
            'cultivo_g' => 1.0,
            'moldes_estimados' => 11,
        ], $receta100);

        $receta250 = $this->service->dosificarReceta(250);
        $this->assertSame([
            'litros' => 250.0,
            'cuajo_ml' => 6.25,
            'sal_kg' => 4.5,
            'cultivo_g' => 2.5,
            'moldes_estimados' => 27,
        ], $receta250);
    }

    public function test_evaluar_termica_cubre_pasteurizando_enfriando_lista_y_muy_fria(): void
    {
        $t72 = $this->service->evaluarTermica(72.0);
        $this->assertSame('pasteurizando', $t72['estado']);
        $this->assertSame(0, $t72['porcentaje']);
        $this->assertFalse($t72['apta']);

        $t55 = $this->service->evaluarTermica(55.0);
        $this->assertSame('enfriando', $t55['estado']);
        $this->assertSame(53, $t55['porcentaje']);
        $this->assertFalse($t55['apta']);

        $t38 = $this->service->evaluarTermica(38.0);
        $this->assertSame('lista_para_cuajo', $t38['estado']);
        $this->assertSame(100, $t38['porcentaje']);
        $this->assertTrue($t38['apta']);

        $t32 = $this->service->evaluarTermica(32.0);
        $this->assertSame('muy_fria', $t32['estado']);
        $this->assertSame(0, $t32['porcentaje']);
        $this->assertFalse($t32['apta']);
    }

    public function test_liquidar_quincena_usa_precio_historico_incluye_inactivos_con_entregas_y_filtra_adelantos_futuros(): void
    {
        $planta = Planta::create([
            'codigo' => 'PLT-UNIT',
            'nombre' => 'Planta Unit Test',
            'ubicacion' => 'Anta',
            'precio_litro' => 2.00,
            'activa' => true,
        ]);

        $provActivo = Proveedor::create([
            'planta_id' => $planta->id,
            'codigo' => 'U-01',
            'nombre' => 'Productor Histórico',
            'sector' => 'Markjo',
            'activo' => true,
        ]);

        $provInactivoConEntrega = Proveedor::create([
            'planta_id' => $planta->id,
            'codigo' => 'U-02',
            'nombre' => 'Productor Retirado Con Entrega',
            'sector' => 'Compone',
            'activo' => false,
        ]);

        Proveedor::create([
            'planta_id' => $planta->id,
            'codigo' => 'U-03',
            'nombre' => 'Productor Retirado Sin Movimiento',
            'sector' => 'Compone',
            'activo' => false,
        ]);

        // Acopio con precio histórico 1.50 distinto al precio actual de planta (2.00)
        Acopio::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $provActivo->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => '2026-11-18',
            'hora' => '06:30:00',
            'litros' => 40.0,
            'precio_litro' => 1.50,
            'metodo' => 'manual',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
        ]);

        // Acopio de proveedor desactivado dentro del período
        Acopio::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $provInactivoConEntrega->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => '2026-11-19',
            'hora' => '07:00:00',
            'litros' => 10.0,
            'precio_litro' => 1.80,
            'metodo' => 'voz',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
        ]);

        // Adelanto dentro del período (debe descontarse)
        Liquidacion::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $provActivo->id,
            'fecha' => '2026-11-22',
            'tipo' => 'adelanto_efectivo',
            'concepto' => 'Adelanto quincena actual',
            'litros' => 0,
            'monto' => 20.00,
            'comision' => 0.00,
            'descontado' => false,
        ]);

        // Adelanto posterior a $hasta (NO debe descontarse)
        Liquidacion::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $provActivo->id,
            'fecha' => '2026-12-02',
            'tipo' => 'adelanto_efectivo',
            'concepto' => 'Adelanto quincena siguiente',
            'litros' => 0,
            'monto' => 50.00,
            'comision' => 0.00,
            'descontado' => false,
        ]);

        $liquidaciones = $this->service->liquidarQuincena('2026-11-16', '2026-11-30', (float) $planta->precio_litro, $planta->id);

        $this->assertCount(2, $liquidaciones);
        $this->assertNull($liquidaciones->firstWhere('codigo', 'U-03'));

        $filaActivo = $liquidaciones->firstWhere('codigo', 'U-01');
        $this->assertNotNull($filaActivo);
        $this->assertSame(40.0, $filaActivo->litros);
        $this->assertSame(1.50, $filaActivo->precio_litro);
        $this->assertSame(60.00, $filaActivo->bruto);
        $this->assertSame(20.00, $filaActivo->adelantos);
        $this->assertSame(40.00, $filaActivo->neto);

        $filaInactivo = $liquidaciones->firstWhere('codigo', 'U-02');
        $this->assertNotNull($filaInactivo);
        $this->assertSame(10.0, $filaInactivo->litros);
        $this->assertSame(1.80, $filaInactivo->precio_litro);
        $this->assertSame(18.00, $filaInactivo->bruto);
        $this->assertSame(18.00, $filaInactivo->neto);
    }

    public function test_excel_export_service_genera_ooxml_con_calc_pr_autofilter_pendiente_e_ingresos_egresos(): void
    {
        $planta = Planta::create([
            'codigo' => 'PLT-XLS',
            'nombre' => 'Quesería Export Test',
            'ubicacion' => 'Cusco',
            'precio_litro' => 1.65,
            'activa' => true,
        ]);

        $prov = Proveedor::create([
            'planta_id' => $planta->id,
            'codigo' => 'X-01',
            'nombre' => 'Ganadero Prueba',
            'sector' => 'Chinchero',
            'activo' => true,
        ]);

        $acopioPendiente = Acopio::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $prov->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => '2026-11-20',
            'hora' => '06:45:00',
            'litros' => 30.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'pendiente',
            'destino' => 'pendiente',
        ]);

        $venta = Liquidacion::create([
            'planta_id' => $planta->id,
            'proveedor_id' => null,
            'fecha' => '2026-11-20',
            'tipo' => 'venta_producto',
            'concepto' => 'Venta de quesos',
            'litros' => 0,
            'cantidad_unidades' => 15,
            'monto' => 300.00,
            'comision' => 0.00,
            'descontado' => true,
        ]);

        $gasto = Liquidacion::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $prov->id,
            'fecha' => '2026-11-20',
            'tipo' => 'adelanto_efectivo',
            'concepto' => 'Adelanto en mano',
            'litros' => 0,
            'cantidad_unidades' => 0,
            'monto' => 45.00,
            'comision' => 0.00,
            'descontado' => false,
        ]);

        $desde = '2026-11-16';
        $hasta = '2026-11-30';
        $liquidaciones = $this->service->liquidarQuincena($desde, $hasta, 1.65, $planta->id);
        $estadisticas = $this->service->obtenerEstadisticas($planta->id, $desde, $hasta, false);

        $excel = new ExcelExportService;
        $xlsxBytes = $excel->generarBalanceXlsx(
            $planta,
            $desde,
            $hasta,
            $liquidaciones,
            collect([$acopioPendiente->load('proveedor')]),
            collect([$venta, $gasto->load('proveedor')]),
            $estadisticas['ranking_plantas']
        );

        $tmpZip = tempnam(sys_get_temp_dir(), 'lat_xlsx_');
        $this->assertNotFalse($tmpZip);
        file_put_contents($tmpZip, $xlsxBytes);

        $zip = new ZipArchive;
        $opened = $zip->open($tmpZip);
        $this->assertTrue($opened === true);

        try {
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            $sheet1Xml = $zip->getFromName('xl/worksheets/sheet1.xml');
            $sheet2Xml = $zip->getFromName('xl/worksheets/sheet2.xml');
            $sheet3Xml = $zip->getFromName('xl/worksheets/sheet3.xml');
            $sheet4Xml = $zip->getFromName('xl/worksheets/sheet4.xml');

            $this->assertIsString($workbookXml);
            $this->assertStringContainsString('<calcPr fullCalcOnLoad="1"/>', $workbookXml);

            // AutoFilter debe terminar en la fila 5 (1 fila de datos) y NO incluir la fila 6 de TOTALES
            $this->assertStringContainsString('<autoFilter ref="A4:I5"/>', $sheet1Xml);
            $this->assertStringContainsString('<row r="6" ht="22" customHeight="1"><c r="A6" t="inlineStr" s="10"><is><t xml:space="preserve">TOTALES</t></is></c>', $sheet1Xml);

            $this->assertStringContainsString('<autoFilter ref="A4:I5"/>', $sheet2Xml);
            $this->assertStringContainsString('Pendiente de prueba', $sheet2Xml);

            // Hoja 3: 2 movimientos (filas 5 y 6) => autoFilter A4:F6, totales en filas 7, 8 y 9
            $this->assertStringContainsString('<autoFilter ref="A4:F6"/>', $sheet3Xml);
            $this->assertStringContainsString('TOTAL INGRESOS', $sheet3Xml);
            $this->assertStringContainsString('TOTAL EGRESOS', $sheet3Xml);
            $this->assertStringContainsString('BALANCE NETO', $sheet3Xml);
            $this->assertStringContainsString('SUMIF(B5:B6,&quot;Venta de producto lácteo&quot;,F5:F6)</f><v>300</v>', $sheet3Xml);
            $this->assertStringContainsString('SUMIF(B5:B6,&quot;&lt;&gt;Venta de producto lácteo&quot;,F5:F6)</f><v>45</v>', $sheet3Xml);
            $this->assertStringContainsString('F7-F8</f><v>255</v>', $sheet3Xml);

            $this->assertStringContainsString('<autoFilter ref="A4:I5"/>', $sheet4Xml);
            $this->assertStringContainsString('TOTAL PLANTA', $sheet4Xml);
        } finally {
            $zip->close();
            @unlink($tmpZip);
        }
    }
}
