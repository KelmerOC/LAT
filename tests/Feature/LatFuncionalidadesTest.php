<?php

namespace Tests\Feature;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\Tina;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class LatFuncionalidadesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_registro_publico_crea_planta_tina_y_usuario_admin(): void
    {
        $response = $this->post('/registrarse', [
            'name' => 'Elena Mamani',
            'usuario' => 'elena.maras',
            'email' => 'elena@maras.pe',
            'planta_nombre' => 'Quesería Maras Norte',
            'planta_ubicacion' => 'Maras · Urubamba',
            'precio_litro' => 1.75,
            'password' => 'clave1234',
            'password_confirmation' => 'clave1234',
        ]);

        $response->assertRedirect('/panel');
        $this->assertAuthenticated();

        $user = User::where('usuario', 'elena.maras')->first();
        $this->assertNotNull($user);
        $this->assertSame('encargado_planta', $user->cargo);
        $this->assertSame('supervisor', $user->rango);
        $this->assertFalse($user->tienePermiso('admin.plantas'));
        $this->assertFalse($user->tienePermiso('dashboard.ver_red'));

        $planta = Planta::find($user->planta_id);
        $this->assertSame('Quesería Maras Norte', $planta->nombre);
        $this->assertSame(1, $planta->tinas()->count());
    }

    public function test_admin_crea_y_edita_credenciales_de_usuario_y_no_admin_es_bloqueado(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $planta = Planta::where('codigo', 'PLT-01')->firstOrFail();

        $res = $this->actingAs($admin)->post('/panel/admin/usuarios', [
            'name' => 'Operario Nuevo',
            'usuario' => 'nuevo.ruta',
            'email' => '',
            'password' => 'operario123',
            'cargo' => 'recolector_ruta',
            'planta_id' => $planta->id,
        ]);

        $res->assertRedirect();
        $nuevo = User::where('usuario', 'nuevo.ruta')->firstOrFail();
        $this->assertSame('recolector_ruta', $nuevo->cargo);
        $this->assertSame($admin->id, $nuevo->creado_por);

        // Actualizar cargo del nuevo usuario
        $resEdit = $this->actingAs($admin)->put("/panel/admin/usuarios/{$nuevo->id}", [
            'name' => 'Operario Ascendido',
            'usuario' => 'nuevo.ruta',
            'cargo' => 'control_tina',
            'planta_id' => $planta->id,
            'activo' => '1',
        ]);
        $resEdit->assertRedirect();
        $this->assertSame('control_tina', $nuevo->fresh()->cargo);

        // Usuario no admin sin permiso admin.usuarios no puede crear usuarios
        $this->actingAs($nuevo)->post('/panel/admin/usuarios', [
            'name' => 'Intruso',
            'usuario' => 'intruso',
            'password' => 'secret123',
            'cargo' => 'administracion',
            'planta_id' => $planta->id,
        ])->assertStatus(403);
    }

    public function test_usuario_personalizado_solo_ve_y_accede_a_sus_secciones_y_controles(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $planta = Planta::where('codigo', 'PLT-01')->firstOrFail();

        // Crear usuario personalizado con acceso únicamente a Tinas (ver + operar)
        $this->actingAs($admin)->post('/panel/admin/usuarios', [
            'name' => 'Lucía Quesera',
            'usuario' => 'lucia.tina',
            'password' => 'clave1234',
            'cargo' => 'personalizado',
            'rango' => 'personalizado',
            'planta_id' => $planta->id,
            'permisos_present' => '1',
            'permisos' => ['tinas.ver', 'tinas.operar'],
        ])->assertRedirect();

        $custom = User::where('usuario', 'lucia.tina')->firstOrFail();
        $this->assertSame('personalizado', $custom->cargo);
        $this->assertSame(['tinas.ver', 'tinas.operar'], $custom->permisosEfectivos());
        $this->assertSame('tinas', $custom->primeraSeccionPermitida());

        // Al entrar al panel solo ve la sección de Tinas, no Dashboard, Acopio, Liquidación ni Admin
        $panelRes = $this->actingAs($custom)->get('/panel');
        $panelRes->assertStatus(200);
        $panelRes->assertSee('id="sec-tina"', false);
        $panelRes->assertDontSee('id="sec-dashboard"', false);
        $panelRes->assertDontSee('id="sec-acopio"', false);
        $panelRes->assertDontSee('id="sec-quincena"', false);
        $panelRes->assertDontSee('id="sec-admin"', false);

        // Puede operar lote en tina
        $lote = Lote::where('planta_id', $planta->id)->enProceso()->firstOrFail();
        $this->actingAs($custom)->post("/panel/tina/{$lote->id}", [
            'temperatura' => 38.5,
            'litros' => $lote->litros,
        ])->assertRedirect();
        $this->assertSame(38.5, $lote->fresh()->temperatura_actual);

        // Bloqueado (403) en controles y secciones no otorgados
        $prov = Proveedor::where('planta_id', $planta->id)->firstOrFail();
        $acopio = Acopio::where('planta_id', $planta->id)->firstOrFail();

        $this->actingAs($custom)->post('/panel/acopio', [
            'proveedor_id' => $prov->id,
            'litros' => 20,
        ])->assertStatus(403);
        $this->actingAs($custom)->post("/panel/calidad/{$acopio->id}", ['calidad' => 'buena'])->assertStatus(403);
        $this->actingAs($custom)->post('/panel/tinas', [
            'codigo' => 'TINA-99',
            'nombre' => 'No autorizada',
            'capacidad_litros' => 200,
            'tipo' => 'quesillo',
        ])->assertStatus(403);
        $this->actingAs($custom)->post('/panel/movimiento', [
            'tipo' => 'venta_producto',
            'concepto' => 'No permitido',
            'monto' => 100,
        ])->assertStatus(403);
        $this->actingAs($custom)->get('/panel/exportar-excel')->assertStatus(403);
    }

    public function test_control_granular_ver_sin_editar_bloquea_modificaciones(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $planta = Planta::where('codigo', 'PLT-01')->firstOrFail();

        $this->actingAs($admin)->post('/panel/admin/usuarios', [
            'name' => 'Registrador Sin Edicion',
            'usuario' => 'solo.crear',
            'password' => 'clave1234',
            'cargo' => 'personalizado',
            'planta_id' => $planta->id,
            'permisos_present' => '1',
            'permisos' => ['acopio.ver', 'acopio.crear'],
        ])->assertRedirect();

        $user = User::where('usuario', 'solo.crear')->firstOrFail();
        $prov = Proveedor::where('planta_id', $planta->id)->firstOrFail();
        $acopio = Acopio::where('planta_id', $planta->id)->firstOrFail();

        // Puede registrar acopio
        $this->actingAs($user)->post('/panel/acopio', [
            'proveedor_id' => $prov->id,
            'litros' => 25.0,
            'metodo' => 'manual',
        ])->assertRedirect();

        // En la vista no debe aparecer el botón de editar acopio
        $this->actingAs($user)->get('/panel')->assertDontSee('js-edit-acopio');

        // No puede editar ni eliminar
        $this->actingAs($user)->put("/panel/acopio/{$acopio->id}", [
            'proveedor_id' => $prov->id,
            'litros' => 99,
            'calidad' => 'buena',
            'metodo' => 'manual',
        ])->assertStatus(403);

        $this->actingAs($user)->delete("/panel/acopio/{$acopio->id}")->assertStatus(403);
    }

    public function test_rango_delegado_permite_crear_usuarios_solo_en_su_planta_y_sin_escalar(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $planta1 = Planta::where('codigo', 'PLT-01')->firstOrFail();
        $planta2 = Planta::where('codigo', 'PLT-02')->firstOrFail();

        // Admin otorga rango supervisor con permiso de gestionar usuarios + acopio
        $this->actingAs($admin)->post('/panel/admin/usuarios', [
            'name' => 'Supervisor Planta 1',
            'usuario' => 'sup.planta1',
            'password' => 'clave1234',
            'cargo' => 'personalizado',
            'rango' => 'supervisor',
            'planta_id' => $planta1->id,
            'permisos_present' => '1',
            'permisos' => ['admin.usuarios', 'acopio.ver', 'acopio.crear'],
        ])->assertRedirect();

        $supervisor = User::where('usuario', 'sup.planta1')->firstOrFail();

        // Puede crear un operario en su misma planta con un subconjunto de sus permisos
        $this->actingAs($supervisor)->post('/panel/admin/usuarios', [
            'name' => 'Suboperario Ruta',
            'usuario' => 'sub.ruta',
            'password' => 'clave1234',
            'cargo' => 'personalizado',
            'planta_id' => $planta1->id,
            'permisos_present' => '1',
            'permisos' => ['acopio.ver', 'acopio.crear'],
        ])->assertRedirect();

        $sub = User::where('usuario', 'sub.ruta')->firstOrFail();
        $this->assertSame($supervisor->id, $sub->creado_por);

        // No puede asignar permisos que no posee (ej. finanzas.ver)
        $this->actingAs($supervisor)->post('/panel/admin/usuarios', [
            'name' => 'Intento Escalado',
            'usuario' => 'escalado.user',
            'password' => 'clave1234',
            'cargo' => 'personalizado',
            'planta_id' => $planta1->id,
            'permisos_present' => '1',
            'permisos' => ['finanzas.ver'],
        ])->assertStatus(403);

        // No puede crear usuario en otra planta
        $this->actingAs($supervisor)->post('/panel/admin/usuarios', [
            'name' => 'Otra Planta',
            'usuario' => 'otra.planta',
            'password' => 'clave1234',
            'cargo' => 'personalizado',
            'planta_id' => $planta2->id,
            'permisos_present' => '1',
            'permisos' => ['acopio.ver'],
        ])->assertStatus(403);

        // No puede crear o editar administradores
        $this->actingAs($supervisor)->put("/panel/admin/usuarios/{$admin->id}", [
            'name' => 'Hack Admin',
            'usuario' => $admin->usuario,
            'cargo' => 'administracion',
            'planta_id' => $planta1->id,
        ])->assertStatus(403);
    }

    public function test_dashboard_retorna_series_temporales_y_respeta_filtro_de_rango(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        $res7d = $this->actingAs($admin)->get('/panel?rango=7d');
        $res7d->assertStatus(200);
        $res7d->assertSee('id="sec-dashboard"', false);
        $res7d->assertSee('id="chart-acopio-diario"', false);
        $res7d->assertSee('id="chart-ventas-gastos"', false);
        $res7d->assertSee('id="chart-moldes-diarios"', false);
        $res7d->assertSee('Últimos 7 días');

        $resMes = $this->actingAs($admin)->get('/panel?rango=mes');
        $resMes->assertStatus(200);
        $resMes->assertSee('Este mes');
    }

    public function test_admin_gestiona_plantas_y_cambia_planta_activa(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        $this->actingAs($admin)->post('/panel/admin/plantas', [
            'codigo' => 'PLT-04',
            'nombre' => 'Planta Urcos Sur',
            'ubicacion' => 'Quispicanchi · Cusco',
            'precio_litro' => 1.80,
        ])->assertRedirect();

        $planta = Planta::where('codigo', 'PLT-04')->firstOrFail();
        $this->assertSame('Planta Urcos Sur', $planta->nombre);

        $this->actingAs($admin)->put("/panel/admin/plantas/{$planta->id}", [
            'codigo' => 'PLT-04',
            'nombre' => 'Planta Urcos Editada',
            'ubicacion' => 'Urcos Centro',
            'precio_litro' => 1.85,
            'activa' => '1',
        ])->assertRedirect();

        $this->assertSame('Planta Urcos Editada', $planta->fresh()->nombre);
        $this->assertSame(1.85, $planta->fresh()->precio_litro);
    }

    public function test_multiples_tinas_agregar_editar_iniciar_y_finalizar_lote(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        // Agregar nueva tina
        $this->actingAs($admin)->post('/panel/tinas', [
            'codigo' => 'TINA-04',
            'nombre' => 'Tina Queso Paria',
            'capacidad_litros' => 400,
            'tipo' => 'principal_queso',
        ])->assertRedirect();

        $tina = Tina::where('codigo', 'TINA-04')->firstOrFail();

        // Editar tina
        $this->actingAs($admin)->put("/panel/tinas/{$tina->id}", [
            'codigo' => 'TINA-04',
            'nombre' => 'Tina Queso Paria Plus',
            'capacidad_litros' => 450,
            'tipo' => 'multiproposito',
            'activa' => '1',
        ])->assertRedirect();

        $this->assertSame('Tina Queso Paria Plus', $tina->fresh()->nombre);

        // Iniciar, actualizar y finalizar lote con usuario operativo de tina (admin no opera en campo)
        $operadorTina = User::where('usuario', 'carlos.anta')->firstOrFail();

        $this->actingAs($operadorTina)->post("/panel/tinas/{$tina->id}/lote", [
            'litros' => 180,
            'temperatura_actual' => 72.0,
        ])->assertRedirect();

        $lote = Lote::where('tina_id', $tina->id)->firstOrFail();
        $this->assertSame('pasteurizando', $lote->estado);

        // Ajustar temperatura y finalizar lote
        $this->actingAs($operadorTina)->post("/panel/tina/{$lote->id}", [
            'temperatura' => 38.0,
            'litros' => 180,
        ])->assertRedirect();
        $this->assertSame('lista_para_cuajo', $lote->fresh()->estado);

        $this->actingAs($operadorTina)->post("/panel/tina/{$lote->id}", [
            'moldes' => 20,
        ])->assertRedirect();
        $this->assertSame('terminado', $lote->fresh()->estado);
        $this->assertSame(20, $lote->fresh()->moldes_producidos);
    }

    public function test_edicion_de_datos_acopio_y_movimiento(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $acopio = Acopio::where('planta_id', $admin->planta_id)->firstOrFail();
        $prov = Proveedor::where('planta_id', $admin->planta_id)->firstOrFail();

        $this->actingAs($admin)->put("/panel/acopio/{$acopio->id}", [
            'proveedor_id' => $prov->id,
            'litros' => 55.5,
            'calidad' => 'acida',
            'metodo' => 'manual',
            'hora' => '08:15',
        ])->assertRedirect();

        $acopioFresh = $acopio->fresh();
        $this->assertSame(55.5, $acopioFresh->litros);
        $this->assertSame('quesillo_4_dias', $acopioFresh->destino);

        $mov = Liquidacion::where('planta_id', $admin->planta_id)->firstOrFail();
        $this->actingAs($admin)->put("/panel/movimiento/{$mov->id}", [
            'tipo' => 'venta_producto',
            'concepto' => 'Venta corregida feria',
            'cantidad_unidades' => 30,
            'monto' => 600.0,
        ])->assertRedirect();

        $this->assertSame(600.0, $mov->fresh()->monto);
        $this->assertSame('venta_producto', $mov->fresh()->tipo);
    }

    public function test_exportacion_excel_xlsx_valida_y_estadisticas_de_plantas(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        // Verificar carga del panel con estadísticas y ranking
        $panelRes = $this->actingAs($admin)->get('/panel');
        $panelRes->assertStatus(200);
        $panelRes->assertSee('Ranking de plantas');
        $panelRes->assertSee('Planta Láctea San Borreal');
        $panelRes->assertSee('Quesería Valle de Chinchero');

        // Descargar Excel .xlsx
        $excelRes = $this->actingAs($admin)->get('/panel/exportar-excel');
        $excelRes->assertStatus(200);
        $excelRes->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $bytes = $excelRes->getContent();
        // Firma PKZIP (PK\x03\x04)
        $this->assertSame("PK\x03\x04", substr($bytes, 0, 4));
        $this->assertStringContainsString('xl/workbook.xml', $bytes);
        $this->assertStringContainsString('xl/worksheets/sheet1.xml', $bytes);
        $this->assertStringContainsString('xl/worksheets/sheet4.xml', $bytes);
    }

    public function test_cambio_de_idioma_persiste_en_sesion(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        $this->get('/idioma/qu')->assertStatus(302);
        $this->get('/')->assertStatus(200)
            ->assertSee('<html lang="qu" data-server-locale="qu">', false)
            ->assertSee('data-i18n="steps.p2_title"', false)
            ->assertSee('data-i18n="bento.how_title"', false);
        $this->get('/login')->assertStatus(200)
            ->assertSee('<html lang="qu" data-server-locale="qu">', false)
            ->assertSee('data-i18n="login.role_custom"', false);
        $this->actingAs($admin)->get('/panel')->assertStatus(200)
            ->assertSee('<html lang="qu" data-server-locale="qu">', false)
            ->assertSee('data-i18n="dash.c1_title"', false);

        $this->get('/idioma/en')->assertStatus(302);
        $this->get('/')->assertStatus(200)
            ->assertSee('<html lang="en" data-server-locale="en">', false);
        $this->actingAs($admin)->get('/panel')->assertStatus(200)
            ->assertSee('<html lang="en" data-server-locale="en">', false);
    }

    public function test_login_acepta_mayusculas_rechaza_invalidos_e_inactivos_y_logout_cierra_sesion(): void
    {
        $resLogin = $this->post('/login', [
            'login' => 'DONJUSTO',
            'password' => 'secreto123',
        ]);
        $resLogin->assertRedirect('/panel');
        $this->assertAuthenticated();

        $resLogout = $this->post('/logout');
        $resLogout->assertRedirect('/');
        $this->assertGuest();

        $resBad = $this->post('/login', [
            'login' => 'donjusto',
            'password' => 'clave-incorrecta',
        ]);
        $resBad->assertSessionHasErrors('login');
        $this->assertGuest();

        $inactivo = User::where('usuario', 'mateo.ruta')->firstOrFail();
        $inactivo->update(['activo' => false]);

        $resInactivo = $this->post('/login', [
            'login' => 'mateo.ruta',
            'password' => 'secreto123',
        ]);
        $resInactivo->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_usuario_desactivado_con_sesion_abierta_es_desconectado_al_entrar_al_panel(): void
    {
        $user = User::where('usuario', 'mateo.ruta')->firstOrFail();
        $this->actingAs($user);

        $user->update(['activo' => false]);

        $res = $this->get('/panel');
        $res->assertRedirect('/login');
        $res->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_cambiar_planta_y_aislamiento_multiplanta_en_edicion_panel_y_excel(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $planta1 = Planta::where('codigo', 'PLT-01')->firstOrFail();
        $planta2 = Planta::where('codigo', 'PLT-02')->firstOrFail();

        // Admin con admin.plantas puede cambiar la planta activa en sesión
        $this->actingAs($admin)->post('/panel/cambiar-planta', [
            'planta_id' => $planta2->id,
        ])->assertRedirect();

        $this->assertSame($planta2->id, session('planta_activa_id'));
        $this->actingAs($admin)->get('/panel')
            ->assertStatus(200)
            ->assertSee('Quesería Valle de Chinchero');

        // Usuario de Planta 1 sin admin.plantas ni dashboard.ver_red
        $operadorP1 = User::create([
            'planta_id' => $planta1->id,
            'name' => 'Operador Aislado P1',
            'usuario' => 'aislado.p1',
            'password' => 'clave1234',
            'cargo' => 'personalizado',
            'rango' => 'operador',
            'permisos' => [
                'dashboard.ver',
                'acopio.ver',
                'acopio.editar',
                'acopio.proveedores',
                'tinas.ver',
                'tinas.operar',
                'tinas.gestionar',
                'finanzas.ver',
                'finanzas.editar',
                'finanzas.exportar',
            ],
            'activo' => true,
        ]);

        $acopioP2 = Acopio::where('planta_id', $planta2->id)->firstOrFail();
        $movP2 = Liquidacion::where('planta_id', $planta2->id)->firstOrFail();
        $provP2 = Proveedor::where('planta_id', $planta2->id)->firstOrFail();
        $tinaP2 = Tina::where('planta_id', $planta2->id)->firstOrFail();

        $this->actingAs($operadorP1)->put("/panel/acopio/{$acopioP2->id}", [
            'proveedor_id' => $provP2->id,
            'litros' => 50,
            'calidad' => 'buena',
            'metodo' => 'manual',
        ])->assertStatus(404);
        $this->actingAs($operadorP1)->delete("/panel/acopio/{$acopioP2->id}")->assertStatus(404);

        $this->actingAs($operadorP1)->put("/panel/movimiento/{$movP2->id}", [
            'tipo' => 'venta_producto',
            'concepto' => 'Intento cruzado',
            'monto' => 100,
        ])->assertStatus(404);
        $this->actingAs($operadorP1)->delete("/panel/movimiento/{$movP2->id}")->assertStatus(404);

        $this->actingAs($operadorP1)->put("/panel/proveedor/{$provP2->id}", [
            'codigo' => $provP2->codigo,
            'nombre' => 'Nombre hackeado',
            'sector' => 'Otro',
        ])->assertStatus(404);

        $this->actingAs($operadorP1)->put("/panel/tinas/{$tinaP2->id}", [
            'codigo' => $tinaP2->codigo,
            'nombre' => 'Tina hackeada',
            'capacidad_litros' => 300,
            'tipo' => 'principal_queso',
        ])->assertStatus(404);

        // No debe ver otras plantas en /panel ni en la hoja 4 del Excel
        $panelRes = $this->actingAs($operadorP1)->get('/panel');
        $panelRes->assertStatus(200);
        $panelRes->assertSee('Planta Láctea San Borreal');
        $panelRes->assertDontSee('Quesería Valle de Chinchero');
        $panelRes->assertDontSee('Asociación Lechera Cachimayo');

        $excelRes = $this->actingAs($operadorP1)->get('/panel/exportar-excel');
        $excelRes->assertStatus(200);

        $tmp = tempnam(sys_get_temp_dir(), 'lat_p1_');
        $this->assertNotFalse($tmp);
        file_put_contents($tmp, $excelRes->getContent());
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp) === true);

        try {
            $sheet4 = $zip->getFromName('xl/worksheets/sheet4.xml');
            $this->assertIsString($sheet4);
            $this->assertStringContainsString('Planta Láctea San Borreal', $sheet4);
            $this->assertStringContainsString('TOTAL PLANTA', $sheet4);
            $this->assertStringNotContainsString('Quesería Valle de Chinchero', $sheet4);
            $this->assertStringNotContainsString('TOTAL RED DE PLANTAS', $sheet4);
        } finally {
            $zip->close();
            @unlink($tmp);
        }
    }

    public function test_crear_y_editar_proveedor_normaliza_codigo_y_permite_vaciar_sector(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $planta1 = Planta::where('codigo', 'PLT-01')->firstOrFail();

        $this->actingAs($admin)->post('/panel/proveedor', [
            'codigo' => '  prv-99  ',
            'nombre' => 'Doña Rosa Condori',
            'sector' => 'Anta Pampa',
        ])->assertRedirect();

        $prov = Proveedor::where('planta_id', $planta1->id)
            ->where('codigo', 'PRV-99')
            ->firstOrFail();
        $this->assertSame('Anta Pampa', $prov->sector);

        // Código duplicado en minúsculas debe ser rechazado por la normalización case-insensitive
        $this->actingAs($admin)->post('/panel/proveedor', [
            'codigo' => 'prv-99',
            'nombre' => 'Duplicado',
            'sector' => 'Markjo',
        ])->assertSessionHasErrors('codigo');

        // Editar permitiendo vaciar sector
        $this->actingAs($admin)->put("/panel/proveedor/{$prov->id}", [
            'codigo' => 'prv-99b',
            'nombre' => 'Doña Rosa Editada',
            'sector' => '',
        ])->assertRedirect();

        $provFresh = $prov->fresh();
        $this->assertSame('PRV-99B', $provFresh->codigo);
        $this->assertSame('Doña Rosa Editada', $provFresh->nombre);
        $this->assertNull($provFresh->sector);
    }

    public function test_eliminar_acopio_y_movimiento_con_permisos_validos(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $acopio = Acopio::where('planta_id', $admin->planta_id)->firstOrFail();
        $mov = Liquidacion::where('planta_id', $admin->planta_id)->firstOrFail();

        $this->actingAs($admin)->delete("/panel/acopio/{$acopio->id}")->assertRedirect();
        $this->assertDatabaseMissing('acopios', ['id' => $acopio->id]);

        $this->actingAs($admin)->delete("/panel/movimiento/{$mov->id}")->assertRedirect();
        $this->assertDatabaseMissing('liquidaciones', ['id' => $mov->id]);
    }

    public function test_evaluar_calidad_aprueba_desvia_y_rechaza_valor_invalido(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $prov = Proveedor::where('planta_id', $admin->planta_id)->firstOrFail();

        $acopio = Acopio::create([
            'planta_id' => $admin->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $admin->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '07:00:00',
            'litros' => 22.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'pendiente',
            'destino' => 'pendiente',
        ]);

        $this->actingAs($admin)->post("/panel/calidad/{$acopio->id}", [
            'calidad' => 'buena',
        ])->assertRedirect();
        $this->assertSame('buena', $acopio->fresh()->calidad);
        $this->assertSame('queso_yogur', $acopio->fresh()->destino);

        $this->actingAs($admin)->post("/panel/calidad/{$acopio->id}", [
            'calidad' => 'acida',
        ])->assertRedirect();
        $this->assertSame('acida', $acopio->fresh()->calidad);
        $this->assertSame('quesillo_4_dias', $acopio->fresh()->destino);

        $this->actingAs($admin)->post("/panel/calidad/{$acopio->id}", [
            'calidad' => 'desconocida',
        ])->assertSessionHasErrors('calidad');
        $this->assertSame('acida', $acopio->fresh()->calidad);
    }

    public function test_movimientos_calculan_y_recalculan_descontado_segun_tipo(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $prov = Proveedor::where('planta_id', $admin->planta_id)->firstOrFail();

        $this->actingAs($admin)->post('/panel/movimiento', [
            'tipo' => 'adelanto_efectivo',
            'proveedor_id' => $prov->id,
            'concepto' => 'Adelanto forraje test',
            'monto' => 55.00,
        ])->assertRedirect();

        $adelanto = Liquidacion::where('concepto', 'Adelanto forraje test')->firstOrFail();
        $this->assertFalse($adelanto->descontado);

        $this->actingAs($admin)->post('/panel/movimiento', [
            'tipo' => 'venta_producto',
            'concepto' => 'Venta quesos test',
            'cantidad_unidades' => 12,
            'monto' => 240.00,
        ])->assertRedirect();

        $venta = Liquidacion::where('concepto', 'Venta quesos test')->firstOrFail();
        $this->assertTrue($venta->descontado);

        // Cambiar de venta_producto a adelanto_efectivo debe recalcular descontado = false
        $this->actingAs($admin)->put("/panel/movimiento/{$venta->id}", [
            'tipo' => 'adelanto_efectivo',
            'proveedor_id' => $prov->id,
            'concepto' => 'Reclasificado como adelanto',
            'cantidad_unidades' => 0,
            'monto' => 120.00,
        ])->assertRedirect();

        $movFresh = $venta->fresh();
        $this->assertSame('adelanto_efectivo', $movFresh->tipo);
        $this->assertFalse($movFresh->descontado);
    }

    public function test_tina_inactiva_y_lote_terminado_o_litros_invalidos_son_bloqueados(): void
    {
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $tinaInactiva = Tina::create([
            'planta_id' => $operador->planta_id,
            'codigo' => 'TINA-OFF',
            'nombre' => 'Tina En Mantenimiento',
            'capacidad_litros' => 250,
            'tipo' => 'quesillo',
            'activa' => false,
        ]);

        $this->actingAs($operador)->post("/panel/tinas/{$tinaInactiva->id}/lote", [
            'litros' => 100,
            'temperatura_actual' => 72.0,
        ])->assertSessionHasErrors('tina');
        $this->assertSame(0, Lote::where('tina_id', $tinaInactiva->id)->count());

        $loteTerminado = Lote::where('planta_id', $operador->planta_id)
            ->where('estado', 'terminado')
            ->firstOrFail();
        $tempOriginal = $loteTerminado->temperatura_actual;

        $this->actingAs($operador)->post("/panel/tina/{$loteTerminado->id}", [
            'temperatura' => 36.0,
            'litros' => 100,
        ])->assertSessionHasErrors('tina');
        $this->assertSame($tempOriginal, $loteTerminado->fresh()->temperatura_actual);

        $loteActivo = Lote::with('tina')
            ->where('planta_id', $operador->planta_id)
            ->enProceso()
            ->firstOrFail();
        $capacidadExcedida = $loteActivo->tina->capacidad_litros + 50;

        $this->actingAs($operador)->post("/panel/tina/{$loteActivo->id}", [
            'litros' => $capacidadExcedida,
        ])->assertSessionHasErrors('litros');

        $this->actingAs($operador)->post("/panel/tina/{$loteActivo->id}", [
            'litros' => 0,
        ])->assertSessionHasErrors('litros');
    }

    public function test_get_panel_en_planta_recien_creada_no_crea_lotes_ficticios(): void
    {
        $this->post('/registrarse', [
            'name' => 'Mateo Planta Nueva',
            'usuario' => 'mateo.nueva',
            'email' => 'mateo.nueva@quesos.pe',
            'planta_nombre' => 'Quesería Limpia Sin Lotes',
            'planta_ubicacion' => 'Zurite · Anta',
            'precio_litro' => 1.70,
            'password' => 'clave1234',
            'password_confirmation' => 'clave1234',
        ])->assertRedirect('/panel');

        $user = User::where('usuario', 'mateo.nueva')->firstOrFail();
        $this->assertSame(0, Lote::where('planta_id', $user->planta_id)->count());

        $this->actingAs($user)->get('/panel')->assertStatus(200);
        $this->assertSame(0, Lote::where('planta_id', $user->planta_id)->count());
    }

    public function test_proteccion_horizontal_supervisor_no_puede_editar_ni_desactivar_otro_supervisor(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $planta1 = Planta::where('codigo', 'PLT-01')->firstOrFail();

        $this->actingAs($admin)->post('/panel/admin/usuarios', [
            'name' => 'Supervisor Uno',
            'usuario' => 'sup.uno',
            'password' => 'clave1234',
            'cargo' => 'personalizado',
            'rango' => 'supervisor',
            'planta_id' => $planta1->id,
            'permisos_present' => '1',
            'permisos' => ['admin.usuarios', 'acopio.ver'],
        ])->assertRedirect();

        $this->actingAs($admin)->post('/panel/admin/usuarios', [
            'name' => 'Supervisor Dos',
            'usuario' => 'sup.dos',
            'password' => 'clave1234',
            'cargo' => 'personalizado',
            'rango' => 'supervisor',
            'planta_id' => $planta1->id,
            'permisos_present' => '1',
            'permisos' => ['admin.usuarios', 'acopio.ver'],
        ])->assertRedirect();

        $sup1 = User::where('usuario', 'sup.uno')->firstOrFail();
        $sup2 = User::where('usuario', 'sup.dos')->firstOrFail();

        $this->actingAs($sup1)->put("/panel/admin/usuarios/{$sup2->id}", [
            'name' => 'Supervisor Dos Modificado',
            'usuario' => 'sup.dos',
            'cargo' => 'personalizado',
            'rango' => 'supervisor',
            'planta_id' => $planta1->id,
            'activo' => '1',
        ])->assertStatus(403);

        $this->actingAs($sup1)->put("/panel/admin/usuarios/{$sup2->id}", [
            'name' => 'Supervisor Dos',
            'usuario' => 'sup.dos',
            'cargo' => 'personalizado',
            'rango' => 'operador',
            'planta_id' => $planta1->id,
            'activo' => '0',
        ])->assertStatus(403);

        $this->assertTrue($sup2->fresh()->activo);
    }

    public function test_periodo_actual_en_segunda_quincena_excluye_acopios_del_dia_5(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-20 10:00:00'));

        try {
            $planta = Planta::create([
                'codigo' => 'PLT-Q2',
                'nombre' => 'Planta Segunda Quincena',
                'ubicacion' => 'Anta',
                'precio_litro' => 1.50,
                'activa' => true,
            ]);

            Tina::create([
                'planta_id' => $planta->id,
                'codigo' => 'TINA-01',
                'nombre' => 'Tina Principal',
                'capacidad_litros' => 400,
                'tipo' => 'principal_queso',
                'activa' => true,
            ]);

            $user = User::create([
                'planta_id' => $planta->id,
                'name' => 'Admin Q2',
                'usuario' => 'admin.q2',
                'password' => 'clave1234',
                'cargo' => 'administracion',
                'rango' => 'administrador',
                'permisos' => User::todasLasClavesPermisos(),
                'activo' => true,
            ]);

            $prov = Proveedor::create([
                'planta_id' => $planta->id,
                'codigo' => 'Q2-01',
                'nombre' => 'Productor Quincena',
                'sector' => 'Anta',
                'activo' => true,
            ]);

            // Acopio de 1.ª quincena (día 5): NO debe entrar en la 2.ª quincena
            Acopio::create([
                'planta_id' => $planta->id,
                'proveedor_id' => $prov->id,
                'registrado_por' => $user->id,
                'uuid_offline' => (string) Str::uuid(),
                'fecha' => '2026-11-05',
                'hora' => '06:30:00',
                'litros' => 40.0,
                'precio_litro' => 1.50,
                'metodo' => 'manual',
                'calidad' => 'buena',
                'destino' => 'queso_yogur',
            ]);

            // Acopio de 2.ª quincena (día 18): SÍ debe entrar
            Acopio::create([
                'planta_id' => $planta->id,
                'proveedor_id' => $prov->id,
                'registrado_por' => $user->id,
                'uuid_offline' => (string) Str::uuid(),
                'fecha' => '2026-11-18',
                'hora' => '06:45:00',
                'litros' => 25.0,
                'precio_litro' => 1.50,
                'metodo' => 'manual',
                'calidad' => 'buena',
                'destino' => 'queso_yogur',
            ]);

            $res = $this->actingAs($user)->get('/panel');
            $res->assertStatus(200);
            $res->assertViewHas('desde', '2026-11-16');
            $res->assertViewHas('hasta', '2026-11-30');
            $res->assertViewHas('liquidaciones', function ($liqs) {
                $fila = $liqs->firstWhere('codigo', 'Q2-01');

                return $fila !== null && $fila->litros === 25.0 && $fila->bruto === 37.50;
            });
            $res->assertViewHas('estadisticas', function (array $stats) {
                return $stats['totales_planta']['litros_periodo'] === 25.0;
            });
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_idioma_qu_persiste_en_cookie_tras_logout_y_traduce_flashes_y_validaciones(): void
    {
        $resLang = $this->get('/idioma/qu');
        $resLang->assertStatus(302);
        $resLang->assertCookie('lat_locale', 'qu');
        $this->assertSame('qu', session('locale'));

        $resLogin = $this->withCookie('lat_locale', 'qu')->post('/login', [
            'login' => 'mateo.ruta',
            'password' => 'secreto123',
        ]);
        $resLogin->assertRedirect('/panel');
        $resLogin->assertSessionHas('exito', __('lat.auth_welcome', [], 'qu'));
        $this->assertStringNotContainsString('Bienvenido', (string) session('exito'));

        $resInvalid = $this->withCookie('lat_locale', 'qu')->post('/panel/acopio', [
            'proveedor_id' => 999999,
            'litros' => 0,
        ]);
        $resInvalid->assertSessionHasErrors(['proveedor_id', 'litros']);

        $errors = session('errors');
        $this->assertNotNull($errors);
        $provError = $errors->first('proveedor_id');
        $litrosError = $errors->first('litros');

        $this->assertStringContainsString('manam kanchu', $provError);
        $this->assertStringContainsString('yupayllapas utaq aswan hatun kananmi', $litrosError);
        $this->assertStringNotContainsString('El campo', $provError);
        $this->assertStringNotContainsString('debe ser', $litrosError);

        $resLogout = $this->withCookie('lat_locale', 'qu')->post('/logout');
        $resLogout->assertRedirect('/');
        $resLogout->assertCookie('lat_locale', 'qu');
        $resLogout->assertSessionHas('info', __('lat.auth_logout', [], 'qu'));
        $this->assertGuest();

        $this->withCookie('lat_locale', 'qu')
            ->get('/')
            ->assertStatus(200)
            ->assertSee('<html lang="qu" data-server-locale="qu">', false)
            ->assertSee(__('lat.auth_logout', [], 'qu'));

        $this->withCookie('lat_locale', 'qu')
            ->get('/login')
            ->assertStatus(200)
            ->assertSee('<html lang="qu" data-server-locale="qu">', false);
    }

    public function test_exportar_excel_en_quechua_genera_ooxml_y_sumif_coherente(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        $this->get('/idioma/qu')->assertStatus(302)->assertCookie('lat_locale', 'qu');

        $excelRes = $this->actingAs($admin)
            ->withCookie('lat_locale', 'qu')
            ->get('/panel/exportar-excel');
        $excelRes->assertStatus(200);
        $excelRes->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $bytes = $excelRes->getContent();
        $this->assertSame("PK\x03\x04", substr($bytes, 0, 4));

        $tmp = tempnam(sys_get_temp_dir(), 'lat_qu_');
        $this->assertNotFalse($tmp);
        file_put_contents($tmp, $bytes);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp) === true);

        try {
            $workbook = $zip->getFromName('xl/workbook.xml');
            $sheet1 = $zip->getFromName('xl/worksheets/sheet1.xml');
            $sheet2 = $zip->getFromName('xl/worksheets/sheet2.xml');
            $sheet3 = $zip->getFromName('xl/worksheets/sheet3.xml');
            $sheet4 = $zip->getFromName('xl/worksheets/sheet4.xml');

            $this->assertIsString($workbook);
            $this->assertIsString($sheet1);
            $this->assertIsString($sheet2);
            $this->assertIsString($sheet3);
            $this->assertIsString($sheet4);

            $this->assertStringContainsString(__('lat.excel_sheet_settlement', [], 'qu'), $workbook);
            $this->assertStringContainsString(__('lat.excel_sheet_intake', [], 'qu'), $workbook);
            $this->assertStringContainsString(__('lat.excel_sheet_movements', [], 'qu'), $workbook);
            $this->assertStringContainsString(__('lat.excel_sheet_plants', [], 'qu'), $workbook);

            $this->assertStringContainsString('QUINCENA PAGAMANTAPACHA WILLAKUY', $sheet1);
            $this->assertStringContainsString(__('lat.excel_totals', [], 'qu'), $sheet1);
            $this->assertStringContainsString("SAPA P'UNCHAW LICHI HUÑUY", html_entity_decode($sheet2, ENT_XML1 | ENT_QUOTES, 'UTF-8'));
            $this->assertStringContainsString('KISU RUWANA WASIKUNA TUPACHIY', $sheet4);

            $sheet3Decoded = html_entity_decode($sheet3, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $ventaLabelQu = __('lat.mov_venta_producto', [], 'qu');
            $this->assertSame('Lichi ruru rantikuy', $ventaLabelQu);
            $this->assertStringContainsString($ventaLabelQu, $sheet3Decoded);
            $this->assertStringContainsString('SUMIF(B5:B', $sheet3Decoded);
            $this->assertStringContainsString("\"{$ventaLabelQu}\"", $sheet3Decoded);
            $this->assertStringContainsString('"<>'.$ventaLabelQu.'"', $sheet3Decoded);
            $this->assertStringContainsString(__('lat.excel_total_income', [], 'qu'), $sheet3Decoded);
            $this->assertStringContainsString(__('lat.excel_net_balance', [], 'qu'), $sheet3Decoded);
        } finally {
            $zip->close();
            @unlink($tmp);
        }
    }
}
