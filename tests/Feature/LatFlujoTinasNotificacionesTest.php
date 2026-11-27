<?php

namespace Tests\Feature;

use App\Models\Acopio;
use App\Models\Lote;
use App\Models\NotificacionPanel;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\Tina;
use App\Models\User;
use App\Services\TinaWorkflowService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class LatFlujoTinasNotificacionesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_recolector_ruta_tiene_acopio_editar_y_puede_editar_y_eliminar_acopios(): void
    {
        $recolector = User::where('usuario', 'mateo.ruta')->firstOrFail();
        $sinEditar = User::where('usuario', 'sabina.tina')->firstOrFail();
        $prov = Proveedor::where('planta_id', $recolector->planta_id)->firstOrFail();
        $acopio = Acopio::create([
            'planta_id' => $recolector->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $recolector->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '06:30:00',
            'litros' => 24.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'pendiente',
            'destino' => 'pendiente',
        ]);

        $this->assertTrue($recolector->tienePermiso('acopio.editar'));

        $this->actingAs($sinEditar)
            ->put("/panel/acopio/{$acopio->id}", [
                'proveedor_id' => $prov->id,
                'litros' => 30.0,
                'calidad' => 'pendiente',
                'metodo' => 'manual',
            ])
            ->assertForbidden();

        $resEdit = $this->actingAs($recolector)
            ->put("/panel/acopio/{$acopio->id}", [
                'proveedor_id' => $prov->id,
                'litros' => 32.5,
                'calidad' => 'pendiente',
                'metodo' => 'voz',
                'frase_dictada' => 'Don Eloy Condori, 32.5 litros',
                'hora' => '07:10',
            ]);

        $resEdit->assertRedirect();
        $resEdit->assertSessionHas('exito', __('lat.intake_updated'));

        $acopioFresh = $acopio->fresh();
        $this->assertSame(32.5, $acopioFresh->litros);
        $this->assertSame('voz', $acopioFresh->metodo);
        $this->assertSame('pendiente', $acopioFresh->calidad);
        $this->assertSame('pendiente', $acopioFresh->destino);

        $resDelete = $this->actingAs($recolector)->delete("/panel/acopio/{$acopio->id}");

        $resDelete->assertRedirect();
        $resDelete->assertSessionHas('exito', __('lat.intake_deleted'));
        $this->assertDatabaseMissing('acopios', ['id' => $acopio->id]);
    }

    public function test_acopio_manual_y_sync_guardan_calidad_y_destino_pendiente_y_se_reflejan_en_panel(): void
    {
        $recolector = User::where('usuario', 'mateo.ruta')->firstOrFail();
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $prov = Proveedor::where('planta_id', $recolector->planta_id)->where('codigo', 'PRV-01')->firstOrFail();
        $uuidManual = (string) Str::uuid();
        $uuidSync = (string) Str::uuid();

        $this->actingAs($recolector)->post('/panel/acopio', [
            'proveedor_id' => $prov->id,
            'litros' => 27.5,
            'metodo' => 'manual',
            'uuid_offline' => $uuidManual,
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
        ])->assertRedirect();

        $this->actingAs($recolector)->postJson('/panel/acopio/sync', [
            'acopios' => [[
                'uuid_offline' => $uuidSync,
                'proveedor_id' => $prov->id,
                'litros' => 19.0,
                'fecha' => Carbon::today()->toDateString(),
                'hora' => '08:15',
                'metodo' => 'voz',
                'frase_dictada' => 'Don Eloy Condori, 19 litros',
                'calidad' => 'buena',
                'destino' => 'queso_yogur',
            ]],
        ])->assertOk()->assertJson(['ok' => true, 'sincronizados' => [$uuidSync]]);

        $acopioManual = Acopio::where('uuid_offline', $uuidManual)->firstOrFail();
        $acopioSync = Acopio::where('uuid_offline', $uuidSync)->firstOrFail();

        $this->assertSame('pendiente', $acopioManual->calidad);
        $this->assertSame('pendiente', $acopioManual->destino);
        $this->assertNull($acopioManual->evaluado_en);

        $this->assertSame('pendiente', $acopioSync->calidad);
        $this->assertSame('pendiente', $acopioSync->destino);
        $this->assertNull($acopioSync->evaluado_en);

        $panelRes = $this->actingAs($admin)->get('/panel');

        $panelRes->assertOk();
        $panelRes->assertSee('Pendiente de prueba');
        $panelRes->assertSee(__('lat.quality_pending'));
        $panelRes->assertViewHas('dashboardData', function (array $data): bool {
            $eventoSync = $data['actividad_reciente']->firstWhere('titulo', 'Don Eloy Condori · 19.0 L');

            return $eventoSync !== null
                && $eventoSync->detalle === __('lat.quality_pending')
                && ! str_contains($eventoSync->detalle, 'Aprobada en recepción');
        });
    }

    public function test_calidad_sanitaria_tres_acciones_actualizan_y_emiten_notificaciones_a_tinas(): void
    {
        $this->travelTo(Carbon::today()->setTime(10, 0, 0));
        $evaluador = User::where('usuario', 'sabina.tina')->firstOrFail();
        $recolector = User::where('usuario', 'mateo.ruta')->firstOrFail();
        $prov = Proveedor::where('planta_id', $evaluador->planta_id)->where('codigo', 'PRV-01')->firstOrFail();

        $crearPendiente = fn (float $litros): Acopio => Acopio::create([
            'planta_id' => $evaluador->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $recolector->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '06:40:00',
            'litros' => $litros,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'pendiente',
            'destino' => 'pendiente',
        ]);

        $acBuena = $crearPendiente(31.0);
        $acAcida = $crearPendiente(22.0);
        $acDescartada = $crearPendiente(14.5);

        $this->actingAs($recolector)
            ->post("/panel/calidad/{$acBuena->id}", ['calidad' => 'buena'])
            ->assertForbidden();

        // 1. Aprobar (buena)
        $this->actingAs($evaluador)
            ->post("/panel/calidad/{$acBuena->id}", ['calidad' => 'buena'])
            ->assertRedirect()
            ->assertSessionHas('exito', __('lat.quality_classified_good'));

        $acBuenaFresh = $acBuena->fresh();
        $this->assertSame('buena', $acBuenaFresh->calidad);
        $this->assertSame('queso_yogur', $acBuenaFresh->destino);
        $this->assertNotNull($acBuenaFresh->evaluado_en);
        $this->assertTrue($acBuenaFresh->evaluado_en->equalTo(Carbon::now()));

        $notifBuena = NotificacionPanel::where('planta_id', $evaluador->planta_id)
            ->where('tipo', 'calidad_aprobada')
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('tinas', $notifBuena->modulo_destino);
        $this->assertSame($evaluador->id, $notifBuena->creado_por);

        // 2. Desviar a quesillo (acida)
        $this->actingAs($evaluador)
            ->post("/panel/calidad/{$acAcida->id}", ['calidad' => 'acida'])
            ->assertRedirect()
            ->assertSessionHas('exito', __('lat.quality_classified_acid'));

        $acAcidaFresh = $acAcida->fresh();
        $this->assertSame('acida', $acAcidaFresh->calidad);
        $this->assertSame('quesillo_4_dias', $acAcidaFresh->destino);
        $this->assertNotNull($acAcidaFresh->evaluado_en);

        $notifAcida = NotificacionPanel::where('planta_id', $evaluador->planta_id)
            ->where('tipo', 'calidad_acida')
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('tinas', $notifAcida->modulo_destino);

        // 3. Descartar (descartada -> elimina completamente el acopio y notifica)
        $this->actingAs($evaluador)
            ->post("/panel/calidad/{$acDescartada->id}", ['calidad' => 'descartada'])
            ->assertRedirect()
            ->assertSessionHas('exito', __('lat.quality_classified_discarded'));

        $this->assertDatabaseMissing('acopios', ['id' => $acDescartada->id]);

        $notifDesc = NotificacionPanel::where('planta_id', $evaluador->planta_id)
            ->where('tipo', 'calidad_descartada')
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('tinas', $notifDesc->modulo_destino);
        $this->assertSame(14.5, (float) ($notifDesc->datos['litros'] ?? 0));
    }

    #[TestWith(['buena'])]
    #[TestWith(['acida'])]
    #[TestWith(['descartada'])]
    public function test_calidad_sanitaria_bloquea_reclasificar_o_descartar_acopio_ya_asignado_a_lote(string $accion): void
    {
        $evaluador = User::where('usuario', 'sabina.tina')->firstOrFail();
        $prov = Proveedor::where('planta_id', $evaluador->planta_id)->firstOrFail();
        $tina = Tina::where('planta_id', $evaluador->planta_id)->where('codigo', 'TINA-03')->firstOrFail();

        $lote = Lote::create([
            'planta_id' => $evaluador->planta_id,
            'tina_id' => $tina->id,
            'codigo' => 'LOTE-LOCK-01',
            'fecha' => Carbon::today()->toDateString(),
            'litros' => 30.0,
            'temperatura_actual' => 72.0,
            'cuajo_ml' => 0.75,
            'sal_kg' => 0.54,
            'cultivo_g' => 0.30,
            'moldes_producidos' => 0,
            'estado' => 'pasteurizando',
            'duracion_minutos' => 45,
            'iniciado_en' => Carbon::now(),
        ]);

        $acopioEnLote = Acopio::create([
            'planta_id' => $evaluador->planta_id,
            'lote_id' => $lote->id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $evaluador->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '06:50:00',
            'litros' => 30.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => Carbon::now(),
        ]);

        $res = $this->actingAs($evaluador)->post("/panel/calidad/{$acopioEnLote->id}", [
            'calidad' => $accion,
        ]);

        $res->assertSessionHasErrors([
            'calidad' => __('lat.quality_locked_in_batch'),
        ]);
        $this->assertModelExists($acopioEnLote);
        $this->assertSame('buena', $acopioEnLote->fresh()->calidad);
        $this->assertSame($lote->id, $acopioEnLote->fresh()->lote_id);
    }

    public function test_iniciar_lote_con_multiples_acopios_suma_litros_enlaza_acopios_y_guarda_receta_manual(): void
    {
        $this->travelTo(Carbon::today()->setTime(10, 15, 0));
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $operador->planta_id)->where('codigo', 'TINA-03')->firstOrFail();
        $prov = Proveedor::where('planta_id', $operador->planta_id)->firstOrFail();

        $acBuena = Acopio::create([
            'planta_id' => $operador->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $operador->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '06:20:00',
            'litros' => 42.5,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => Carbon::now(),
        ]);

        $acAcida = Acopio::create([
            'planta_id' => $operador->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $operador->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '06:35:00',
            'litros' => 17.5,
            'precio_litro' => 1.65,
            'metodo' => 'voz',
            'calidad' => 'acida',
            'destino' => 'quesillo_4_dias',
            'evaluado_en' => Carbon::now(),
        ]);

        $res = $this->actingAs($operador)->post("/panel/tinas/{$tinaLibre->id}/lote", [
            'acopio_ids' => [$acBuena->id, $acAcida->id],
            'receta_base' => 'personalizada',
            'receta_nombre' => 'Queso Mixto Especial',
            'cuajo_ml' => 3.15,
            'sal_kg' => 1.45,
            'cultivo_g' => 0.95,
            'temperatura_actual' => 72.0,
            'duracion_minutos' => 55,
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('exito', __('lat.batch_started', ['vat' => $tinaLibre->nombre]));

        $lote = Lote::where('tina_id', $tinaLibre->id)->latest('id')->firstOrFail();
        $this->assertSame(60.0, $lote->litros);
        $this->assertSame(3.15, $lote->cuajo_ml);
        $this->assertSame(1.45, $lote->sal_kg);
        $this->assertSame(0.95, $lote->cultivo_g);
        $this->assertSame('Queso Mixto Especial', $lote->receta_nombre);
        $this->assertSame(55, $lote->duracion_minutos);
        $this->assertSame('pasteurizando', $lote->estado);
        $this->assertFalse($lote->alerta_fin_enviada);
        $this->assertNotNull($lote->iniciado_en);
        $this->assertTrue($lote->iniciado_en->equalTo(Carbon::now()));

        $this->assertSame($lote->id, $acBuena->fresh()->lote_id);
        $this->assertSame($lote->id, $acAcida->fresh()->lote_id);
    }

    public function test_iniciar_lote_rechaza_acopios_vacios(): void
    {
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $operador->planta_id)->where('codigo', 'TINA-03')->firstOrFail();

        $res = $this->actingAs($operador)->post("/panel/tinas/{$tinaLibre->id}/lote", [
            'acopio_ids' => [],
            'temperatura_actual' => 72.0,
        ]);

        $res->assertSessionHasErrors('acopio_ids');
        $this->assertNull($tinaLibre->fresh()->loteActivo);
    }

    public function test_iniciar_lote_rechaza_acopio_pendiente(): void
    {
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $operador->planta_id)->where('codigo', 'TINA-03')->firstOrFail();
        $prov = Proveedor::where('planta_id', $operador->planta_id)->firstOrFail();

        $acPendiente = Acopio::create([
            'planta_id' => $operador->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $operador->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '06:50:00',
            'litros' => 25.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'pendiente',
            'destino' => 'pendiente',
        ]);

        $res = $this->actingAs($operador)->post("/panel/tinas/{$tinaLibre->id}/lote", [
            'acopio_ids' => [$acPendiente->id],
        ]);

        $res->assertSessionHasErrors([
            'acopio_ids' => __('lat.vat_milk_invalid_error'),
        ]);
        $this->assertNull($tinaLibre->fresh()->loteActivo);
        $this->assertNull($acPendiente->fresh()->lote_id);
    }

    public function test_iniciar_lote_rechaza_acopio_ya_usado_en_otro_lote(): void
    {
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $operador->planta_id)->where('codigo', 'TINA-03')->firstOrFail();
        $loteExistente = Lote::where('planta_id', $operador->planta_id)->enProceso()->firstOrFail();
        $prov = Proveedor::where('planta_id', $operador->planta_id)->firstOrFail();

        $acUsado = Acopio::create([
            'planta_id' => $operador->planta_id,
            'lote_id' => $loteExistente->id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $operador->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '07:00:00',
            'litros' => 30.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => Carbon::now(),
        ]);

        $res = $this->actingAs($operador)->post("/panel/tinas/{$tinaLibre->id}/lote", [
            'acopio_ids' => [$acUsado->id],
        ]);

        $res->assertSessionHasErrors([
            'acopio_ids' => __('lat.vat_milk_invalid_error'),
        ]);
        $this->assertNull($tinaLibre->fresh()->loteActivo);
        $this->assertSame($loteExistente->id, $acUsado->fresh()->lote_id);
    }

    public function test_iniciar_lote_rechaza_acopio_de_otra_planta(): void
    {
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $operador->planta_id)->where('codigo', 'TINA-03')->firstOrFail();
        $plantaVecina = Planta::where('codigo', 'PLT-02')->firstOrFail();
        $acopioVecino = Acopio::where('planta_id', $plantaVecina->id)->where('calidad', 'buena')->firstOrFail();

        $res = $this->actingAs($operador)->post("/panel/tinas/{$tinaLibre->id}/lote", [
            'acopio_ids' => [$acopioVecino->id],
        ]);

        $res->assertSessionHasErrors([
            'acopio_ids' => __('lat.vat_milk_invalid_error'),
        ]);
        $this->assertNull($tinaLibre->fresh()->loteActivo);
        $this->assertNull($acopioVecino->fresh()->lote_id);
    }

    public function test_iniciar_lote_rechaza_suma_que_supera_capacidad_de_tina(): void
    {
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $operador->planta_id)->where('codigo', 'TINA-03')->firstOrFail();
        $prov = Proveedor::where('planta_id', $operador->planta_id)->firstOrFail();

        $ac1 = Acopio::create([
            'planta_id' => $operador->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $operador->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '06:10:00',
            'litros' => 95.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => Carbon::now(),
        ]);

        $ac2 = Acopio::create([
            'planta_id' => $operador->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $operador->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '06:15:00',
            'litros' => 70.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => Carbon::now(),
        ]);

        $res = $this->actingAs($operador)->post("/panel/tinas/{$tinaLibre->id}/lote", [
            'acopio_ids' => [$ac1->id, $ac2->id],
        ]);

        $res->assertSessionHasErrors('acopio_ids');
        $this->assertNull($tinaLibre->fresh()->loteActivo);
        $this->assertNull($ac1->fresh()->lote_id);
        $this->assertNull($ac2->fresh()->lote_id);
    }

    public function test_actualizar_solo_temperatura_preserva_receta_manual_y_temporizador_y_editar_receta_reinicia_cronometro(): void
    {
        $this->travelTo(Carbon::today()->setTime(10, 30, 0));
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $operador->planta_id)->where('codigo', 'TINA-03')->firstOrFail();
        $inicioOriginal = Carbon::now()->subMinutes(15);

        $lote = Lote::create([
            'planta_id' => $operador->planta_id,
            'tina_id' => $tinaLibre->id,
            'codigo' => 'LOTE-MANUAL-01',
            'fecha' => Carbon::today()->toDateString(),
            'litros' => 80.0,
            'temperatura_actual' => 72.0,
            'cuajo_ml' => 5.10,
            'sal_kg' => 2.30,
            'cultivo_g' => 1.75,
            'moldes_producidos' => 0,
            'estado' => 'pasteurizando',
            'receta_nombre' => 'Receta Manual Base',
            'duracion_minutos' => 50,
            'iniciado_en' => $inicioOriginal,
            'alerta_fin_enviada' => true,
        ]);

        // 1. Actualizar únicamente temperatura preserva cuajo/sal/cultivo manuales y no reinicia temporizador
        $this->actingAs($operador)->post("/panel/tina/{$lote->id}", [
            'temperatura' => 38.5,
        ])->assertRedirect();

        $loteTrasTemp = $lote->fresh();
        $this->assertSame('lista_para_cuajo', $loteTrasTemp->estado);
        $this->assertSame(38.5, $loteTrasTemp->temperatura_actual);
        $this->assertSame(5.10, $loteTrasTemp->cuajo_ml);
        $this->assertSame(2.30, $loteTrasTemp->sal_kg);
        $this->assertSame(1.75, $loteTrasTemp->cultivo_g);
        $this->assertSame(50, $loteTrasTemp->duracion_minutos);
        $this->assertTrue($loteTrasTemp->iniciado_en->equalTo($inicioOriginal));

        // 2. Actualizar explícitamente receta y duración persiste valores y reinicia el cronómetro
        $this->travel(5)->minutes();
        $ahoraReinicio = Carbon::now();

        $this->actingAs($operador)->post("/panel/tina/{$lote->id}", [
            'cuajo_ml' => 4.44,
            'sal_kg' => 2.22,
            'cultivo_g' => 1.11,
            'duracion_minutos' => 35,
        ])->assertRedirect();

        $loteEditado = $lote->fresh();
        $this->assertSame(4.44, $loteEditado->cuajo_ml);
        $this->assertSame(2.22, $loteEditado->sal_kg);
        $this->assertSame(1.11, $loteEditado->cultivo_g);
        $this->assertSame(35, $loteEditado->duracion_minutos);
        $this->assertFalse($loteEditado->alerta_fin_enviada);
        $this->assertTrue($loteEditado->iniciado_en->equalTo($ahoraReinicio));
    }

    public function test_finalizar_lote_emite_notificacion_devuelta_por_poll_y_marcable_como_leida(): void
    {
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $receptor = User::where('usuario', 'sabina.tina')->firstOrFail();
        $lote = Lote::with('tina')
            ->where('planta_id', $operador->planta_id)
            ->enProceso()
            ->firstOrFail();

        $resFin = $this->actingAs($operador)->post("/panel/tina/{$lote->id}", [
            'moldes' => 16,
        ]);

        $resFin->assertRedirect();
        $resFin->assertSessionHas('exito', __('lat.batch_finished'));

        $loteFresh = $lote->fresh();
        $this->assertSame('terminado', $loteFresh->estado);
        $this->assertSame(16, $loteFresh->moldes_producidos);

        $notif = NotificacionPanel::where('planta_id', $operador->planta_id)
            ->where('tipo', 'tina_finalizada')
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('tinas', $notif->modulo_destino);
        $this->assertSame($lote->id, $notif->datos['lote_id'] ?? null);

        $pollRes = $this->actingAs($receptor)->getJson('/panel/notificaciones/poll');
        $pollRes->assertOk()
            ->assertJsonPath('no_leidas', 1)
            ->assertJsonFragment([
                'id' => $notif->id,
                'tipo' => 'tina_finalizada',
                'leida' => false,
            ]);

        $leerRes = $this->actingAs($receptor)->postJson('/panel/notificaciones/leer', [
            'notificacion_id' => $notif->id,
        ]);
        $leerRes->assertOk()
            ->assertJson([
                'ok' => true,
                'no_leidas' => 0,
            ]);

        $this->assertTrue($notif->fresh()->estaLeidaPor($receptor->id));

        $pollDespues = $this->actingAs($receptor)->getJson('/panel/notificaciones/poll');
        $pollDespues->assertOk()
            ->assertJsonPath('no_leidas', 0)
            ->assertJsonFragment([
                'id' => $notif->id,
                'leida' => true,
            ]);

        // Marcar notificación de otra planta falla con 404
        $planta2 = Planta::where('codigo', 'PLT-02')->firstOrFail();
        $notifAjena = NotificacionPanel::create([
            'planta_id' => $planta2->id,
            'tipo' => 'tina_finalizada',
            'modulo_destino' => 'tinas',
            'titulo' => 'Ajena',
            'mensaje' => 'No visible en planta 1',
            'leida_por' => [],
        ]);

        $this->actingAs($operador)->postJson('/panel/notificaciones/leer', [
            'notificacion_id' => $notifAjena->id,
        ])->assertNotFound();
    }

    public function test_cronometro_vencido_dispara_notificacion_una_sola_vez_de_forma_idempotente(): void
    {
        $this->freezeTime();
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $operador->planta_id)->where('codigo', 'TINA-03')->firstOrFail();

        $lote = Lote::create([
            'planta_id' => $operador->planta_id,
            'tina_id' => $tinaLibre->id,
            'codigo' => 'LOTE-TIMER-EXP',
            'fecha' => Carbon::today()->toDateString(),
            'litros' => 90.0,
            'temperatura_actual' => 45.0,
            'cuajo_ml' => 2.25,
            'sal_kg' => 1.62,
            'cultivo_g' => 0.90,
            'moldes_producidos' => 0,
            'estado' => 'enfriando',
            'duracion_minutos' => 30,
            'iniciado_en' => Carbon::now()->subMinutes(45),
            'alerta_fin_enviada' => false,
        ]);

        $this->actingAs($operador)->getJson('/panel/notificaciones/poll')->assertOk();
        $this->actingAs($operador)->getJson('/panel/notificaciones/poll')->assertOk();

        $this->assertTrue($lote->fresh()->alerta_fin_enviada);
        $this->assertSame(
            1,
            NotificacionPanel::where('planta_id', $operador->planta_id)
                ->where('tipo', 'tina_tiempo_cumplido')
                ->count()
        );
    }

    public function test_personalizar_recetas_base_persiste_en_planta_y_rechaza_usuarios_sin_permiso(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $sinPermiso = User::where('usuario', 'sabina.tina')->firstOrFail();

        $payload = [
            'recetas' => [
                'principal_queso' => [
                    'cuajo_x_100l' => 3.2,
                    'sal_x_100l' => 2.1,
                    'cultivo_x_100l' => 1.4,
                    'temp_inicial' => 74.0,
                    'duracion_minutos' => 60,
                ],
                'quesillo' => [
                    'cuajo_x_100l' => 2.4,
                    'sal_x_100l' => 1.6,
                    'cultivo_x_100l' => 0.9,
                    'litros_x_molde' => 7.5,
                    'temp_inicial' => 69.0,
                    'duracion_minutos' => 35,
                ],
            ],
        ];

        $this->actingAs($sinPermiso)
            ->put('/panel/tinas/recetas-base', $payload)
            ->assertForbidden();

        $res = $this->actingAs($admin)
            ->withSession(['admin_modo_operativo' => true])
            ->put('/panel/tinas/recetas-base', $payload);

        $res->assertRedirect();
        $res->assertSessionHas('exito', __('lat.base_recipes_updated'));

        $plantaFresh = Planta::findOrFail($admin->planta_id);
        $recetas = $plantaFresh->obtenerRecetasBase();
        $this->assertSame(3.2, $recetas['principal_queso']['cuajo_x_100l']);
        $this->assertSame(2.1, $recetas['principal_queso']['sal_x_100l']);
        $this->assertSame(1.4, $recetas['principal_queso']['cultivo_x_100l']);
        $this->assertSame(9.0, $recetas['principal_queso']['litros_x_molde']);
        $this->assertSame(74.0, $recetas['principal_queso']['temp_inicial']);
        $this->assertSame(60, $recetas['principal_queso']['duracion_minutos']);
        $this->assertSame(7.5, $recetas['quesillo']['litros_x_molde']);

        // Verificar que la dosificación usa la nueva receta base al iniciar un lote sin sobreescribir insumos
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $operador->planta_id)->where('codigo', 'TINA-03')->firstOrFail();
        $prov = Proveedor::where('planta_id', $operador->planta_id)->firstOrFail();
        $ac = Acopio::create([
            'planta_id' => $operador->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $operador->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '07:00:00',
            'litros' => 100.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => Carbon::now(),
        ]);

        $this->actingAs($operador)->post("/panel/tinas/{$tinaLibre->id}/lote", [
            'acopio_ids' => [$ac->id],
            'receta_base' => 'principal_queso',
        ])->assertRedirect();

        $lote = Lote::where('tina_id', $tinaLibre->id)->latest('id')->firstOrFail();
        $this->assertSame(3.2, $lote->cuajo_ml);
        $this->assertSame(2.1, $lote->sal_kg);
        $this->assertSame(1.4, $lote->cultivo_g);
        $this->assertSame(60, $lote->duracion_minutos);
    }

    public function test_tina_con_tipo_texto_libre_persiste_y_renderiza_sin_fuga_de_clave_i18n(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['admin_modo_operativo' => true])
            ->post('/panel/tinas', [
                'codigo' => '08',
                'nombre' => 'Tina Mantequillera',
                'capacidad_litros' => 120,
                'tipo' => 'Mantequilla artesanal',
            ])
            ->assertRedirect();

        $tina = Tina::where('planta_id', $admin->planta_id)
            ->where('codigo', 'TINA-08')
            ->firstOrFail();
        $this->assertSame('Mantequilla artesanal', $tina->tipo);
        $this->assertFalse($tina->esTipoPreestablecido());

        $panelRes = $this->actingAs($admin)->get('/panel');

        $panelRes->assertOk();
        $panelRes->assertSee('Mantequilla artesanal');
        $panelRes->assertDontSee('tina.type_Mantequilla', false);
    }

    #[TestWith(['en', '09', 'VAT-09'])]
    #[TestWith(['es', '10', 'TINA-10'])]
    #[TestWith(['qu', '11', 'TINA-11'])]
    public function test_prefijo_tina_usa_vat_en_ingles_y_tina_en_espanol_y_quechua(string $locale, string $sufijo, string $codigoEsperado): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['admin_modo_operativo' => true])
            ->withCookie('lat_locale', $locale)
            ->post('/panel/tinas', [
                'codigo' => $sufijo,
                'nombre' => "Tina {$codigoEsperado}",
                'capacidad_litros' => 200,
                'tipo' => 'principal_queso',
            ])
            ->assertRedirect();

        $tina = Tina::where('planta_id', $admin->planta_id)
            ->where('codigo', $codigoEsperado)
            ->firstOrFail();
        $this->assertSame($codigoEsperado, $tina->codigo);

        $panelRes = $this->actingAs($admin)
            ->withCookie('lat_locale', $locale)
            ->get('/panel');
        $panelRes->assertOk();
        $panelRes->assertSee($codigoEsperado);
    }

    public function test_tina_workflow_service_defaults_permanecen_consistentes(): void
    {
        $defaults = TinaWorkflowService::recetasDefault();

        $this->assertSame(['principal_queso', 'quesillo', 'yogur', 'multiproposito'], array_keys($defaults));
        $this->assertSame(45, $defaults['principal_queso']['duracion_minutos']);
    }
}
