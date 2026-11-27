<?php

namespace Tests\Feature;

use App\Models\Acopio;
use App\Models\AdelantoAplicacion;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\ReporteExcel;
use App\Models\Tina;
use App\Models\User;
use App\Services\LacteoService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class LatCorreccionesAuditoriaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_adelantos_pagados_no_se_descuentan_dos_veces_y_arrastran_solo_saldo_pendiente(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $planta = Planta::findOrFail($admin->planta_id);
        $prov = Proveedor::create([
            'planta_id' => $planta->id,
            'codigo' => 'FICT-01',
            'nombre' => 'Productor Ficticio Quincena',
            'sector' => 'Sector Demo',
            'activo' => true,
        ]);

        // Quincena 1 (1..15): 50 L a S/ 2.00 = S/ 100.00 bruto, adelanto S/ 40.00
        $q1Desde = '2026-10-01';
        $q1Hasta = '2026-10-15';
        $q2Desde = '2026-10-16';
        $q2Hasta = '2026-10-31';

        Acopio::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $admin->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => '2026-10-05',
            'hora' => '06:30:00',
            'litros' => 50.0,
            'precio_litro' => 2.00,
            'metodo' => 'manual',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => Carbon::parse('2026-10-05 07:00:00'),
        ]);

        Acopio::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $admin->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => '2026-10-20',
            'hora' => '06:30:00',
            'litros' => 60.0,
            'precio_litro' => 2.00,
            'metodo' => 'manual',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => Carbon::parse('2026-10-20 07:00:00'),
        ]);

        $adelanto = Liquidacion::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $prov->id,
            'fecha' => '2026-10-06',
            'tipo' => 'adelanto_efectivo',
            'concepto' => 'Adelanto quincena 1 ficticio',
            'litros' => 0,
            'cantidad_unidades' => 0,
            'monto' => 40.00,
            'comision' => 0.00,
            'descontado' => false,
            'estado_pago' => 'pendiente',
        ]);

        // Confirmar pago en Quincena 1 sin alterar el importe del adelanto (40.00)
        $this->actingAs($admin)->put('/panel/finanzas/liquidacion-grid', [
            'fecha_desde' => $q1Desde,
            'fecha_hasta' => $q1Hasta,
            'filas' => [[
                'proveedor_id' => $prov->id,
                'precio_litro' => 2.00,
                'adelantos' => 40.00,
                'estado_pago' => 'pagado',
            ]],
        ])->assertRedirect();

        // Guardar la misma quincena 1 otra vez debe ser idempotente
        $this->actingAs($admin)->put('/panel/finanzas/liquidacion-grid', [
            'fecha_desde' => $q1Desde,
            'fecha_hasta' => $q1Hasta,
            'filas' => [[
                'proveedor_id' => $prov->id,
                'precio_litro' => 2.00,
                'adelantos' => 40.00,
                'estado_pago' => 'pagado',
            ]],
        ])->assertRedirect();

        $adelantoFresh = $adelanto->fresh();
        $this->assertNotNull($adelantoFresh);
        $this->assertSame(40.00, $adelantoFresh->monto);
        $this->assertTrue($adelantoFresh->descontado);
        $this->assertSame(1, AdelantoAplicacion::where('adelanto_liquidacion_id', $adelanto->id)->count());
        $this->assertSame(40.00, (float) AdelantoAplicacion::where('adelanto_liquidacion_id', $adelanto->id)->sum('monto_aplicado'));

        $service = app(LacteoService::class);

        // El histórico de Quincena 1 conserva los S/ 40.00 descontados
        $histQ1 = $service->liquidarQuincena($q1Desde, $q1Hasta, 1.65, $planta->id, ['proveedor_id' => $prov->id])->first();
        $this->assertNotNull($histQ1);
        $this->assertSame(40.00, $histQ1->adelantos);
        $this->assertSame(60.00, $histQ1->neto);
        $this->assertSame('pagado', $histQ1->estado_pago);

        // Quincena 2 NO vuelve a descontar ese adelanto
        $liqQ2 = $service->liquidarQuincena($q2Desde, $q2Hasta, 1.65, $planta->id, ['proveedor_id' => $prov->id])->first();
        $this->assertNotNull($liqQ2);
        $this->assertSame(0.00, $liqQ2->adelantos);
        $this->assertSame(120.00, $liqQ2->neto);

        // Editar el concepto del adelanto ya descontado NO debe reactivar descontado=false
        $this->actingAs($admin)->put("/panel/movimiento/{$adelanto->id}", [
            'tipo' => 'adelanto_efectivo',
            'proveedor_id' => $prov->id,
            'fecha' => '2026-10-06',
            'concepto' => 'Adelanto quincena 1 corregido',
            'monto' => 40.00,
        ])->assertRedirect();
        $this->assertTrue($adelanto->fresh()->descontado);

        // Caso de saldo parcial sin cubrir: adelanto S/ 150.00 en Q2 (bruto S/ 120.00) -> aplica 120, arrastra 30 a Q3
        $adelantoGrande = Liquidacion::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $prov->id,
            'fecha' => '2026-10-21',
            'tipo' => 'adelanto_efectivo',
            'concepto' => 'Adelanto mayor al bruto',
            'litros' => 0,
            'cantidad_unidades' => 0,
            'monto' => 150.00,
            'comision' => 0.00,
            'descontado' => false,
            'estado_pago' => 'pendiente',
        ]);

        $this->actingAs($admin)->put('/panel/finanzas/liquidacion-grid', [
            'fecha_desde' => $q2Desde,
            'fecha_hasta' => $q2Hasta,
            'filas' => [[
                'proveedor_id' => $prov->id,
                'precio_litro' => 2.00,
                'adelantos' => 150.00,
                'estado_pago' => 'pagado',
            ]],
        ])->assertRedirect();

        $this->assertFalse($adelantoGrande->fresh()->descontado);
        $this->assertSame(120.00, (float) AdelantoAplicacion::where('adelanto_liquidacion_id', $adelantoGrande->id)->sum('monto_aplicado'));

        $histQ2 = $service->liquidarQuincena($q2Desde, $q2Hasta, 1.65, $planta->id, ['proveedor_id' => $prov->id])->first();
        $this->assertSame(120.00, $histQ2->adelantos);
        $this->assertSame(0.00, $histQ2->neto);

        $liqQ3 = $service->liquidarQuincena('2026-11-01', '2026-11-15', 1.65, $planta->id, ['proveedor_id' => $prov->id])->first();
        $this->assertSame(30.00, $liqQ3->adelantos);
    }

    public function test_acopio_vinculado_a_lote_bloquea_edicion_y_borrado_y_cierra_bypass_de_calidad(): void
    {
        $recolector = User::where('usuario', 'mateo.ruta')->firstOrFail();
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $prov = Proveedor::where('planta_id', $recolector->planta_id)->firstOrFail();
        $prov2 = Proveedor::where('planta_id', $recolector->planta_id)->where('id', '!=', $prov->id)->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $recolector->planta_id)->where('codigo', 'TINA-03')->firstOrFail();

        $acLibre = Acopio::create([
            'planta_id' => $recolector->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $recolector->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '06:15:00',
            'litros' => 25.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'pendiente',
            'destino' => 'pendiente',
        ]);

        // Recolector sin calidad.evaluar NO puede aprobar calidad editando el acopio
        $this->actingAs($recolector)->put("/panel/acopio/{$acLibre->id}", [
            'proveedor_id' => $prov->id,
            'litros' => 25.0,
            'calidad' => 'buena',
            'metodo' => 'manual',
        ])->assertForbidden();
        $this->assertSame('pendiente', $acLibre->fresh()->calidad);

        // Acopio sin lote_id SÍ se puede corregir en litros
        $this->actingAs($recolector)->put("/panel/acopio/{$acLibre->id}", [
            'proveedor_id' => $prov->id,
            'litros' => 30.0,
            'calidad' => 'pendiente',
            'metodo' => 'manual',
        ])->assertRedirect();
        $this->assertSame(30.0, $acLibre->fresh()->litros);

        // Evaluar e iniciar lote con la entrega
        $this->actingAs($operador)->post("/panel/calidad/{$acLibre->id}", ['calidad' => 'buena'])->assertRedirect();
        $this->actingAs($operador)->post("/panel/tinas/{$tinaLibre->id}/lote", [
            'acopio_ids' => [$acLibre->id],
        ])->assertRedirect();

        $lote = Lote::where('tina_id', $tinaLibre->id)->latest('id')->firstOrFail();
        $this->assertSame($lote->id, $acLibre->fresh()->lote_id);
        $this->assertSame(30.0, $lote->litros);

        // Entrega vinculada a un lote NO puede editarse ni borrarse
        $this->actingAs($operador)->put("/panel/acopio/{$acLibre->id}", [
            'proveedor_id' => $prov2->id,
            'litros' => 99.0,
            'calidad' => 'pendiente',
            'metodo' => 'manual',
        ])->assertSessionHasErrors('acopio');

        $this->actingAs($operador)->delete("/panel/acopio/{$acLibre->id}")
            ->assertSessionHasErrors('acopio');

        $this->assertSame(30.0, $acLibre->fresh()->litros);
        $this->assertSame($prov->id, $acLibre->fresh()->proveedor_id);
        $this->assertSame($lote->id, $acLibre->fresh()->lote_id);
        $this->assertSame(30.0, $lote->fresh()->litros);
    }

    public function test_temperatura_se_guarda_en_lote_con_litros_decimales_y_formulario_separado(): void
    {
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $operador->planta_id)->where('codigo', 'TINA-03')->firstOrFail();
        $prov = Proveedor::where('planta_id', $operador->planta_id)->firstOrFail();

        $acDecimal = Acopio::create([
            'planta_id' => $operador->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $operador->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '06:20:00',
            'litros' => 42.3,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => Carbon::now(),
        ]);

        $this->actingAs($operador)->post("/panel/tinas/{$tinaLibre->id}/lote", [
            'acopio_ids' => [$acDecimal->id],
            'temperatura_actual' => 72.0,
            'cuajo_ml' => 1.06,
            'sal_kg' => 0.76,
            'cultivo_g' => 0.42,
        ])->assertRedirect();

        $lote = Lote::where('tina_id', $tinaLibre->id)->latest('id')->firstOrFail();
        $this->assertSame(42.3, $lote->litros);

        // El HTML renderiza formulario separado de temperatura y step="0.1" min="0.1" para litros
        $panel = $this->actingAs($operador)->get('/panel');
        $panel->assertOk();
        $panel->assertSee('class="grid-form-tina-temp"', false);
        $panel->assertSee('step="0.1" min="0.1"', false);

        // Guardar únicamente la temperatura no altera litros ni receta
        $this->actingAs($operador)->post("/panel/tina/{$lote->id}", [
            'temperatura' => 38.2,
        ])->assertRedirect();

        $fresh = $lote->fresh();
        $this->assertSame(38.2, $fresh->temperatura_actual);
        $this->assertSame(42.3, $fresh->litros);
        $this->assertSame(1.06, $fresh->cuajo_ml);
        $this->assertSame(0.76, $fresh->sal_kg);
        $this->assertSame(0.42, $fresh->cultivo_g);
    }

    public function test_descartar_conserva_ficha_excluye_de_tinas_y_liquidacion_paga_solo_evaluada_aprovechable(): void
    {
        $evaluador = User::where('usuario', 'sabina.tina')->firstOrFail();
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $planta = Planta::findOrFail($evaluador->planta_id);
        $tinaLibre = Tina::where('planta_id', $planta->id)->where('codigo', 'TINA-03')->firstOrFail();
        $prov = Proveedor::create([
            'planta_id' => $planta->id,
            'codigo' => 'FICT-LIQ',
            'nombre' => 'Productor Regla Pago',
            'sector' => 'Anta',
            'activo' => true,
        ]);

        $hoy = Carbon::today()->toDateString();
        $crear = fn (float $litros): Acopio => Acopio::create([
            'planta_id' => $planta->id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $operador->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => $hoy,
            'hora' => '06:30:00',
            'litros' => $litros,
            'precio_litro' => 2.00,
            'metodo' => 'manual',
            'calidad' => 'pendiente',
            'destino' => 'pendiente',
        ]);

        $acBuena = $crear(20.0);
        $acAcida = $crear(10.0);
        $acPendiente = $crear(15.0);
        $acDescartada = $crear(25.0);

        $this->actingAs($evaluador)->post("/panel/calidad/{$acBuena->id}", ['calidad' => 'buena'])->assertRedirect();
        $this->actingAs($evaluador)->post("/panel/calidad/{$acAcida->id}", ['calidad' => 'acida'])->assertRedirect();
        $this->actingAs($evaluador)->post("/panel/calidad/{$acDescartada->id}", [
            'calidad' => 'descartada',
            'motivo_descarte' => 'Contaminación con agua de lluvia',
        ])->assertRedirect();

        // Ficha conservada y excluida de Tinas
        $descFresh = $acDescartada->fresh();
        $this->assertNotNull($descFresh);
        $this->assertSame('descartada', $descFresh->calidad);
        $this->assertSame('descartada', $descFresh->destino);
        $this->assertSame('Contaminación con agua de lluvia', $descFresh->motivo_descarte);
        $this->assertSame($evaluador->id, $descFresh->evaluado_por);
        $this->assertFalse(Acopio::disponiblesParaTina()->where('id', $acDescartada->id)->exists());

        $this->actingAs($operador)->post("/panel/tinas/{$tinaLibre->id}/lote", [
            'acopio_ids' => [$acDescartada->id],
        ])->assertSessionHasErrors('acopio_ids');

        // Liquidación suma SOLO buena (20) + acida (10) = 30 L -> S/ 60.00; excluye pendiente (15) y descartada (25)
        $desde = Carbon::today()->day <= 15
            ? Carbon::today()->startOfMonth()->toDateString()
            : Carbon::today()->startOfMonth()->addDays(15)->toDateString();
        $hasta = Carbon::today()->day <= 15
            ? Carbon::today()->startOfMonth()->addDays(14)->toDateString()
            : Carbon::today()->endOfMonth()->toDateString();

        $fila = app(LacteoService::class)
            ->liquidarQuincena($desde, $hasta, 1.65, $planta->id, ['proveedor_id' => $prov->id])
            ->first();

        $this->assertNotNull($fila);
        $this->assertSame(30.0, $fila->litros);
        $this->assertSame(60.00, $fila->bruto);

        // Exportación Excel respeta la misma regla (30 L, S/ 60.00)
        $excelRes = $this->actingAs($admin)->get('/panel/exportar-excel?'.http_build_query([
            'solo' => 'liquidacion',
            'liq_desde' => $desde,
            'liq_hasta' => $hasta,
            'liq_proveedor_id' => $prov->id,
        ]))->assertOk();

        $tmp = tempnam(sys_get_temp_dir(), 'lat_rule_');
        $this->assertNotFalse($tmp);
        file_put_contents($tmp, $excelRes->getContent());
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp) === true);

        try {
            $sheet1 = html_entity_decode((string) $zip->getFromName('xl/worksheets/sheet1.xml'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $this->assertStringContainsString('<v>30</v>', $sheet1);
            $this->assertStringContainsString('<v>60</v>', $sheet1);
        } finally {
            $zip->close();
            @unlink($tmp);
        }

        // Historial muestra la ficha descartada y su motivo
        $this->actingAs($admin)->get('/panel')
            ->assertOk()
            ->assertSee('Contaminación con agua de lluvia');
    }

    public function test_tinas_css_oculta_hidden_y_modal_muestra_recibida_evaluada_y_aviso_fuera_de_filtro(): void
    {
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();

        $css = file_get_contents(public_path('css/lat-dashboard.css'));
        $this->assertIsString($css);
        $this->assertStringContainsString('.it-acopio-item[hidden]', $css);

        $panel = $this->actingAs($operador)->get('/panel');
        $panel->assertOk();
        $panel->assertSee('Fecha de acopio');
        $panel->assertSee('Recibida:');
        $panel->assertSee('Evaluada:');
        $panel->assertSee('id="it-hidden-selection-notice"', false);
        $panel->assertSee('id="it-btn-desmarcar-ocultas"', false);
    }

    public function test_calidad_busca_pendientes_de_otros_dias_sin_depender_del_filtro_de_acopio(): void
    {
        $controlTina = User::where('usuario', 'sabina.tina')->firstOrFail();
        $recolector = User::where('usuario', 'mateo.ruta')->firstOrFail();
        $encargado = User::where('usuario', 'carlos.anta')->firstOrFail();
        $prov = Proveedor::where('planta_id', $controlTina->planta_id)->firstOrFail();

        $ayer = Carbon::yesterday()->toDateString();
        $acAyer = Acopio::create([
            'planta_id' => $controlTina->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $recolector->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => $ayer,
            'hora' => '17:45:00',
            'litros' => 19.5,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'pendiente',
            'destino' => 'pendiente',
        ]);

        // Abre en Hoy, pero avisa pendientes de otros días
        $resHoy = $this->actingAs($controlTina)->get('/panel');
        $resHoy->assertOk();
        $resHoy->assertViewHas('pendientesOtrosDias', 1);
        $resHoy->assertViewHas('acopiosCalidad', fn ($col): bool => ! $col->contains('id', $acAyer->id));

        // Desde Calidad encuentra pendientes de ayer sin tocar Acopio
        $resPend = $this->actingAs($controlTina)->get('/panel?cal_vista=pendientes');
        $resPend->assertOk();
        $resPend->assertViewHas('acopiosCalidad', fn ($col): bool => $col->contains('id', $acAyer->id));

        // Filtrar Acopio por fecha lejana no vacía Calidad de hoy
        $resFiltroAcopio = $this->actingAs($encargado)->get('/panel?'.http_build_query([
            'acopio_fecha_desde' => '2020-01-01',
            'acopio_fecha_hasta' => '2020-01-02',
        ]));
        $resFiltroAcopio->assertOk();
        $resFiltroAcopio->assertViewHas('acopiosHoy', fn ($col): bool => $col->isEmpty());
        $resFiltroAcopio->assertViewHas('acopiosCalidad', fn ($col): bool => $col->isNotEmpty());
    }

    public function test_boton_de_acopio_dice_registrar_entrega_en_vista_y_traducciones(): void
    {
        $recolector = User::where('usuario', 'mateo.ruta')->firstOrFail();

        $res = $this->actingAs($recolector)->get('/panel');
        $res->assertOk();
        $res->assertSee('Registrar entrega');
        $res->assertDontSee('Guardar entrega en tina');

        $i18n = file_get_contents(public_path('js/lat-i18n.js'));
        $this->assertIsString($i18n);
        $this->assertStringContainsString("'m1.save_btn': 'Registrar entrega'", $i18n);
        $this->assertStringContainsString("'m1.save_btn': 'Chaskiyta qillqay'", $i18n);
        $this->assertStringContainsString("'m1.save_btn': 'Record delivery'", $i18n);
    }

    public function test_excel_ventas_y_sueldos_respeta_monto_real_y_sueldo_personal_cuenta_en_gastos(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $plantaId = $admin->planta_id;
        $hoy = Carbon::today()->toDateString();

        // Venta con precio_unitario = 0 y monto = 250.00 (no debe exportar S/ 0.00)
        $this->actingAs($admin)->post('/panel/movimiento', [
            'tipo' => 'venta_producto',
            'producto_categoria' => 'queso_andino',
            'canal_referencia' => 'feria_local',
            'concepto' => 'Venta lote directo sin p.unit',
            'cantidad_unidades' => 5,
            'precio_unitario' => 0,
            'monto' => 250.00,
        ])->assertRedirect();

        // Sueldo de personal con descuento / monto manual (3 * 50 = 150, pero monto final = 135.00)
        $this->actingAs($admin)->post('/panel/movimiento', [
            'tipo' => 'sueldo_personal',
            'concepto' => 'Sueldo ajustado maestro quesero',
            'cantidad_unidades' => 3,
            'precio_unitario' => 50.00,
            'monto' => 135.00,
        ])->assertRedirect();

        $ventasXlsx = $this->actingAs($admin)->get('/panel/exportar-excel?solo=ventas')->assertOk()->getContent();
        $tmpV = tempnam(sys_get_temp_dir(), 'lat_f3_v_');
        file_put_contents($tmpV, $ventasXlsx);
        $zipV = new ZipArchive;
        $this->assertTrue($zipV->open($tmpV) === true);
        try {
            $sheetV = html_entity_decode((string) $zipV->getFromName('xl/worksheets/sheet1.xml'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $this->assertStringContainsString('Venta lote directo sin p.unit', $sheetV);
            $this->assertStringContainsString('<v>250</v>', $sheetV);
        } finally {
            $zipV->close();
            @unlink($tmpV);
        }

        $sueldosXlsx = $this->actingAs($admin)->get('/panel/exportar-excel?solo=sueldos')->assertOk()->getContent();
        $tmpS = tempnam(sys_get_temp_dir(), 'lat_f3_s_');
        file_put_contents($tmpS, $sueldosXlsx);
        $zipS = new ZipArchive;
        $this->assertTrue($zipS->open($tmpS) === true);
        try {
            $sheetS = html_entity_decode((string) $zipS->getFromName('xl/worksheets/sheet1.xml'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $this->assertStringContainsString('Sueldo ajustado maestro quesero', $sheetS);
            $this->assertStringContainsString('<v>135</v>', $sheetS);
        } finally {
            $zipS->close();
            @unlink($tmpS);
        }

        // Balance hoja 3 debe etiquetar sueldo_personal como "Sueldo de personal"
        $balanceXlsx = $this->actingAs($admin)->get('/panel/exportar-excel')->assertOk()->getContent();
        $tmpB = tempnam(sys_get_temp_dir(), 'lat_f3_b_');
        file_put_contents($tmpB, $balanceXlsx);
        $zipB = new ZipArchive;
        $this->assertTrue($zipB->open($tmpB) === true);
        try {
            $sheet3 = html_entity_decode((string) $zipB->getFromName('xl/worksheets/sheet3.xml'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $this->assertStringContainsString(__('lat.mov_sueldo_personal'), $sheet3);
        } finally {
            $zipB->close();
            @unlink($tmpB);
        }

        // LacteoService incluye sueldo_personal en gastos de Dashboard y Estadísticas
        $service = app(LacteoService::class);
        $dash = $service->obtenerDatosDashboard($plantaId, 'hoy', false);
        $stats = $service->obtenerEstadisticas($plantaId, $hoy, $hoy, false);
        $this->assertGreaterThanOrEqual(135.0, $dash['kpis']['ventas']['gastos']);
        $this->assertGreaterThanOrEqual(135.0, $stats['totales_planta']['gastos_monto']);
    }

    public function test_tina_normaliza_tipos_preestablecidos_y_conserva_texto_libre(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['admin_modo_operativo' => true])
            ->post('/panel/tinas', [
                'codigo' => '55',
                'nombre' => 'Tina Quesillo Traducida',
                'capacidad_litros' => 200,
                'tipo' => 'Quesillo 4 días',
            ])
            ->assertRedirect();

        $tina = Tina::where('planta_id', $admin->planta_id)->where('codigo', 'TINA-55')->firstOrFail();
        $this->assertSame('quesillo', $tina->tipo);
        $this->assertTrue($tina->esTipoPreestablecido());

        // Editar enviando etiqueta en inglés también normaliza a la clave canónica
        $this->actingAs($admin)
            ->withSession(['admin_modo_operativo' => true])
            ->put("/panel/tinas/{$tina->id}", [
                'codigo' => '55',
                'nombre' => 'Tina Quesillo Editada',
                'capacidad_litros' => 220,
                'tipo' => '4-day quesillo',
                'activa' => '1',
            ])
            ->assertRedirect();
        $this->assertSame('quesillo', $tina->fresh()->tipo);

        // Texto libre personalizado NO se altera
        $this->actingAs($admin)
            ->withSession(['admin_modo_operativo' => true])
            ->put("/panel/tinas/{$tina->id}", [
                'codigo' => '55',
                'nombre' => 'Tina Especial',
                'capacidad_litros' => 220,
                'tipo' => 'Mantequilla artesanal',
                'activa' => '1',
            ])
            ->assertRedirect();
        $this->assertSame('Mantequilla artesanal', $tina->fresh()->tipo);
        $this->assertFalse($tina->fresh()->esTipoPreestablecido());
    }

    public function test_finanzas_usa_step_centavos_y_offline_implementa_chunks_y_limpieza_cache(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $html = $this->actingAs($admin)->get('/panel')->assertOk()->getContent();

        $this->assertStringContainsString('id="nv-total" name="monto" class="input-lat js-venta-total" min="0.5" max="1000000" step="0.01"', $html);
        $this->assertStringContainsString('id="ns-total" name="monto" class="input-lat js-sueldo-total" min="0.5" max="1000000" step="0.01"', $html);
        $this->assertStringContainsString('id="edit-mov-monto" class="input-lat" required', $html);
        $this->assertStringContainsString('step="0.01" min="0.5" name="monto" id="edit-mov-monto"', $html);
        $this->assertStringContainsString('class="js-logout-form"', $html);

        $offlineJs = file_get_contents(public_path('js/lat-offline.js'));
        $swJs = file_get_contents(public_path('sw.js'));
        $this->assertIsString($offlineJs);
        $this->assertIsString($swJs);
        $this->assertStringContainsString('CHUNK_SIZE = 50', $offlineJs);
        $this->assertStringContainsString('LAT_CLEAR_AUTH_CACHE', $offlineJs);
        $this->assertStringContainsString("const CACHE_VERSION = 'lat-compacto-v9'", $swJs);
        $this->assertStringContainsString('LAT_CLEAR_AUTH_CACHE', $swJs);
    }

    public function test_reactivar_usuario_inactivo_conserva_permisos_y_enviar_excel_a_contador_no_filtra_red(): void
    {
        Storage::fake('local');

        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $contador = User::where('usuario', 'contador.marco')->firstOrFail();
        $recolector = User::where('usuario', 'mateo.ruta')->firstOrFail();

        // Desactivar y reactivar recolector sin enviar permisos
        $recolector->update(['activo' => false]);
        $this->actingAs($admin)->put("/panel/admin/usuarios/{$recolector->id}", [
            'name' => $recolector->name,
            'usuario' => $recolector->usuario,
            'email' => $recolector->email,
            'cargo' => $recolector->cargo,
            'rango' => $recolector->rango,
            'planta_id' => $recolector->planta_id,
            'activo' => '1',
        ])->assertRedirect();

        $recolectorFresh = $recolector->fresh();
        $this->assertTrue($recolectorFresh->activo);
        $this->assertTrue($recolectorFresh->tienePermiso('acopio.crear'));
        $this->assertTrue($recolectorFresh->tienePermiso('acopio.editar'));

        // Admin envía balance_quincenal al contador de PLT-01 -> Hoja 4 no expone PLT-02 ni PLT-03
        $this->actingAs($admin)->post('/panel/reportes-excel/enviar', [
            'destinatario_id' => $contador->id,
            'tipo_reporte' => 'balance_quincenal',
            'fecha_desde' => Carbon::today()->subDays(5)->toDateString(),
            'fecha_hasta' => Carbon::today()->toDateString(),
        ])->assertRedirect();

        $descarga = $this->actingAs($contador)->get('/panel/reportes-excel/'.ReporteExcel::latest('id')->firstOrFail()->id.'/descargar')->assertOk();
        $tmp = tempnam(sys_get_temp_dir(), 'lat_scope_');
        file_put_contents($tmp, $descarga->getContent());
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp) === true);
        try {
            $sheet4 = html_entity_decode((string) $zip->getFromName('xl/worksheets/sheet4.xml'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $this->assertStringContainsString('PLT-01', $sheet4);
            $this->assertStringNotContainsString('PLT-02', $sheet4);
            $this->assertStringNotContainsString('PLT-03', $sheet4);
        } finally {
            $zip->close();
            @unlink($tmp);
        }
    }
}
