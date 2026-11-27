<?php

namespace Tests\Feature;

use App\Models\Acopio;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\Tina;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class LatObservacionesPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_acopio_por_voz_flashea_aviso_y_renderiza_controles_de_edicion_y_eliminacion(): void
    {
        $encargado = User::where('usuario', 'carlos.anta')->firstOrFail();
        $prov = Proveedor::where('planta_id', $encargado->planta_id)
            ->where('codigo', 'PRV-01')
            ->firstOrFail();

        $postRes = $this->actingAs($encargado)
            ->from('/panel')
            ->post('/panel/acopio', [
                'proveedor_id' => $prov->id,
                'litros' => 35.0,
                'metodo' => 'voz',
                'frase_dictada' => 'Don Eloy Condori, 35 litros',
            ]);

        $postRes->assertRedirect('/panel');
        $postRes->assertSessionHas('exito', __('lat.intake_voice_auto_created', [
            'producer' => 'Don Eloy Condori',
            'liters' => '35.0',
        ]));
        $postRes->assertSessionHas('acopio_voz_reciente');

        $acopio = Acopio::where('planta_id', $encargado->planta_id)
            ->where('proveedor_id', $prov->id)
            ->where('metodo', 'voz')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(35.0, (float) $acopio->litros);
        $this->assertSame('Don Eloy Condori, 35 litros', $acopio->frase_dictada);

        $flashVoz = session('acopio_voz_reciente');
        $this->assertIsArray($flashVoz);
        $this->assertSame($acopio->id, $flashVoz['id']);
        $this->assertSame('Don Eloy Condori', $flashVoz['proveedor_nombre']);
        $this->assertSame(35.0, (float) $flashVoz['litros']);
        $this->assertSame('Don Eloy Condori, 35 litros', $flashVoz['frase_dictada']);

        $panelRes = $this->actingAs($encargado)->get('/panel');

        $panelRes->assertOk();
        $panelRes->assertSee('id="aviso-acopio-voz"', false);
        $panelRes->assertDontSee('id="aviso-acopio-voz" class="alerta alerta-verde acopio-voz-aviso" role="status" aria-live="polite" hidden', false);
        $panelRes->assertSee('Don Eloy Condori · 35.0 L');
        $panelRes->assertSee('Don Eloy Condori, 35 litros');
        $panelRes->assertSee('id="aviso-acopio-voz-acciones"', false);
        $panelRes->assertSee('data-id="'.$acopio->id.'"', false);
        $panelRes->assertSee('data-frase="Don Eloy Condori, 35 litros"', false);
        $panelRes->assertSee(route('panel.acopio.delete', $acopio->id), false);
    }

    public function test_acopio_manual_no_flashea_aviso_voz_y_usuario_sin_editar_no_ve_acciones_en_aviso(): void
    {
        $recolector = User::where('usuario', 'mateo.ruta')->firstOrFail();
        $prov = Proveedor::where('planta_id', $recolector->planta_id)
            ->where('codigo', 'PRV-01')
            ->firstOrFail();

        $manualRes = $this->actingAs($recolector)
            ->from('/panel')
            ->post('/panel/acopio', [
                'proveedor_id' => $prov->id,
                'litros' => 20.0,
                'metodo' => 'manual',
            ]);

        $manualRes->assertRedirect('/panel');
        $manualRes->assertSessionHas('exito', __('lat.intake_created'));
        $manualRes->assertSessionMissing('acopio_voz_reciente');

        $vozRes = $this->actingAs($recolector)
            ->from('/panel')
            ->post('/panel/acopio', [
                'proveedor_id' => $prov->id,
                'litros' => 18.0,
                'metodo' => 'voz',
                'frase_dictada' => 'Don Eloy Condori, 18 litros',
            ]);

        $vozRes->assertRedirect('/panel');
        $vozRes->assertSessionHas('acopio_voz_reciente');

        $acopioMateo = Acopio::where('planta_id', $recolector->planta_id)
            ->where('registrado_por', $recolector->id)
            ->where('metodo', 'voz')
            ->latest('id')
            ->firstOrFail();

        $panelRecolector = $this->actingAs($recolector)->get('/panel');

        $panelRecolector->assertOk();
        $panelRecolector->assertSee('Don Eloy Condori · 18.0 L');
        $panelRecolector->assertSee('Don Eloy Condori, 18 litros');
        $panelRecolector->assertSee('id="aviso-acopio-voz-acciones"', false);

        $this->actingAs($recolector)
            ->put("/panel/acopio/{$acopioMateo->id}", [
                'proveedor_id' => $prov->id,
                'litros' => 21.5,
                'calidad' => 'pendiente',
                'metodo' => 'voz',
                'frase_dictada' => 'Don Eloy Condori, 21.5 litros',
            ])
            ->assertRedirect();
        $this->assertSame(21.5, $acopioMateo->fresh()->litros);

        $this->actingAs($recolector)
            ->delete("/panel/acopio/{$acopioMateo->id}")
            ->assertRedirect();
        $this->assertDatabaseMissing('acopios', ['id' => $acopioMateo->id]);

        $soloCrear = User::create([
            'planta_id' => $recolector->planta_id,
            'name' => 'Operador Sin Editar',
            'usuario' => 'sin.editar.voz',
            'password' => 'secreto123',
            'cargo' => 'personalizado',
            'rango' => 'personalizado',
            'permisos' => ['acopio.ver', 'acopio.crear'],
            'activo' => true,
        ]);

        $vozSinEditar = $this->actingAs($soloCrear)
            ->from('/panel')
            ->post('/panel/acopio', [
                'proveedor_id' => $prov->id,
                'litros' => 18.0,
                'metodo' => 'voz',
                'frase_dictada' => 'Don Eloy Condori, 18 litros',
            ]);

        $vozSinEditar->assertRedirect('/panel');
        $vozSinEditar->assertSessionHas('acopio_voz_reciente');

        $panelRes = $this->actingAs($soloCrear)->get('/panel');

        $panelRes->assertOk();
        $panelRes->assertSee('Don Eloy Condori · 18.0 L');
        $panelRes->assertSee('Don Eloy Condori, 18 litros');
        $panelRes->assertDontSee('id="aviso-acopio-voz-acciones"', false);
    }

    public function test_editar_acopio_actualiza_preserva_y_limpia_frase_dictada_y_renderiza_confirmacion(): void
    {
        $encargado = User::where('usuario', 'carlos.anta')->firstOrFail();
        $prov = Proveedor::where('planta_id', $encargado->planta_id)
            ->where('codigo', 'PRV-01')
            ->firstOrFail();
        $acopio = Acopio::create([
            'planta_id' => $encargado->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $encargado->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '06:30:00',
            'litros' => 35.0,
            'precio_litro' => 1.65,
            'metodo' => 'voz',
            'frase_dictada' => 'Frase original',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
        ]);

        $this->actingAs($encargado)
            ->put("/panel/acopio/{$acopio->id}", [
                'proveedor_id' => $prov->id,
                'litros' => 40.0,
                'calidad' => 'buena',
                'metodo' => 'voz',
                'frase_dictada' => '  Don Eloy Condori, 40 litros  ',
            ])
            ->assertRedirect();

        $this->assertSame('Don Eloy Condori, 40 litros', $acopio->fresh()->frase_dictada);

        $this->actingAs($encargado)
            ->put("/panel/acopio/{$acopio->id}", [
                'proveedor_id' => $prov->id,
                'litros' => 42.0,
                'calidad' => 'buena',
                'metodo' => 'voz',
            ])
            ->assertRedirect();

        $this->assertSame('Don Eloy Condori, 40 litros', $acopio->fresh()->frase_dictada);

        $this->actingAs($encargado)
            ->put("/panel/acopio/{$acopio->id}", [
                'proveedor_id' => $prov->id,
                'litros' => 42.0,
                'calidad' => 'buena',
                'metodo' => 'manual',
                'frase_dictada' => '',
            ])
            ->assertRedirect();

        $this->assertNull($acopio->fresh()->frase_dictada);

        $panelRes = $this->actingAs($encargado)->get('/panel');

        $panelRes->assertOk();
        $panelRes->assertSee('<form method="POST" id="form-edit-acopio" action="" class="js-confirm-form" data-confirm-kind="edit_acopio">', false);
    }

    public function test_aviso_calidad_tinas_calcula_litros_de_hoy_incluso_con_filtros_de_acopio_activos(): void
    {
        $encargado = User::where('usuario', 'carlos.anta')->firstOrFail();
        $prov = Proveedor::where('planta_id', $encargado->planta_id)
            ->where('codigo', 'PRV-01')
            ->firstOrFail();

        Acopio::create([
            'planta_id' => $encargado->planta_id,
            'proveedor_id' => $prov->id,
            'registrado_por' => $encargado->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => Carbon::today()->toDateString(),
            'hora' => '08:15:00',
            'litros' => 12.5,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'calidad' => 'pendiente',
            'destino' => 'pendiente',
        ]);

        $res = $this->actingAs($encargado)->get('/panel?'.http_build_query([
            'acopio_fecha_desde' => Carbon::yesterday()->toDateString(),
            'acopio_fecha_hasta' => Carbon::yesterday()->toDateString(),
            'acopio_litros_min' => 999,
        ]));

        $res->assertOk();
        $res->assertViewHas('hayFiltrosAcopio', true);
        $res->assertViewHas('acopiosHoy', fn ($col): bool => $col->isEmpty());
        $res->assertViewHas('resumenLecheTinas', function (array $resumen): bool {
            return $resumen['optima']['litros'] === 145.5
                && $resumen['optima']['entregas'] === 4
                && $resumen['acida']['litros'] === 18.0
                && $resumen['acida']['entregas'] === 1
                && $resumen['pendiente']['litros'] === 12.5
                && $resumen['pendiente']['entregas'] === 1
                && $resumen['total_litros'] === 176.0;
        });

        $res->assertSee('id="aviso-calidad-tinas"', false);
        $res->assertSee('145.5 L');
        $res->assertSee('18.0 L');
        $res->assertSee('12.5 L');
    }

    public function test_tina_libre_renderiza_confirmacion_iniciar_lote_solo_para_quien_opera(): void
    {
        $operador = User::where('usuario', 'carlos.anta')->firstOrFail();
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $operador->planta_id)
            ->where('codigo', 'TINA-03')
            ->firstOrFail();

        $resOperador = $this->actingAs($operador)->get('/panel');

        $resOperador->assertOk();
        $resOperador->assertSee('class="btn btn-ember btn-bloque js-open-iniciar-tina"', false);
        $resOperador->assertSee('data-tina-codigo="TINA-03"', false);
        $resOperador->assertSee('data-action-url="'.route('panel.tinas.lote', $tinaLibre->id).'"', false);
        $resOperador->assertSee('id="modal-iniciar-tina"', false);
        $resOperador->assertSee('id="form-iniciar-tina"', false);
        $resOperador->assertSee('data-confirm-kind="start_batch"', false);

        $resAdmin = $this->actingAs($admin)->get('/panel');

        $resAdmin->assertOk();
        $resAdmin->assertDontSee('class="btn btn-ember btn-bloque js-open-iniciar-tina"', false);
        $resAdmin->assertDontSee('id="modal-iniciar-tina"', false);
        $resAdmin->assertDontSee('data-confirm-kind="start_batch"', false);
    }

    #[TestWith(['en'])]
    #[TestWith(['qu'])]
    public function test_eliminar_tina_activa_bloquea_con_mensaje_localizado_y_tina_libre_se_elimina(string $locale): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $tinaActiva = Tina::where('planta_id', $admin->planta_id)
            ->whereHas('loteActivo')
            ->firstOrFail();
        $tinaLibre = Tina::where('planta_id', $admin->planta_id)
            ->whereDoesntHave('loteActivo')
            ->firstOrFail();

        $panelRes = $this->actingAs($admin)->get('/panel');
        $panelRes->assertOk();
        $panelRes->assertSee('disabled aria-disabled="true"', false);
        $panelRes->assertSee(route('panel.tinas.delete', $tinaLibre->id), false);

        $resActive = $this->actingAs($admin)
            ->withCookie('lat_locale', $locale)
            ->delete("/panel/tinas/{$tinaActiva->id}");
        $resActive->assertSessionHasErrors([
            'tina' => __('lat.vat_delete_active_error', [], $locale),
        ]);
        $this->assertModelExists($tinaActiva);

        $resDeleteIdle = $this->actingAs($admin)
            ->withCookie('lat_locale', $locale)
            ->delete("/panel/tinas/{$tinaLibre->id}");
        $resDeleteIdle->assertRedirect();
        $resDeleteIdle->assertSessionHas('exito', __('lat.vat_deleted', [], $locale));
        $this->assertDatabaseMissing('tinas', ['id' => $tinaLibre->id]);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function codigosTinaValidosProvider(): array
    {
        return [
            'solo sufijo numerico' => ['05', 'TINA-05'],
            'prefijo minusculas con espacio' => ['tina 06', 'TINA-06'],
            'prefijo completo mayusculas' => ['TINA-07', 'TINA-07'],
        ];
    }

    #[DataProvider('codigosTinaValidosProvider')]
    public function test_crear_y_editar_tina_normaliza_prefijo_tina(string $inputCodigo, string $codigoEsperado): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        $resCrear = $this->actingAs($admin)->post('/panel/tinas', [
            'codigo' => $inputCodigo,
            'nombre' => 'Tina Normalizada',
            'capacidad_litros' => 300,
            'tipo' => 'principal_queso',
        ]);

        $resCrear->assertRedirect();
        $tina = Tina::where('planta_id', $admin->planta_id)
            ->where('codigo', $codigoEsperado)
            ->firstOrFail();

        $resEditar = $this->actingAs($admin)->put("/panel/tinas/{$tina->id}", [
            'codigo' => strtolower($inputCodigo),
            'nombre' => 'Tina Editada Prefijo',
            'capacidad_litros' => 350,
            'tipo' => 'multiproposito',
            'activa' => '1',
        ]);

        $resEditar->assertRedirect();
        $this->assertSame($codigoEsperado, $tina->fresh()->codigo);
        $this->assertSame('Tina Editada Prefijo', $tina->fresh()->nombre);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function codigosTinaInvalidosProvider(): array
    {
        return [
            'prefijo sin sufijo' => ['TINA-'],
            'caracteres invalidos' => ['05@#'],
            'guion inicial en sufijo' => ['-05'],
        ];
    }

    #[DataProvider('codigosTinaInvalidosProvider')]
    public function test_crear_y_editar_tina_rechaza_codigo_invalido_con_prefijo_tina(string $inputInvalido): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $tinaExistente = Tina::where('planta_id', $admin->planta_id)
            ->where('codigo', 'TINA-03')
            ->firstOrFail();
        $conteoInicial = Tina::where('planta_id', $admin->planta_id)->count();

        $this->actingAs($admin)
            ->post('/panel/tinas', [
                'codigo' => $inputInvalido,
                'nombre' => 'Tina Inválida',
                'capacidad_litros' => 250,
                'tipo' => 'quesillo',
            ])
            ->assertSessionHasErrors('codigo');

        $this->assertSame($conteoInicial, Tina::where('planta_id', $admin->planta_id)->count());

        $this->actingAs($admin)
            ->put("/panel/tinas/{$tinaExistente->id}", [
                'codigo' => $inputInvalido,
                'nombre' => 'Nombre No Guardado',
                'capacidad_litros' => 200,
                'tipo' => 'yogur',
                'activa' => '1',
            ])
            ->assertSessionHasErrors('codigo');

        $this->assertSame('TINA-03', $tinaExistente->fresh()->codigo);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function codigosPlantaValidosProvider(): array
    {
        return [
            'solo sufijo numerico' => ['05', 'PLT-05'],
            'prefijo minusculas con espacio' => ['plt 06', 'PLT-06'],
            'prefijo completo mayusculas' => ['PLT-07', 'PLT-07'],
        ];
    }

    #[DataProvider('codigosPlantaValidosProvider')]
    public function test_crear_y_editar_planta_normaliza_prefijo_plt(string $inputCodigo, string $codigoEsperado): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        $resCrear = $this->actingAs($admin)->post('/panel/admin/plantas', [
            'codigo' => $inputCodigo,
            'nombre' => 'Planta Prefijo Test',
            'ubicacion' => 'Anta · Cusco',
            'precio_litro' => 1.75,
        ]);

        $resCrear->assertRedirect();
        $planta = Planta::where('codigo', $codigoEsperado)->firstOrFail();
        $this->assertSame(1, $planta->tinas()->where('codigo', 'TINA-01')->count());

        $resEditar = $this->actingAs($admin)->put("/panel/admin/plantas/{$planta->id}", [
            'codigo' => strtolower($inputCodigo),
            'nombre' => 'Planta Prefijo Editada',
            'ubicacion' => 'Zurite · Anta',
            'precio_litro' => 1.85,
            'activa' => '1',
        ]);

        $resEditar->assertRedirect();
        $this->assertSame($codigoEsperado, $planta->fresh()->codigo);
        $this->assertSame('Planta Prefijo Editada', $planta->fresh()->nombre);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function codigosPlantaInvalidosProvider(): array
    {
        return [
            'prefijo sin sufijo' => ['PLT-'],
            'caracteres invalidos' => ['05!#'],
            'guion inicial en sufijo' => ['-05'],
        ];
    }

    #[DataProvider('codigosPlantaInvalidosProvider')]
    public function test_crear_y_editar_planta_rechaza_codigo_invalido_con_prefijo_plt(string $inputInvalido): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $plantaExistente = Planta::where('codigo', 'PLT-02')->firstOrFail();
        $conteoInicial = Planta::count();

        $this->actingAs($admin)
            ->post('/panel/admin/plantas', [
                'codigo' => $inputInvalido,
                'nombre' => 'Planta Inválida',
                'ubicacion' => 'Cusco',
                'precio_litro' => 1.70,
            ])
            ->assertSessionHasErrors('codigo');

        $this->assertSame($conteoInicial, Planta::count());

        $this->actingAs($admin)
            ->put("/panel/admin/plantas/{$plantaExistente->id}", [
                'codigo' => $inputInvalido,
                'nombre' => 'Planta No Actualizada',
                'ubicacion' => 'Chinchero',
                'precio_litro' => 1.70,
                'activa' => '1',
            ])
            ->assertSessionHasErrors('codigo');

        $this->assertSame('PLT-02', $plantaExistente->fresh()->codigo);
    }

    public function test_panel_no_duplica_selector_de_idioma_en_topbar_y_preserva_selector_en_sidebar(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        $res = $this->actingAs($admin)->get('/panel');

        $res->assertOk();
        $res->assertDontSee('dash-topbar-lang', false);
        $res->assertSee('class="dash-lang-row"', false);
        $res->assertSee('data-lang="es"', false);
        $res->assertSee('data-lang="qu"', false);
        $res->assertSee('data-lang="en"', false);
    }
}
