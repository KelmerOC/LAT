<?php

namespace Tests\Feature;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\Tina;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
            'name'                  => 'Elena Mamani',
            'usuario'               => 'elena.maras',
            'email'                 => 'elena@maras.pe',
            'planta_nombre'         => 'Quesería Maras Norte',
            'planta_ubicacion'      => 'Maras · Urubamba',
            'precio_litro'          => 1.75,
            'password'              => 'clave1234',
            'password_confirmation' => 'clave1234',
        ]);

        $response->assertRedirect('/panel');
        $this->assertAuthenticated();

        $user = User::where('usuario', 'elena.maras')->first();
        $this->assertNotNull($user);
        $this->assertSame('administracion', $user->cargo);

        $planta = Planta::find($user->planta_id);
        $this->assertSame('Quesería Maras Norte', $planta->nombre);
        $this->assertSame(1, $planta->tinas()->count());
    }

    public function test_admin_crea_y_edita_credenciales_de_usuario_y_no_admin_es_bloqueado(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();
        $planta = Planta::where('codigo', 'PLT-01')->firstOrFail();

        $res = $this->actingAs($admin)->post('/panel/admin/usuarios', [
            'name'      => 'Operario Nuevo',
            'usuario'   => 'nuevo.ruta',
            'email'     => '',
            'password'  => 'operario123',
            'cargo'     => 'recolector_ruta',
            'planta_id' => $planta->id,
        ]);

        $res->assertRedirect();
        $nuevo = User::where('usuario', 'nuevo.ruta')->firstOrFail();
        $this->assertSame('recolector_ruta', $nuevo->cargo);

        // Actualizar cargo del nuevo usuario
        $resEdit = $this->actingAs($admin)->put("/panel/admin/usuarios/{$nuevo->id}", [
            'name'      => 'Operario Ascendido',
            'usuario'   => 'nuevo.ruta',
            'cargo'     => 'control_tina',
            'planta_id' => $planta->id,
            'activo'    => '1',
        ]);
        $resEdit->assertRedirect();
        $this->assertSame('control_tina', $nuevo->fresh()->cargo);

        // Usuario no admin no puede crear usuarios
        $this->actingAs($nuevo)->post('/panel/admin/usuarios', [
            'name'      => 'Intruso',
            'usuario'   => 'intruso',
            'password'  => 'secret123',
            'cargo'     => 'administracion',
            'planta_id' => $planta->id,
        ])->assertStatus(403);
    }

    public function test_admin_gestiona_plantas_y_cambia_planta_activa(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        $this->actingAs($admin)->post('/panel/admin/plantas', [
            'codigo'       => 'PLT-04',
            'nombre'       => 'Planta Urcos Sur',
            'ubicacion'    => 'Quispicanchi · Cusco',
            'precio_litro' => 1.80,
        ])->assertRedirect();

        $planta = Planta::where('codigo', 'PLT-04')->firstOrFail();
        $this->assertSame('Planta Urcos Sur', $planta->nombre);

        $this->actingAs($admin)->put("/panel/admin/plantas/{$planta->id}", [
            'codigo'       => 'PLT-04',
            'nombre'       => 'Planta Urcos Editada',
            'ubicacion'    => 'Urcos Centro',
            'precio_litro' => 1.85,
            'activa'       => '1',
        ])->assertRedirect();

        $this->assertSame('Planta Urcos Editada', $planta->fresh()->nombre);
        $this->assertSame(1.85, $planta->fresh()->precio_litro);
    }

    public function test_multiples_tinas_agregar_editar_iniciar_y_finalizar_lote(): void
    {
        $admin = User::where('usuario', 'donjusto')->firstOrFail();

        // Agregar nueva tina
        $this->actingAs($admin)->post('/panel/tinas', [
            'codigo'           => 'TINA-04',
            'nombre'           => 'Tina Queso Paria',
            'capacidad_litros' => 400,
            'tipo'             => 'principal_queso',
        ])->assertRedirect();

        $tina = Tina::where('codigo', 'TINA-04')->firstOrFail();

        // Editar tina
        $this->actingAs($admin)->put("/panel/tinas/{$tina->id}", [
            'codigo'           => 'TINA-04',
            'nombre'           => 'Tina Queso Paria Plus',
            'capacidad_litros' => 450,
            'tipo'             => 'multiproposito',
            'activa'           => '1',
        ])->assertRedirect();

        $this->assertSame('Tina Queso Paria Plus', $tina->fresh()->nombre);

        // Iniciar lote en la tina nueva
        $this->actingAs($admin)->post("/panel/tinas/{$tina->id}/lote", [
            'litros'             => 180,
            'temperatura_actual' => 72.0,
        ])->assertRedirect();

        $lote = Lote::where('tina_id', $tina->id)->firstOrFail();
        $this->assertSame('pasteurizando', $lote->estado);

        // Ajustar temperatura y finalizar lote
        $this->actingAs($admin)->post("/panel/tina/{$lote->id}", [
            'temperatura' => 38.0,
            'litros'      => 180,
        ])->assertRedirect();
        $this->assertSame('lista_para_cuajo', $lote->fresh()->estado);

        $this->actingAs($admin)->post("/panel/tina/{$lote->id}", [
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
            'litros'       => 55.5,
            'calidad'      => 'acida',
            'metodo'       => 'manual',
            'hora'         => '08:15',
        ])->assertRedirect();

        $acopioFresh = $acopio->fresh();
        $this->assertSame(55.5, $acopioFresh->litros);
        $this->assertSame('quesillo_4_dias', $acopioFresh->destino);

        $mov = Liquidacion::where('planta_id', $admin->planta_id)->firstOrFail();
        $this->actingAs($admin)->put("/panel/movimiento/{$mov->id}", [
            'tipo'              => 'venta_producto',
            'concepto'          => 'Venta corregida feria',
            'cantidad_unidades' => 30,
            'monto'             => 600.0,
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
        $this->get('/idioma/qu')->assertStatus(302);
        $this->get('/')->assertStatus(200)->assertSee('<html lang="qu">', false);

        $this->get('/idioma/en')->assertStatus(302);
        $this->get('/')->assertStatus(200)->assertSee('<html lang="en">', false);
    }
}
