<?php

namespace Tests\Feature;

use App\Models\Acopio;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\ReporteExcel;
use App\Models\Tina;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class LatAuditoriaFinalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_papelera_plantas_eliminar_restaurar_purga_30_dias_y_proteccion_ultima_planta(): void
    {
        $admin = User::where('usuario', 'rodrigo')->firstOrFail();
        $planta1 = Planta::where('codigo', 'PLT-01')->firstOrFail();
        $planta2 = Planta::where('codigo', 'PLT-02')->firstOrFail();
        $planta3 = Planta::where('codigo', 'PLT-03')->firstOrFail();

        // Cambiar sesión a planta2 y enviarla al tacho
        $this->actingAs($admin)->post('/panel/cambiar-planta', ['planta_id' => $planta2->id])->assertRedirect();
        $this->actingAs($admin)->delete("/panel/admin/plantas/{$planta2->id}")->assertRedirect();

        $this->assertSoftDeleted('plantas', ['id' => $planta2->id]);
        $this->assertNotSame($planta2->id, session('planta_activa_id'));

        $trash = Planta::onlyTrashed()->findOrFail($planta2->id);
        $this->assertSame($admin->id, $trash->eliminado_por);
        $this->assertGreaterThanOrEqual(29, $trash->diasRestantesPapelera());

        // Restaurar planta2 y verificar que sus tinas y acopios siguen intactos
        $this->actingAs($admin)->post("/panel/admin/plantas/{$planta2->id}/restaurar")->assertRedirect();
        $this->assertDatabaseHas('plantas', ['id' => $planta2->id, 'deleted_at' => null, 'activa' => true]);
        $this->assertGreaterThan(0, Tina::where('planta_id', $planta2->id)->count());

        // Enviar planta2 y planta3 al tacho; intentar borrar la última planta (planta1) debe bloquearse
        $this->actingAs($admin)->delete("/panel/admin/plantas/{$planta2->id}")->assertRedirect();
        $this->actingAs($admin)->delete("/panel/admin/plantas/{$planta3->id}")->assertRedirect();
        $this->actingAs($admin)->delete("/panel/admin/plantas/{$planta1->id}")->assertSessionHasErrors('planta');
        $this->assertDatabaseHas('plantas', ['id' => $planta1->id, 'deleted_at' => null]);

        // Purgar planta vencida > 30 días
        Planta::onlyTrashed()->where('id', $planta3->id)->update(['deleted_at' => Carbon::now()->subDays(31)]);
        $purgadas = Planta::purgarExpiradas();
        $this->assertSame(1, $purgadas);
        $this->assertDatabaseMissing('plantas', ['id' => $planta3->id]);

        // Eliminación definitiva manual de planta2
        $this->actingAs($admin)->delete("/panel/admin/plantas/{$planta2->id}/definitivo")->assertRedirect();
        $this->assertDatabaseMissing('plantas', ['id' => $planta2->id]);
    }

    public function test_crud_eliminar_tina_proveedor_y_usuario(): void
    {
        $admin = User::where('usuario', 'rodrigo')->firstOrFail();
        $planta1 = Planta::where('codigo', 'PLT-01')->firstOrFail();

        // Tina con lote activo NO se puede eliminar; tina sin lote SÍ (con modo operativo activo para admin)
        $tinaConLote = Tina::where('planta_id', $planta1->id)->whereHas('loteActivo')->firstOrFail();
        $this->actingAs($admin)
            ->withSession(['admin_modo_operativo' => true])
            ->delete("/panel/tinas/{$tinaConLote->id}")
            ->assertSessionHasErrors('tina');
        $this->assertDatabaseHas('tinas', ['id' => $tinaConLote->id]);

        $tinaLibre = Tina::create([
            'planta_id' => $planta1->id,
            'codigo' => 'TINA-DEL',
            'nombre' => 'Tina Descartable',
            'capacidad_litros' => 100,
            'tipo' => 'quesillo',
            'activa' => true,
        ]);
        $this->actingAs($admin)
            ->withSession(['admin_modo_operativo' => true])
            ->delete("/panel/tinas/{$tinaLibre->id}")
            ->assertRedirect();
        $this->assertDatabaseMissing('tinas', ['id' => $tinaLibre->id]);

        // Proveedor con acopios se desactiva para no perder contabilidad; sin acopios se elimina
        $provConHistorial = Proveedor::where('planta_id', $planta1->id)->firstOrFail();
        $this->actingAs($admin)
            ->withSession(['admin_modo_operativo' => true])
            ->delete("/panel/proveedor/{$provConHistorial->id}")
            ->assertRedirect();
        $this->assertFalse($provConHistorial->fresh()->activo);

        $provSinHistorial = Proveedor::create([
            'planta_id' => $planta1->id,
            'codigo' => 'PRV-TEMP',
            'nombre' => 'Productor Sin Entregas',
            'sector' => 'Anta',
            'activo' => true,
        ]);
        $this->actingAs($admin)
            ->withSession(['admin_modo_operativo' => true])
            ->delete("/panel/proveedor/{$provSinHistorial->id}")
            ->assertRedirect();
        $this->assertDatabaseMissing('proveedores', ['id' => $provSinHistorial->id]);

        // Eliminar usuario (no puede auto-eliminarse)
        $this->actingAs($admin)->delete("/panel/admin/usuarios/{$admin->id}")->assertSessionHasErrors('usuario');
        $mateo = User::where('usuario', 'javier.ruta')->firstOrFail();
        $this->actingAs($admin)->delete("/panel/admin/usuarios/{$mateo->id}")->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $mateo->id]);
    }

    public function test_separacion_rol_admin_no_realiza_acopio_ni_opera_tina(): void
    {
        $admin = User::where('usuario', 'rodrigo')->firstOrFail();
        $prov = Proveedor::where('planta_id', $admin->planta_id)->firstOrFail();
        $tina = Tina::where('planta_id', $admin->planta_id)->firstOrFail();

        $this->assertFalse($admin->tienePermiso('acopio.crear'));
        $this->assertFalse($admin->tienePermiso('tinas.operar'));

        $this->actingAs($admin)->post('/panel/acopio', [
            'proveedor_id' => $prov->id,
            'litros' => 25,
        ])->assertStatus(403);

        $this->actingAs($admin)->post("/panel/tinas/{$tina->id}/lote", [
            'litros' => 50,
        ])->assertStatus(403);
    }

    public function test_fecha_acopio_filtros_y_validacion_minima_litros_para_evitar_gotita(): void
    {
        $recolector = User::where('usuario', 'javier.ruta')->firstOrFail();
        $prov = Proveedor::where('planta_id', $recolector->planta_id)->where('codigo', 'PRV-01')->firstOrFail();

        // Rechaza "una gotita" (0.1 L y 0.5 L < min:1)
        $this->actingAs($recolector)->post('/panel/acopio', [
            'proveedor_id' => $prov->id,
            'litros' => 0.1,
        ])->assertSessionHasErrors('litros');

        $this->actingAs($recolector)->post('/panel/acopio', [
            'proveedor_id' => $prov->id,
            'litros' => 0.5,
        ])->assertSessionHasErrors('litros');

        // Acepta >= 1.0 L con fecha específica
        $fechaAyer = Carbon::yesterday()->toDateString();
        $this->actingAs($recolector)->post('/panel/acopio', [
            'proveedor_id' => $prov->id,
            'litros' => 1.0,
            'fecha' => $fechaAyer,
            'metodo' => 'manual',
        ])->assertRedirect();

        $this->assertDatabaseHas('acopios', [
            'proveedor_id' => $prov->id,
            'litros' => 1.0,
        ]);

        // Filtros por productor, rango de fechas y litros
        $resFiltro = $this->actingAs($recolector)->get('/panel?'.http_build_query([
            'acopio_fecha_desde' => $fechaAyer,
            'acopio_fecha_hasta' => $fechaAyer,
            'acopio_proveedor_id' => $prov->id,
            'acopio_litros_min' => 1,
            'acopio_litros_max' => 5,
        ]));
        $resFiltro->assertStatus(200);
        $resFiltro->assertViewHas('acopiosHoy', fn ($col): bool => $col->count() === 1 && (float) $col->first()->litros === 1.0);
    }

    public function test_sincronizacion_offline_es_idempotente_por_uuid(): void
    {
        $recolector = User::where('usuario', 'javier.ruta')->firstOrFail();
        $prov = Proveedor::where('planta_id', $recolector->planta_id)->firstOrFail();
        $uuid = (string) Str::uuid();

        $payload = [
            'acopios' => [
                [
                    'uuid_offline' => $uuid,
                    'proveedor_id' => $prov->id,
                    'litros' => 24.5,
                    'fecha' => Carbon::today()->toDateString(),
                    'hora' => '06:25',
                    'metodo' => 'manual',
                ],
            ],
        ];

        $this->actingAs($recolector)->postJson('/panel/acopio/sync', $payload)
            ->assertStatus(200)
            ->assertJson(['ok' => true, 'sincronizados' => [$uuid], 'duplicados' => []]);

        // Reintento del mismo lote no debe duplicar
        $this->actingAs($recolector)->postJson('/panel/acopio/sync', $payload)
            ->assertStatus(200)
            ->assertJson(['ok' => true, 'sincronizados' => [], 'duplicados' => [$uuid]]);

        $this->assertSame(1, Acopio::where('uuid_offline', $uuid)->count());
    }

    public function test_envio_excel_ordenado_al_contador_descarga_y_revision(): void
    {
        Storage::fake('local');

        $encargado = User::where('usuario', 'andres.anta')->firstOrFail();
        $contador = User::where('usuario', 'contador.ricardo')->firstOrFail();

        $this->actingAs($encargado)->post('/panel/reportes-excel/enviar', [
            'destinatario_id' => $contador->id,
            'tipo_reporte' => 'ventas_productor',
            'fecha_desde' => Carbon::today()->subDays(7)->toDateString(),
            'fecha_hasta' => Carbon::today()->toDateString(),
            'notas' => 'Resumen ordenado de ventas y productores',
        ])->assertRedirect();

        $reporte = ReporteExcel::firstOrFail();
        $this->assertSame('enviado', $reporte->estado);
        Storage::disk('local')->assertExists($reporte->ruta_archivo);

        // El contador ve y descarga el .xlsx y lo marca revisado
        $this->actingAs($contador)->get('/panel')->assertStatus(200)->assertSee('Resumen ordenado de ventas y productores');

        $descarga = $this->actingAs($contador)->get("/panel/reportes-excel/{$reporte->id}/descargar");
        $descarga->assertStatus(200);
        $this->assertSame("PK\x03\x04", substr($descarga->getContent(), 0, 4));
        $this->assertNotNull($reporte->fresh()->leido_at);

        $this->actingAs($contador)->post("/panel/reportes-excel/{$reporte->id}/revisar")->assertRedirect();
        $this->assertSame('revisado', $reporte->fresh()->estado);
    }

    public function test_qr_panel_eliminado_y_anuncio_qr_publico_en_landing(): void
    {
        $admin = User::where('usuario', 'rodrigo')->firstOrFail();
        $planta1 = Planta::where('codigo', 'PLT-01')->firstOrFail();

        $this->actingAs($admin)->post('/panel/admin/qr', [
            'etiqueta' => 'Obsoleto',
            'cargo_asignado' => 'recolector_ruta',
            'planta_id' => $planta1->id,
        ])->assertNotFound();

        $this->get('/acceso-qr/token-demo')->assertNotFound();

        $panelRes = $this->actingAs($admin)->get('/panel');
        $panelRes->assertOk();
        $panelRes->assertDontSee('id="qr-svg-preview"', false);

        $landingRes = $this->get('/');
        $landingRes->assertOk();
        $landingRes->assertSee('id="anuncio-qr-registro"', false);
        $landingRes->assertSee('id="landing-qr-svg"', false);
        $landingRes->assertSee(route('registro'), false);
        $landingRes->assertSee('Puedes registrarte ahora');
    }

    public function test_optimizacion_consultas_panel_sin_n_mas_uno_excesivo(): void
    {
        $admin = User::where('usuario', 'rodrigo')->firstOrFail();

        DB::enableQueryLog();
        $this->actingAs($admin)->get('/panel')->assertStatus(200);
        $queriesBase = DB::getQueryLog();
        DB::disableQueryLog();

        // Verificamos que no existan funciones DATE() sobre columnas fecha
        foreach ($queriesBase as $q) {
            $this->assertStringNotContainsString('DATE(fecha)', strtoupper($q['query']));
        }

        // Agregar más acopios no debe incrementar el número de consultas (cero N+1)
        $prov = Proveedor::where('planta_id', $admin->planta_id)->firstOrFail();
        for ($i = 0; $i < 5; $i++) {
            Acopio::create([
                'planta_id' => $admin->planta_id,
                'proveedor_id' => $prov->id,
                'registrado_por' => $admin->id,
                'uuid_offline' => (string) Str::uuid(),
                'fecha' => Carbon::today()->toDateString(),
                'hora' => '08:00:00',
                'litros' => 15.0,
                'precio_litro' => 1.65,
                'metodo' => 'manual',
                'calidad' => 'buena',
                'destino' => 'queso_yogur',
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($admin)->get('/panel')->assertStatus(200);
        $queriesDespues = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertSame(count($queriesBase), count($queriesDespues));
        $this->assertLessThanOrEqual(42, count($queriesBase));
    }
}
