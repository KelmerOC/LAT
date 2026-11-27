<?php

namespace Tests\Feature;

use App\Models\Acopio;
use App\Models\Lote;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\ReporteExcel;
use App\Models\Tina;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class LatSeguridadOwaspTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_owasp_a01_registro_publico_no_puede_escalar_a_otras_plantas_ni_editar_usuarios_ajenos(): void
    {
        $planta1 = Planta::where('codigo', 'PLT-01')->firstOrFail();
        $adminP1 = User::where('usuario', 'rodrigo')->firstOrFail();

        $this->post('/registrarse', [
            'name' => 'Dueño Planta Nueva',
            'usuario' => 'dueno.nuevo',
            'email' => 'dueno@nueva.pe',
            'planta_nombre' => 'Quesería Independiente',
            'planta_ubicacion' => 'Ollantaytambo',
            'precio_litro' => 1.70,
            'password' => 'ClaveSegura123',
            'password_confirmation' => 'ClaveSegura123',
        ])->assertRedirect('/panel');

        $nuevo = User::where('usuario', 'dueno.nuevo')->firstOrFail();

        // No debe poder cambiar de planta ni administrar plantas globales
        $this->actingAs($nuevo)->post('/panel/cambiar-planta', ['planta_id' => $planta1->id])->assertStatus(403);
        $this->actingAs($nuevo)->delete("/panel/admin/plantas/{$planta1->id}")->assertStatus(403);
        $this->actingAs($nuevo)->put("/panel/admin/plantas/{$planta1->id}", [
            'codigo' => 'HACK',
            'nombre' => 'Hack',
            'ubicacion' => 'Hack',
            'precio_litro' => 1.5,
        ])->assertStatus(403);

        // No debe poder ver ni editar usuarios de Planta 1
        $this->actingAs($nuevo)->put("/panel/admin/usuarios/{$adminP1->id}", [
            'name' => 'Hackeado',
            'usuario' => $adminP1->usuario,
            'cargo' => 'encargado_planta',
            'planta_id' => $nuevo->planta_id,
        ])->assertStatus(403);

        $panelRes = $this->actingAs($nuevo)->get('/panel');
        $panelRes->assertStatus(200);
        $panelRes->assertDontSee('Planta Láctea San Borreal');
    }

    public function test_owasp_a01_idor_cruzado_entre_plantas_es_bloqueado_en_todas_las_rutas(): void
    {
        $planta2 = Planta::where('codigo', 'PLT-02')->firstOrFail();
        $encargadoP1 = User::where('usuario', 'andres.anta')->firstOrFail();

        $provP2 = Proveedor::where('planta_id', $planta2->id)->firstOrFail();
        $acopioP2 = Acopio::where('planta_id', $planta2->id)->firstOrFail();
        $tinaP2 = Tina::where('planta_id', $planta2->id)->firstOrFail();
        $loteP2 = Lote::where('planta_id', $planta2->id)->firstOrFail();

        // No puede registrar acopio ni movimiento usando proveedor de otra planta
        $this->actingAs($encargadoP1)->post('/panel/acopio', [
            'proveedor_id' => $provP2->id,
            'litros' => 20,
        ])->assertSessionHasErrors('proveedor_id');

        $this->actingAs($encargadoP1)->post('/panel/movimiento', [
            'tipo' => 'adelanto_efectivo',
            'proveedor_id' => $provP2->id,
            'concepto' => 'Intento IDOR',
            'monto' => 50,
        ])->assertSessionHasErrors('proveedor_id');

        // No puede evaluar calidad, operar tina o lote ni eliminar proveedor/tina de otra planta
        $this->actingAs($encargadoP1)->post("/panel/calidad/{$acopioP2->id}", ['calidad' => 'acida'])->assertStatus(404);
        $this->actingAs($encargadoP1)->post("/panel/tinas/{$tinaP2->id}/lote", ['litros' => 50])->assertStatus(404);
        $this->actingAs($encargadoP1)->post("/panel/tina/{$loteP2->id}", ['temperatura' => 38])->assertStatus(404);
        $this->actingAs($encargadoP1)->delete("/panel/tinas/{$tinaP2->id}")->assertStatus(404);
        $this->actingAs($encargadoP1)->delete("/panel/proveedor/{$provP2->id}")->assertStatus(404);

        // No puede descargar reporte Excel de otra planta
        Storage::fake('local');
        Storage::disk('local')->put('reportes_excel/p2.xlsx', 'fake');
        $adminP1 = User::where('usuario', 'rodrigo')->firstOrFail();
        $repP2 = ReporteExcel::create([
            'planta_id' => $planta2->id,
            'remitente_id' => $adminP1->id,
            'destinatario_id' => $adminP1->id,
            'tipo_reporte' => 'balance_quincenal',
            'fecha_desde' => now()->toDateString(),
            'fecha_hasta' => now()->toDateString(),
            'nombre_archivo' => 'p2.xlsx',
            'ruta_archivo' => 'reportes_excel/p2.xlsx',
            'estado' => 'enviado',
        ]);
        $this->actingAs($encargadoP1)->get("/panel/reportes-excel/{$repP2->id}/descargar")->assertStatus(404);
    }

    public function test_owasp_a01_invitado_es_redirigido_en_rutas_privadas(): void
    {
        $this->get('/panel')->assertRedirect('/login');
        $this->post('/panel/acopio', [])->assertRedirect('/login');
        $this->post('/panel/acopio/sync', [])->assertRedirect('/login');
        $this->delete('/panel/admin/plantas/1')->assertRedirect('/login');
        $this->post('/panel/reportes-excel/enviar', [])->assertRedirect('/login');
    }

    public function test_owasp_a03_sql_injection_xss_y_excel_formula_injection(): void
    {
        $encargado = User::where('usuario', 'andres.anta')->firstOrFail();
        $admin = User::where('usuario', 'rodrigo')->firstOrFail();

        // 1. SQL Injection en login y en filtros de acopio
        $this->post('/login', [
            'login' => "' OR '1'='1' --",
            'password' => "' OR '1'='1",
        ])->assertSessionHasErrors('login');
        $this->assertGuest();

        $this->actingAs($encargado)->get('/panel?acopio_fecha_desde='.urlencode("2026-01-01' OR 1=1--").'&acopio_litros_min='.urlencode('1; DROP TABLE acopios;--'))
            ->assertStatus(200);
        $this->assertTrue(Acopio::count() > 0);

        // 2. XSS en proveedor y movimiento debe escaparse en HTML
        $xssPayload = '<script>window.__xss=1</script>';
        $this->actingAs($encargado)->post('/panel/proveedor', [
            'codigo' => 'XSS-01',
            'nombre' => $xssPayload,
            'sector' => 'Sector <img src=x onerror=alert(1)>',
        ])->assertRedirect();

        $this->actingAs($encargado)->post('/panel/movimiento', [
            'tipo' => 'gasto_operativo',
            'concepto' => '=CMD|\' /C calc\'!A0',
            'monto' => 15.0,
        ])->assertRedirect();

        $panelHtml = $this->actingAs($encargado)->get('/panel')->assertStatus(200)->getContent();
        $this->assertStringNotContainsString($xssPayload, $panelHtml);
        $this->assertStringContainsString('&lt;script&gt;window.__xss=1&lt;/script&gt;', $panelHtml);

        // 3. Excel Formula Injection debe neutralizarse con comilla simple (')
        $excelRes = $this->actingAs($admin)->get('/panel/exportar-excel')->assertStatus(200);
        $tmp = tempnam(sys_get_temp_dir(), 'lat_sec_xlsx_');
        $this->assertNotFalse($tmp);
        file_put_contents($tmp, $excelRes->getContent());
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tmp) === true);

        try {
            $sheet3 = html_entity_decode((string) $zip->getFromName('xl/worksheets/sheet3.xml'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $this->assertStringContainsString("'=CMD|' /C calc'!A0", $sheet3);
        } finally {
            $zip->close();
            @unlink($tmp);
        }
    }

    public function test_owasp_a07_rate_limiting_en_login_y_registro(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'login' => 'rodrigo',
                'password' => 'clave-incorrecta',
            ])->assertSessionHasErrors('login');
        }

        // El 6.º intento debe quedar bloqueado por el rate limiter
        $resBloqueo = $this->post('/login', [
            'login' => 'rodrigo',
            'password' => 'secreto123',
        ]);
        $resBloqueo->assertSessionHasErrors('login');
        $this->assertGuest();

        // Throttling en /registrarse (máx 5/min)
        for ($i = 0; $i < 5; $i++) {
            $this->post('/registrarse', []);
        }
        $this->post('/registrarse', [])->assertStatus(429);
    }

    public function test_owasp_a05_cabeceras_de_seguridad_y_csrf_en_middleware(): void
    {
        $res = $this->get('/');
        $res->assertStatus(200);
        $res->assertHeader('X-Frame-Options', 'DENY');
        $res->assertHeader('X-Content-Type-Options', 'nosniff');
        $res->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString("default-src 'self'", (string) $res->headers->get('Content-Security-Policy'));

        // Verificar que la ruta POST /panel/acopio exige middleware web (CSRF) + auth
        $route = app('router')->getRoutes()->getByName('panel.acopio');
        $this->assertNotNull($route);
        $this->assertContains('web', $route->gatherMiddleware());
        $this->assertContains('auth', $route->gatherMiddleware());
    }
}
