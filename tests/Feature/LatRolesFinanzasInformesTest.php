<?php

namespace Tests\Feature;

use App\Models\Acopio;
use App\Models\InformeActividadPlanta;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\NotificacionPanel;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\ReporteExcel;
use App\Models\Tina;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class LatRolesFinanzasInformesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_notificaciones_de_tina_solo_llegan_a_encargado_de_planta_y_control_de_tina(): void
    {
        $encargado = User::where('usuario', 'carlos.anta')->firstOrFail();
        $controlTina = User::where('usuario', 'sabina.tina')->firstOrFail();
        $recolector = User::where('usuario', 'mateo.ruta')->firstOrFail();
        $contador = User::where('usuario', 'contador.marco')->firstOrFail();
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $prov = Proveedor::where('planta_id', $encargado->planta_id)->firstOrFail();

        $acopio = Acopio::create([
            'planta_id' => $encargado->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $recolector->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '06:30:00',
            'litros' => 28.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'pendiente',
            'destino' => 'pendiente',
        ]);

        $this->actingAs($controlTina)
            ->post("/panel/calidad/{$acopio->id}", ['calidad' => 'buena'])
            ->assertRedirect();

        $lote = Lote::where('planta_id', $encargado->planta_id)->enProceso()->firstOrFail();
        $this->actingAs($encargado)
            ->post("/panel/tina/{$lote->id}", ['moldes' => 12])
            ->assertRedirect();

        $notifCalidad = NotificacionPanel::where('planta_id', $encargado->planta_id)
            ->where('tipo', 'calidad_aprobada')
            ->latest('id')
            ->firstOrFail();
        $notifFinal = NotificacionPanel::where('planta_id', $encargado->planta_id)
            ->where('tipo', 'tina_finalizada')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(['encargado_planta', 'control_tina'], $notifCalidad->roles_destino);
        $this->assertSame(['encargado_planta', 'control_tina'], $notifFinal->roles_destino);

        foreach ([$encargado, $controlTina] as $autorizado) {
            $items = $this->actingAs($autorizado)
                ->getJson('/panel/notificaciones/poll')
                ->assertOk()
                ->json('items');

            $ids = array_column($items, 'id');
            $this->assertContains($notifCalidad->id, $ids);
            $this->assertContains($notifFinal->id, $ids);
        }

        foreach ([$recolector, $contador, $admin] as $excluido) {
            $items = $this->actingAs($excluido)
                ->getJson('/panel/notificaciones/poll')
                ->assertOk()
                ->json('items');

            $ids = array_column($items, 'id');
            $tipos = array_column($items, 'tipo');
            $this->assertNotContains($notifCalidad->id, $ids);
            $this->assertNotContains($notifFinal->id, $ids);
            $this->assertNotContains('calidad_aprobada', $tipos);
            $this->assertNotContains('tina_finalizada', $tipos);
        }
    }

    public function test_admin_total_bloqueado_en_operaciones_m1_m2_m3_por_defecto_y_habilitado_con_interruptor(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $planta1 = Planta::where('codigo', 'PLT-01')->firstOrFail();
        $prov = Proveedor::where('planta_id', $planta1->id)->firstOrFail();
        $acopio = Acopio::where('planta_id', $planta1->id)->whereNull('lote_id')->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $planta1->id)->whereDoesntHave('loteActivo')->firstOrFail();

        $this->actingAs($admin)
            ->get('/panel')
            ->assertOk()
            ->assertSee('id="form-toggle-modo-operativo"', false)
            ->assertSee('id="btn-toggle-modo-operativo"', false);

        // Por defecto, operaciones de M1, M2 y M3 retornan 403
        $this->actingAs($admin)->post('/panel/acopio', [
            'proveedor_id' => $prov->id,
            'litros' => 20,
        ])->assertForbidden();

        $this->actingAs($admin)->put("/panel/acopio/{$acopio->id}", [
            'proveedor_id' => $prov->id,
            'litros' => 25,
            'calidad' => 'buena',
            'metodo' => 'manual',
        ])->assertForbidden();

        $this->actingAs($admin)->post("/panel/calidad/{$acopio->id}", [
            'calidad' => 'acida',
        ])->assertForbidden();

        $this->actingAs($admin)->post('/panel/tinas', [
            'codigo' => 'TINA-90',
            'nombre' => 'Tina Bloqueada Sin Modo',
            'capacidad_litros' => 200,
            'tipo' => 'quesillo',
        ])->assertForbidden();

        $this->actingAs($admin)->post("/panel/tinas/{$tinaLibre->id}/lote", [
            'acopio_ids' => [$acopio->id],
        ])->assertForbidden();

        // Funciones financieras y administrativas siguen activas por defecto
        $this->actingAs($admin)->post('/panel/movimiento', [
            'tipo' => 'venta_producto',
            'producto_categoria' => 'queso_andino',
            'canal_referencia' => 'feria_local',
            'concepto' => 'Venta directa admin',
            'cantidad_unidades' => 5,
            'precio_unitario' => 20.00,
        ])->assertRedirect();

        $this->actingAs($admin)->put('/panel/finanzas/liquidacion-grid', [
            'filas' => [[
                'proveedor_id' => $prov->id,
                'precio_litro' => 1.75,
                'adelantos' => 10.00,
                'estado_pago' => 'parcial',
            ]],
        ])->assertRedirect();

        $this->actingAs($admin)->get('/panel/exportar-excel')->assertOk();

        $this->actingAs($admin)->post('/panel/admin/plantas', [
            'codigo' => 'PLT-09',
            'nombre' => 'Planta Admin Test',
            'ubicacion' => 'Zurite · Anta',
            'precio_litro' => 1.80,
        ])->assertRedirect();

        $this->actingAs($admin)->post('/panel/cambiar-planta', [
            'planta_id' => $planta1->id,
        ])->assertRedirect();

        // Activar modo operativo vía interruptor real
        $this->actingAs($admin)
            ->post('/panel/admin/modo-operativo', ['activar' => '1'])
            ->assertRedirect()
            ->assertSessionHas('admin_modo_operativo', true);

        $this->actingAs($admin)->post('/panel/tinas', [
            'codigo' => '90',
            'nombre' => 'Tina Modo Operativo',
            'capacidad_litros' => 250,
            'tipo' => 'principal_queso',
        ])->assertRedirect();
        $this->assertDatabaseHas('tinas', [
            'planta_id' => $planta1->id,
            'codigo' => 'TINA-90',
        ]);

        $this->actingAs($admin)->post("/panel/calidad/{$acopio->id}", [
            'calidad' => 'acida',
        ])->assertRedirect();
        $this->assertSame('acida', $acopio->fresh()->calidad);

        // Desactivar modo operativo vuelve a bloquear M1/M2/M3 con 403
        $this->actingAs($admin)
            ->post('/panel/admin/modo-operativo', ['activar' => '0'])
            ->assertRedirect()
            ->assertSessionHas('admin_modo_operativo', false);

        $this->actingAs($admin)->post('/panel/tinas', [
            'codigo' => '91',
            'nombre' => 'Tina Desactivada',
            'capacidad_litros' => 250,
            'tipo' => 'quesillo',
        ])->assertForbidden();

        $this->actingAs($admin)->post("/panel/calidad/{$acopio->id}", [
            'calidad' => 'buena',
        ])->assertForbidden();
    }

    public function test_notificaciones_entre_contador_y_administradores_llegan_en_sombreado_verde(): void
    {
        Storage::fake('local');

        $encargado = User::where('usuario', 'carlos.anta')->firstOrFail();
        $contador = User::where('usuario', 'contador.marco')->firstOrFail();
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $recolector = User::where('usuario', 'mateo.ruta')->firstOrFail();

        // 1. Encargado envía Excel al contador -> notificación verde solo para contador
        $this->actingAs($encargado)->post('/panel/reportes-excel/enviar', [
            'destinatario_id' => $contador->id,
            'tipo_reporte' => 'ventas_productos',
            'fecha_desde' => Carbon::today()->subDays(5)->toDateString(),
            'fecha_hasta' => Carbon::today()->toDateString(),
            'notas' => 'Ventas semanales ordenadas',
        ])->assertRedirect();

        $reporte = ReporteExcel::latest('id')->firstOrFail();
        $notifEnvio = NotificacionPanel::where('tipo', 'comunicacion_contable')
            ->where('destinatario_id', $contador->id)
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('verde', $notifEnvio->color_tema);

        $this->actingAs($contador)
            ->getJson('/panel/notificaciones/poll')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $notifEnvio->id,
                'color_tema' => 'verde',
            ]);

        foreach ([$recolector, $admin] as $otro) {
            $ids = array_column(
                $this->actingAs($otro)->getJson('/panel/notificaciones/poll')->assertOk()->json('items'),
                'id'
            );
            $this->assertNotContains($notifEnvio->id, $ids);
        }

        // 2. Contador marca revisado -> notificación verde vuelve al remitente
        $this->actingAs($contador)
            ->post("/panel/reportes-excel/{$reporte->id}/revisar")
            ->assertRedirect();

        $notifRevision = NotificacionPanel::where('tipo', 'comunicacion_contable')
            ->where('destinatario_id', $encargado->id)
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('verde', $notifRevision->color_tema);

        $this->actingAs($encargado)
            ->getJson('/panel/notificaciones/poll')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $notifRevision->id,
                'color_tema' => 'verde',
            ]);

        // 3. Notificación contable directa Contador -> Administrador Total
        $this->actingAs($contador)->post('/panel/notificaciones/contable', [
            'destinatario_id' => $admin->id,
            'asunto' => 'Cierre validado',
            'mensaje' => 'Conformidad de balances de la quincena.',
        ])->assertRedirect();

        $notifParaAdmin = NotificacionPanel::where('tipo', 'comunicacion_contable')
            ->where('destinatario_id', $admin->id)
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('verde', $notifParaAdmin->color_tema);

        $this->actingAs($admin)
            ->getJson('/panel/notificaciones/poll')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $notifParaAdmin->id,
                'color_tema' => 'verde',
                'titulo' => 'Cierre validado',
            ]);

        // 4. Notificación contable directa Administrador Total -> Contador
        $this->actingAs($admin)->post('/panel/notificaciones/contable', [
            'destinatario_id' => $contador->id,
            'asunto' => 'Revisar ventas feria',
            'mensaje' => 'Validar boletas de feria dominical.',
        ])->assertRedirect();

        $notifParaContador = NotificacionPanel::where('tipo', 'comunicacion_contable')
            ->where('destinatario_id', $contador->id)
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('verde', $notifParaContador->color_tema);

        $this->actingAs($contador)
            ->getJson('/panel/notificaciones/poll')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $notifParaContador->id,
                'color_tema' => 'verde',
                'titulo' => 'Revisar ventas feria',
            ]);
    }

    public function test_liquidacion_estilo_excel_edicion_filtros_y_descarga_exclusiva(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $prov = Proveedor::where('planta_id', $admin->planta_id)->where('codigo', 'PRV-01')->firstOrFail();

        $hoy = Carbon::today();
        $desde = $hoy->day <= 15
            ? $hoy->copy()->startOfMonth()->toDateString()
            : $hoy->copy()->startOfMonth()->addDays(15)->toDateString();
        $hasta = $hoy->day <= 15
            ? $hoy->copy()->startOfMonth()->addDays(14)->toDateString()
            : $hoy->copy()->endOfMonth()->toDateString();

        $this->actingAs($admin)->put('/panel/finanzas/liquidacion-grid', [
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'filas' => [[
                'proveedor_id' => $prov->id,
                'precio_litro' => 2.15,
                'adelantos' => 12.50,
                'estado_pago' => 'pagado',
            ]],
        ])->assertRedirect();

        $this->assertDatabaseHas('liquidaciones', [
            'planta_id' => $admin->planta_id,
            'proveedor_id' => $prov->id,
            'tipo' => 'quincena',
            'estado_pago' => 'pagado',
        ]);

        $resFiltro = $this->actingAs($admin)->get('/panel?'.http_build_query([
            'liq_desde' => $desde,
            'liq_hasta' => $hasta,
            'liq_proveedor_id' => $prov->id,
            'liq_sector' => $prov->sector,
            'liq_estado' => 'pagado',
        ]));

        $resFiltro->assertOk();
        $resFiltro->assertSee('id="tabla-excel-liquidacion"', false);
        $resFiltro->assertViewHas('liquidaciones', function ($liqs) use ($prov): bool {
            return $liqs->count() === 1
                && $liqs->first()->proveedor_id === $prov->id
                && $liqs->first()->precio_litro === 2.15
                && $liqs->first()->adelantos === 12.50
                && $liqs->first()->estado_pago === 'pagado';
        });

        $excelRes = $this->actingAs($admin)->get('/panel/exportar-excel?'.http_build_query([
            'solo' => 'liquidacion',
            'liq_desde' => $desde,
            'liq_hasta' => $hasta,
            'liq_proveedor_id' => $prov->id,
        ]));

        $excelRes->assertOk();
        $excelRes->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $tmp = tempnam(sys_get_temp_dir(), 'lat_liq_');
        $this->assertNotFalse($tmp);
        file_put_contents($tmp, $excelRes->getContent());
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp) === true);

        try {
            $sheet1 = $zip->getFromName('xl/worksheets/sheet1.xml');
            $this->assertIsString($sheet1);
            $this->assertFalse($zip->getFromName('xl/worksheets/sheet2.xml'));

            $sheet1Decoded = html_entity_decode($sheet1, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $this->assertStringContainsString($prov->codigo, $sheet1Decoded);
            $this->assertStringContainsString('ROUND(D', $sheet1Decoded);
            $this->assertStringContainsString('MAX(0,', $sheet1Decoded);
            $this->assertStringContainsString('SUM(', $sheet1Decoded);
        } finally {
            $zip->close();
            @unlink($tmp);
        }
    }

    public function test_tablas_separadas_ventas_y_sueldos_filtros_y_descargas_exclusivas(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        $this->actingAs($admin)->post('/panel/movimiento', [
            'tipo' => 'venta_producto',
            'producto_categoria' => 'queso_andino',
            'canal_referencia' => 'feria_local',
            'concepto' => 'Venta exclusiva queso andino test',
            'cantidad_unidades' => 12,
            'precio_unitario' => 25.00,
        ])->assertRedirect();

        $this->actingAs($admin)->post('/panel/movimiento', [
            'tipo' => 'sueldo_personal',
            'concepto' => 'Sueldo maestro quesero quincena test',
            'cantidad_unidades' => 6,
            'precio_unitario' => 80.00,
        ])->assertRedirect();

        $this->actingAs($admin)->post('/panel/movimiento', [
            'tipo' => 'ayuda_del_dia',
            'concepto' => 'Ayuda volteo quesos test',
            'monto' => 50.00,
        ])->assertRedirect();

        $venta = Liquidacion::where('concepto', 'Venta exclusiva queso andino test')->firstOrFail();
        $sueldo = Liquidacion::where('concepto', 'Sueldo maestro quesero quincena test')->firstOrFail();
        $this->assertSame(300.0, $venta->monto);
        $this->assertSame(480.0, $sueldo->monto);

        $panelRes = $this->actingAs($admin)->get('/panel');
        $panelRes->assertOk();
        $panelRes->assertViewHas('ventasMovimientos', fn ($col): bool => $col->every(fn (Liquidacion $m): bool => $m->tipo === 'venta_producto')
            && $col->contains('id', $venta->id));
        $panelRes->assertViewHas('sueldosMovimientos', fn ($col): bool => $col->every(fn (Liquidacion $m): bool => in_array($m->tipo, Liquidacion::TIPOS_SUELDOS_PAGOS, true))
            && $col->contains('id', $sueldo->id));

        $html = $panelRes->getContent();
        $this->assertSame(1, preg_match('/<table id="tabla-excel-ventas".*?<\/table>/s', $html, $mVentas));
        $this->assertSame(1, preg_match('/<table id="tabla-excel-sueldos".*?<\/table>/s', $html, $mSueldos));

        $this->assertStringContainsString('Venta exclusiva queso andino test', $mVentas[0]);
        $this->assertStringNotContainsString('Sueldo maestro quesero quincena test', $mVentas[0]);
        $this->assertStringContainsString('Sueldo maestro quesero quincena test', $mSueldos[0]);
        $this->assertStringNotContainsString('Venta exclusiva queso andino test', $mSueldos[0]);

        // Filtros por tabla
        $resFiltros = $this->actingAs($admin)->get('/panel?'.http_build_query([
            'v_producto' => 'queso_andino',
            'v_canal' => 'feria_local',
            's_tipo' => 'sueldo_personal',
        ]));
        $resFiltros->assertOk();
        $resFiltros->assertViewHas('ventasMovimientos', fn ($col): bool => $col->contains('id', $venta->id));
        $resFiltros->assertViewHas('sueldosMovimientos', fn ($col): bool => $col->contains('id', $sueldo->id)
            && ! $col->contains('concepto', 'Ayuda volteo quesos test'));

        // Descarga exclusiva de Ventas (.xlsx)
        $ventasXlsx = $this->actingAs($admin)->get('/panel/exportar-excel?'.http_build_query([
            'solo' => 'ventas',
            'v_producto' => 'queso_andino',
            'v_canal' => 'feria_local',
        ]))->assertOk()->getContent();

        $tmpV = tempnam(sys_get_temp_dir(), 'lat_v_');
        $this->assertNotFalse($tmpV);
        file_put_contents($tmpV, $ventasXlsx);
        $zipV = new ZipArchive;
        $this->assertTrue($zipV->open($tmpV) === true);

        try {
            $sheetV = $zipV->getFromName('xl/worksheets/sheet1.xml');
            $this->assertIsString($sheetV);
            $this->assertFalse($zipV->getFromName('xl/worksheets/sheet2.xml'));

            $decodedV = html_entity_decode($sheetV, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $this->assertStringContainsString('Venta exclusiva queso andino test', $decodedV);
            $this->assertStringNotContainsString('Sueldo maestro quesero quincena test', $decodedV);
            $this->assertStringContainsString('E5*F5', $decodedV);
            $this->assertStringContainsString('SUM(G5:G', $decodedV);
        } finally {
            $zipV->close();
            @unlink($tmpV);
        }

        // Descarga exclusiva de Sueldos y Pagos (.xlsx)
        $sueldosXlsx = $this->actingAs($admin)->get('/panel/exportar-excel?'.http_build_query([
            'solo' => 'sueldos',
            's_tipo' => 'sueldo_personal',
        ]))->assertOk()->getContent();

        $tmpS = tempnam(sys_get_temp_dir(), 'lat_s_');
        $this->assertNotFalse($tmpS);
        file_put_contents($tmpS, $sueldosXlsx);
        $zipS = new ZipArchive;
        $this->assertTrue($zipS->open($tmpS) === true);

        try {
            $sheetS = $zipS->getFromName('xl/worksheets/sheet1.xml');
            $this->assertIsString($sheetS);
            $this->assertFalse($zipS->getFromName('xl/worksheets/sheet2.xml'));

            $decodedS = html_entity_decode($sheetS, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $this->assertStringContainsString('Sueldo maestro quesero quincena test', $decodedS);
            $this->assertStringNotContainsString('Venta exclusiva queso andino test', $decodedS);
            $this->assertStringNotContainsString('Ayuda volteo quesos test', $decodedS);
            $this->assertStringContainsString('E5*F5', $decodedS);
            $this->assertStringContainsString('SUM(G5:G', $decodedS);
        } finally {
            $zipS->close();
            @unlink($tmpS);
        }
    }

    public function test_aislamiento_estricto_de_administrador_de_planta_e_informes_automaticos_y_manuales(): void
    {
        $encargado = User::where('usuario', 'carlos.anta')->firstOrFail();
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $planta1 = Planta::where('codigo', 'PLT-01')->firstOrFail();
        $planta2 = Planta::where('codigo', 'PLT-02')->firstOrFail();

        // Aislamiento estricto: encargado de PLT-01 no crea usuarios en PLT-02 ni asigna permisos globales
        $this->actingAs($encargado)->post('/panel/admin/usuarios', [
            'name' => 'Intento Otra Planta',
            'usuario' => 'intruso.p2',
            'password' => 'clave1234',
            'cargo' => 'recolector_ruta',
            'planta_id' => $planta2->id,
        ])->assertForbidden();

        $this->actingAs($encargado)->post('/panel/admin/usuarios', [
            'name' => 'Intento Plantas Global',
            'usuario' => 'escalar.plantas',
            'password' => 'clave1234',
            'cargo' => 'personalizado',
            'planta_id' => $planta1->id,
            'permisos_present' => '1',
            'permisos' => ['admin.plantas'],
        ])->assertForbidden();

        $this->actingAs($encargado)->post('/panel/admin/usuarios', [
            'name' => 'Intento Red Global',
            'usuario' => 'escalar.red',
            'password' => 'clave1234',
            'cargo' => 'personalizado',
            'planta_id' => $planta1->id,
            'permisos_present' => '1',
            'permisos' => ['dashboard.ver_red'],
        ])->assertForbidden();

        $this->actingAs($encargado)
            ->get('/panel')
            ->assertOk()
            ->assertDontSee('id="card-informes-planta"', false)
            ->assertSee('id="card-enviar-informe-planta"', false);

        // Envío manual del informe de actividad por encargado de planta
        $this->actingAs($encargado)
            ->post('/panel/informes-planta/enviar')
            ->assertRedirect();

        $informeManual = InformeActividadPlanta::where('planta_id', $planta1->id)
            ->where('origen', 'manual_admin_planta')
            ->latest('id')
            ->firstOrFail();
        $this->assertSame($encargado->id, $informeManual->generado_por);

        $notifManual = NotificacionPanel::where('tipo', 'informe_planta')
            ->where('planta_id', $planta1->id)
            ->latest('id')
            ->firstOrFail();
        $this->assertSame(['administracion'], $notifManual->roles_destino);

        // Encargado no puede descargar informes desde la vista de administración
        $this->actingAs($encargado)
            ->get("/panel/estadisticas/informes/{$informeManual->id}/descargar")
            ->assertForbidden();

        // Comando automático 24h genera 1 informe por cada planta activa (3) y notifica a admin
        $this->artisan('lat:generar-informes-planta')->assertExitCode(0);
        $this->assertSame(3, InformeActividadPlanta::where('origen', 'automatico_24h')->count());

        // Admin filtra historial en /panel y descarga el .txt plano trazable
        $resHistorial = $this->actingAs($admin)->get('/panel?'.http_build_query([
            'inf_planta_id' => $planta1->id,
            'inf_origen' => 'manual_admin_planta',
        ]));

        $resHistorial->assertOk();
        $resHistorial->assertSee('id="card-informes-planta"', false);
        $resHistorial->assertViewHas('informesPlanta', function ($col) use ($informeManual): bool {
            return $col->count() === 1 && $col->first()->id === $informeManual->id;
        });

        $txtRes = $this->actingAs($admin)->get("/panel/estadisticas/informes/{$informeManual->id}/descargar");
        $txtRes->assertOk();
        $this->assertStringStartsWith('text/plain', (string) $txtRes->headers->get('Content-Type'));
        $this->assertStringContainsString('.txt', (string) $txtRes->headers->get('Content-Disposition'));

        $txtBody = $txtRes->getContent();
        $this->assertStringContainsString('INFORME DE ACTIVIDAD DE PLANTA', $txtBody);
        $this->assertStringContainsString('PLT-01', $txtBody);
        $this->assertStringContainsString('Planta Láctea San Borreal', $txtBody);
        $this->assertStringContainsString('manual_admin_planta', $txtBody);
        $this->assertStringContainsString('BITÁCORA CRONOLÓGICA DE ACTIVIDADES', $txtBody);
    }

    public function test_finanzas_oculta_formulas_excel_y_actualiza_litros_manual_o_desde_acopios(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $recolector = User::where('usuario', 'mateo.ruta')->firstOrFail();
        $prov = Proveedor::where('planta_id', $admin->planta_id)->where('codigo', 'PRV-01')->firstOrFail();

        $hoy = Carbon::today();
        $desde = $hoy->day <= 15
            ? $hoy->copy()->startOfMonth()->toDateString()
            : $hoy->copy()->startOfMonth()->addDays(15)->toDateString();
        $hasta = $hoy->day <= 15
            ? $hoy->copy()->startOfMonth()->addDays(14)->toDateString()
            : $hoy->copy()->endOfMonth()->toDateString();

        // Registro público muestra que Precio por litro es opcional y funciona con valor vacío
        $this->get('/registrarse')
            ->assertOk()
            ->assertSee('Precio por litro (S/) (opcional)', false);

        $this->post('/registrarse', [
            'name' => 'Ing. Carmen Vega',
            'usuario' => 'carmen.vega',
            'planta_nombre' => 'Quesería Alto Cusco',
            'planta_ubicacion' => 'Anta',
            'precio_litro' => '',
            'password' => 'ClaveSegura123',
            'password_confirmation' => 'ClaveSegura123',
        ])->assertRedirect('/panel');
        $this->post('/logout')->assertRedirect('/');

        // La UI de Finanzas no incluye barras fx ni fórmulas visibles en encabezados
        $panelRes = $this->actingAs($admin)->get('/panel');
        $panelRes->assertOk();
        $panelRes->assertDontSee('excel-formula-bar', false);
        $panelRes->assertDontSee('=MAX(0,', false);
        $panelRes->assertDontSee('(=D×E)', false);
        $panelRes->assertDontSee('(=Cant×P.Unit)', false);
        $panelRes->assertDontSee('(=Días×Tarifa)', false);

        // Edición manual de litros en liquidación (PRV-01 mantiene su adelanto abierto de S/ 30.00)
        $this->actingAs($admin)->put('/panel/finanzas/liquidacion-grid', [
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'filas' => [[
                'proveedor_id' => $prov->id,
                'litros' => 95.5,
                'precio_litro' => 2.00,
                'adelantos' => 30.00,
                'estado_pago' => 'parcial',
            ]],
        ])->assertRedirect();

        $datosManual = $this->actingAs($admin)
            ->getJson('/panel/finanzas/liquidacion-datos?'.http_build_query([
                'liq_desde' => $desde,
                'liq_hasta' => $hasta,
                'liq_proveedor_id' => $prov->id,
            ]))
            ->assertOk()
            ->json();

        $this->assertSame(95.5, (float) $datosManual['filas'][0]['litros']);
        $this->assertSame(191.0, (float) $datosManual['filas'][0]['bruto']);
        $this->assertSame(161.0, (float) $datosManual['filas'][0]['neto']);

        // Usuario sin permisos financieros no puede consultar ni sincronizar
        $this->actingAs($recolector)
            ->postJson('/panel/finanzas/liquidacion-sincronizar')
            ->assertForbidden();

        // Sincronizar desde acopios limpia el ajuste manual y recalcula con los acopios reales
        $acopiosReales = (float) Acopio::dePlanta($admin->planta_id)
            ->where('proveedor_id', $prov->id)
            ->delPeriodo($desde, $hasta)
            ->liquidables()
            ->sum('litros');

        $datosSync = $this->actingAs($admin)
            ->postJson('/panel/finanzas/liquidacion-sincronizar', [
                'liq_desde' => $desde,
                'liq_hasta' => $hasta,
                'liq_proveedor_id' => $prov->id,
            ])
            ->assertOk()
            ->json();

        $this->assertEqualsWithDelta($acopiosReales, (float) $datosSync['filas'][0]['litros'], 0.01);
    }
}
