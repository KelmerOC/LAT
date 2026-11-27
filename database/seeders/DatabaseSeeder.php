<?php

namespace Database\Seeders;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\Tina;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * ============================================================================
 * SEMBRADOR CENTRAL DE DATOS (DATABASE SEEDER)
 * ============================================================================
 * Inicializa el ecosistema lácteo multi-planta con múltiples tinas,
 * entregas reales, lotes térmicos, liquidaciones y ventas comparativas.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('secreto123');
        $hoy = Carbon::today()->toDateString();
        $ayer = Carbon::yesterday()->toDateString();

        // 1. Plantas Lácteas Comunitarias (Cusco Provincia)
        $planta1 = Planta::create([
            'codigo' => 'PLT-01',
            'nombre' => 'Planta Láctea San Borreal',
            'ubicacion' => 'Anta · Cusco Provincia',
            'precio_litro' => 1.65,
            'activa' => true,
        ]);

        $planta2 = Planta::create([
            'codigo' => 'PLT-02',
            'nombre' => 'Quesería Valle de Chinchero',
            'ubicacion' => 'Chinchero · Urubamba',
            'precio_litro' => 1.70,
            'activa' => true,
        ]);

        $planta3 = Planta::create([
            'codigo' => 'PLT-03',
            'nombre' => 'Asociación Lechera Cachimayo',
            'ubicacion' => 'Cachimayo · Anta',
            'precio_litro' => 1.60,
            'activa' => true,
        ]);

        // 2. Usuarios con Roles Operativos y Personalizados (Contraseña general: secreto123)
        $admin = User::create([
            'planta_id' => $planta1->id,
            'name' => 'Ing. Rodrigo Quispe',
            'usuario' => 'donjusto',
            'email' => 'donjusto@sanborreal.pe',
            'password' => $password,
            'cargo' => 'administracion',
            'rango' => 'administrador',
            'permisos' => User::PLANTILLAS_CARGO['administracion'],
            'activo' => true,
        ]);

        $encargado = User::create([
            'planta_id' => $planta1->id,
            'name' => 'Ing. Andrés Huamán',
            'usuario' => 'carlos.anta',
            'email' => 'carlos@sanborreal.pe',
            'password' => $password,
            'cargo' => 'encargado_planta',
            'rango' => 'supervisor',
            'permisos' => User::PLANTILLAS_CARGO['encargado_planta'],
            'activo' => true,
            'creado_por' => $admin->id,
        ]);

        $recolector = User::create([
            'planta_id' => $planta1->id,
            'name' => 'Téc. Javier Condori',
            'usuario' => 'mateo.ruta',
            'email' => 'mateo@sanborreal.pe',
            'password' => $password,
            'cargo' => 'recolector_ruta',
            'rango' => 'operador',
            'permisos' => User::PLANTILLAS_CARGO['recolector_ruta'],
            'activo' => true,
            'creado_por' => $admin->id,
        ]);

        User::create([
            'planta_id' => $planta1->id,
            'name' => 'Téc. Valeria Cárdenas',
            'usuario' => 'sabina.tina',
            'email' => 'sabina@sanborreal.pe',
            'password' => $password,
            'cargo' => 'control_tina',
            'rango' => 'operador',
            'permisos' => User::PLANTILLAS_CARGO['control_tina'],
            'activo' => true,
            'creado_por' => $admin->id,
        ]);

        User::create([
            'planta_id' => $planta1->id,
            'name' => 'CPC Ricardo Paredes',
            'usuario' => 'contador.marco',
            'email' => 'contador@sanborreal.pe',
            'password' => $password,
            'cargo' => 'contador_externo',
            'rango' => 'operador',
            'permisos' => User::PLANTILLAS_CARGO['contador_externo'],
            'activo' => true,
            'creado_por' => $admin->id,
        ]);

        // Operadores en plantas vecinas
        $encargado2 = User::create([
            'planta_id' => $planta2->id,
            'name' => 'Rosaura Auccapuma',
            'usuario' => 'rosaura.chinchero',
            'email' => 'rosaura@chinchero.pe',
            'password' => $password,
            'cargo' => 'encargado_planta',
            'rango' => 'supervisor',
            'permisos' => User::PLANTILLAS_CARGO['encargado_planta'],
            'activo' => true,
            'creado_por' => $admin->id,
        ]);

        $encargado3 = User::create([
            'planta_id' => $planta3->id,
            'name' => 'Lucio Mendoza',
            'usuario' => 'lucio.cachimayo',
            'email' => 'lucio@cachimayo.pe',
            'password' => $password,
            'cargo' => 'encargado_planta',
            'rango' => 'supervisor',
            'permisos' => User::PLANTILLAS_CARGO['encargado_planta'],
            'activo' => true,
            'creado_por' => $admin->id,
        ]);

        // 3. Múltiples Tinas por Planta
        $tina1A = Tina::create([
            'planta_id' => $planta1->id,
            'codigo' => 'TINA-01',
            'nombre' => 'Tina Principal Queso Andino',
            'capacidad_litros' => 500,
            'tipo' => 'principal_queso',
            'activa' => true,
        ]);
        $tina1B = Tina::create([
            'planta_id' => $planta1->id,
            'codigo' => 'TINA-02',
            'nombre' => 'Tina Quesillo Fresco 4 Días',
            'capacidad_litros' => 200,
            'tipo' => 'quesillo',
            'activa' => true,
        ]);
        $tina1C = Tina::create([
            'planta_id' => $planta1->id,
            'codigo' => 'TINA-03',
            'nombre' => 'Tina Fermentación Yogur',
            'capacidad_litros' => 150,
            'tipo' => 'yogur',
            'activa' => true,
        ]);

        $tina2A = Tina::create([
            'planta_id' => $planta2->id,
            'codigo' => 'TINA-01',
            'nombre' => 'Tina Chinchero Principal',
            'capacidad_litros' => 450,
            'tipo' => 'principal_queso',
            'activa' => true,
        ]);
        $tina3A = Tina::create([
            'planta_id' => $planta3->id,
            'codigo' => 'TINA-01',
            'nombre' => 'Tina Cachimayo Principal',
            'capacidad_litros' => 300,
            'tipo' => 'principal_queso',
            'activa' => true,
        ]);

        // 4. Productores Ganaderos (Planta 1: San Borreal)
        $p1 = Proveedor::create(['planta_id' => $planta1->id, 'codigo' => 'PRV-01', 'nombre' => 'Don Eloy Condori', 'sector' => 'Markjo', 'latitud' => -13.461, 'longitud' => -72.148, 'activo' => true]);
        $p2 = Proveedor::create(['planta_id' => $planta1->id, 'codigo' => 'PRV-02', 'nombre' => 'Doña María Huamán', 'sector' => 'Compone', 'latitud' => -13.455, 'longitud' => -72.142, 'activo' => true]);
        $p3 = Proveedor::create(['planta_id' => $planta1->id, 'codigo' => 'PRV-03', 'nombre' => 'Don Juan Bautista', 'sector' => 'Izcuchaca', 'latitud' => -13.468, 'longitud' => -72.139, 'activo' => true]);
        $p4 = Proveedor::create(['planta_id' => $planta1->id, 'codigo' => 'PRV-04', 'nombre' => 'Sra. Juana Ccori', 'sector' => 'Huayllacocha', 'latitud' => -13.472, 'longitud' => -72.155, 'activo' => true]);
        $p5 = Proveedor::create(['planta_id' => $planta1->id, 'codigo' => 'PRV-05', 'nombre' => 'Don Pedro Quispe', 'sector' => 'Cachimayo', 'latitud' => -13.480, 'longitud' => -72.120, 'activo' => true]);

        // Proveedores Planta 2 y 3
        $p2_1 = Proveedor::create(['planta_id' => $planta2->id, 'codigo' => 'CHI-01', 'nombre' => 'Sra. Hermelinda Quispe', 'sector' => 'Cuper Bajo', 'activo' => true]);
        $p2_2 = Proveedor::create(['planta_id' => $planta2->id, 'codigo' => 'CHI-02', 'nombre' => 'Don Saturnino Sallo', 'sector' => 'Yanacona', 'activo' => true]);
        $p3_1 = Proveedor::create(['planta_id' => $planta3->id, 'codigo' => 'CAC-01', 'nombre' => 'Don Efraín Huamán', 'sector' => 'Cachimayo Centro', 'activo' => true]);

        // 5. Entregas de leche de hoy y ayer (Planta 1)
        $evaluadoHoy = Carbon::today()->setTime(7, 55);

        Acopio::create([
            'planta_id' => $planta1->id,
            'proveedor_id' => $p1->id,
            'registrado_por' => $recolector->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => $hoy,
            'hora' => '06:15:00',
            'litros' => 35.0,
            'precio_litro' => 1.65,
            'metodo' => 'voz',
            'frase_dictada' => 'Don Eloy Condori, 35 litros',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => $evaluadoHoy,
        ]);

        Acopio::create([
            'planta_id' => $planta1->id,
            'proveedor_id' => $p2->id,
            'registrado_por' => $recolector->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => $hoy,
            'hora' => '06:40:00',
            'litros' => 28.5,
            'precio_litro' => 1.65,
            'metodo' => 'voz',
            'frase_dictada' => 'Señora María Huamán, 28 litros y medio',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => $evaluadoHoy,
        ]);

        Acopio::create([
            'planta_id' => $planta1->id,
            'proveedor_id' => $p3->id,
            'registrado_por' => $encargado->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => $hoy,
            'hora' => '07:10:00',
            'litros' => 18.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'frase_dictada' => null,
            'calidad' => 'acida',
            'destino' => 'quesillo_4_dias',
            'evaluado_en' => $evaluadoHoy,
        ]);

        Acopio::create([
            'planta_id' => $planta1->id,
            'proveedor_id' => $p4->id,
            'registrado_por' => $recolector->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => $hoy,
            'hora' => '07:35:00',
            'litros' => 42.0,
            'precio_litro' => 1.65,
            'metodo' => 'voz',
            'frase_dictada' => 'Juana Ccori, 42 litros',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => $evaluadoHoy,
        ]);

        Acopio::create([
            'planta_id' => $planta1->id,
            'proveedor_id' => $p5->id,
            'registrado_por' => $encargado->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => $hoy,
            'hora' => '07:50:00',
            'litros' => 40.0,
            'precio_litro' => 1.65,
            'metodo' => 'manual',
            'frase_dictada' => null,
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => $evaluadoHoy,
        ]);

        // Acopios en Planta 2 y Planta 3
        Acopio::create([
            'planta_id' => $planta2->id,
            'proveedor_id' => $p2_1->id,
            'registrado_por' => $encargado2->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => $hoy,
            'hora' => '06:30:00',
            'litros' => 110.0,
            'precio_litro' => 1.70,
            'metodo' => 'voz',
            'frase_dictada' => 'Hermelinda Quispe, 110 litros',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => $evaluadoHoy,
        ]);
        Acopio::create([
            'planta_id' => $planta2->id,
            'proveedor_id' => $p2_2->id,
            'registrado_por' => $encargado2->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => $hoy,
            'hora' => '07:05:00',
            'litros' => 85.0,
            'precio_litro' => 1.70,
            'metodo' => 'manual',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => $evaluadoHoy,
        ]);
        Acopio::create([
            'planta_id' => $planta3->id,
            'proveedor_id' => $p3_1->id,
            'registrado_por' => $encargado3->id,
            'uuid_offline' => (string) Str::uuid(),
            'fecha' => $hoy,
            'hora' => '06:45:00',
            'litros' => 95.0,
            'precio_litro' => 1.60,
            'metodo' => 'manual',
            'calidad' => 'buena',
            'destino' => 'queso_yogur',
            'evaluado_en' => $evaluadoHoy,
        ]);

        // 6. Lotes en múltiples tinas (activos y terminados para estadísticas)
        $inicioActivo = Carbon::now()->subMinutes(15);

        Lote::create([
            'planta_id' => $planta1->id,
            'tina_id' => $tina1A->id,
            'codigo' => 'LOTE-'.Carbon::now()->format('Ymd').'-T1',
            'fecha' => $hoy,
            'litros' => 145.5,
            'temperatura_actual' => 48.0,
            'cuajo_ml' => 3.64,
            'sal_kg' => 2.62,
            'cultivo_g' => 1.46,
            'moldes_producidos' => 0,
            'estado' => 'enfriando',
            'duracion_minutos' => 45,
            'iniciado_en' => $inicioActivo,
        ]);

        Lote::create([
            'planta_id' => $planta1->id,
            'tina_id' => $tina1B->id,
            'codigo' => 'LOTE-'.Carbon::now()->format('Ymd').'-T2',
            'fecha' => $hoy,
            'litros' => 36.0,
            'temperatura_actual' => 38.0,
            'cuajo_ml' => 0.90,
            'sal_kg' => 0.65,
            'cultivo_g' => 0.36,
            'moldes_producidos' => 0,
            'estado' => 'lista_para_cuajo',
            'duracion_minutos' => 45,
            'iniciado_en' => $inicioActivo,
        ]);

        Lote::create([
            'planta_id' => $planta1->id,
            'tina_id' => $tina1A->id,
            'codigo' => 'LOTE-'.Carbon::yesterday()->format('Ymd').'-T1',
            'fecha' => $ayer,
            'litros' => 270.0,
            'temperatura_actual' => 37.5,
            'cuajo_ml' => 6.75,
            'sal_kg' => 4.86,
            'cultivo_g' => 2.70,
            'moldes_producidos' => 30,
            'estado' => 'terminado',
        ]);

        Lote::create([
            'planta_id' => $planta2->id,
            'tina_id' => $tina2A->id,
            'codigo' => 'LOTE-CHI-'.Carbon::now()->format('Ymd').'-01',
            'fecha' => $hoy,
            'litros' => 195.0,
            'temperatura_actual' => 39.0,
            'cuajo_ml' => 4.88,
            'sal_kg' => 3.51,
            'cultivo_g' => 1.95,
            'moldes_producidos' => 22,
            'estado' => 'terminado',
        ]);

        Lote::create([
            'planta_id' => $planta3->id,
            'tina_id' => $tina3A->id,
            'codigo' => 'LOTE-CAC-'.Carbon::now()->format('Ymd').'-01',
            'fecha' => $hoy,
            'litros' => 95.0,
            'temperatura_actual' => 42.0,
            'cuajo_ml' => 2.38,
            'sal_kg' => 1.71,
            'cultivo_g' => 0.95,
            'moldes_producidos' => 10,
            'estado' => 'terminado',
        ]);

        // 7. Movimientos financieros y Ventas por Planta (para ranking "¿Quién vende más?")
        Liquidacion::create([
            'planta_id' => $planta1->id,
            'proveedor_id' => $p1->id,
            'fecha' => $hoy,
            'tipo' => 'adelanto_efectivo',
            'concepto' => 'Adelanto en efectivo para compra de forraje',
            'litros' => 0,
            'monto' => 30.00,
            'comision' => 0.00,
            'descontado' => false,
        ]);

        Liquidacion::create([
            'planta_id' => $planta1->id,
            'proveedor_id' => $p2->id,
            'fecha' => $hoy,
            'tipo' => 'adelanto_queso',
            'concepto' => '2 moldes de queso andino maduro (S/ 20 c/u)',
            'litros' => 0,
            'cantidad_unidades' => 2,
            'monto' => 40.00,
            'comision' => 0.00,
            'descontado' => false,
        ]);

        Liquidacion::create([
            'planta_id' => $planta1->id,
            'proveedor_id' => null,
            'fecha' => $hoy,
            'tipo' => 'ayuda_del_dia',
            'concepto' => 'Pago en mano por faena de lavado de porongos y volteo de quesos',
            'litros' => 0,
            'monto' => 45.00,
            'comision' => 0.00,
            'descontado' => true,
        ]);

        // Ventas de productos lácteos por planta
        Liquidacion::create([
            'planta_id' => $planta1->id,
            'proveedor_id' => null,
            'fecha' => $hoy,
            'tipo' => 'venta_producto',
            'concepto' => 'Venta feria dominical Anta: 32 moldes queso andino + 15 quesillos',
            'litros' => 0,
            'cantidad_unidades' => 47,
            'monto' => 890.00,
            'comision' => 0.00,
            'descontado' => true,
        ]);
        Liquidacion::create([
            'planta_id' => $planta1->id,
            'proveedor_id' => null,
            'fecha' => $ayer,
            'tipo' => 'venta_producto',
            'concepto' => 'Pedido bodegas Cusco centro: 20 moldes queso maduro',
            'litros' => 0,
            'cantidad_unidades' => 20,
            'monto' => 440.00,
            'comision' => 0.00,
            'descontado' => true,
        ]);

        Liquidacion::create([
            'planta_id' => $planta2->id,
            'proveedor_id' => null,
            'fecha' => $hoy,
            'tipo' => 'venta_producto',
            'concepto' => 'Venta directa mercado Chinchero y hoteles Urubamba: 45 moldes',
            'litros' => 0,
            'cantidad_unidades' => 45,
            'monto' => 1080.00,
            'comision' => 0.00,
            'descontado' => true,
        ]);

        Liquidacion::create([
            'planta_id' => $planta3->id,
            'proveedor_id' => null,
            'fecha' => $hoy,
            'tipo' => 'venta_producto',
            'concepto' => 'Venta local Cachimayo: 18 moldes queso fresco',
            'litros' => 0,
            'cantidad_unidades' => 18,
            'monto' => 360.00,
            'comision' => 0.00,
            'descontado' => true,
        ]);

        // 8. Historial de 6 días previos para gráficas de tendencia en Dashboard
        $historialDias = [
            ['dias_atras' => 1, 'litros_buena' => 142.0, 'litros_acida' => 14.0, 'venta' => 520.00, 'unidades' => 26, 'gasto' => 65.00, 'moldes' => 18],
            ['dias_atras' => 2, 'litros_buena' => 158.0, 'litros_acida' => 10.0, 'venta' => 680.00, 'unidades' => 34, 'gasto' => 90.00, 'moldes' => 21],
            ['dias_atras' => 3, 'litros_buena' => 134.0, 'litros_acida' => 22.0, 'venta' => 475.00, 'unidades' => 24, 'gasto' => 55.00, 'moldes' => 16],
            ['dias_atras' => 4, 'litros_buena' => 168.0, 'litros_acida' => 8.5,  'venta' => 790.00, 'unidades' => 39, 'gasto' => 80.00, 'moldes' => 24],
            ['dias_atras' => 5, 'litros_buena' => 151.0, 'litros_acida' => 12.0, 'venta' => 610.00, 'unidades' => 30, 'gasto' => 70.00, 'moldes' => 19],
            ['dias_atras' => 6, 'litros_buena' => 126.0, 'litros_acida' => 16.0, 'venta' => 430.00, 'unidades' => 22, 'gasto' => 50.00, 'moldes' => 15],
        ];

        $proveedoresP1 = [$p1, $p2, $p3, $p4, $p5];
        foreach ($historialDias as $idx => $hd) {
            $fechaDia = Carbon::today()->subDays($hd['dias_atras'])->toDateString();
            $provPrincipal = $proveedoresP1[$idx % count($proveedoresP1)];
            $provSecundario = $proveedoresP1[($idx + 1) % count($proveedoresP1)];

            Acopio::create([
                'planta_id' => $planta1->id,
                'proveedor_id' => $provPrincipal->id,
                'registrado_por' => $recolector->id,
                'uuid_offline' => (string) Str::uuid(),
                'fecha' => $fechaDia,
                'hora' => '06:30:00',
                'litros' => $hd['litros_buena'],
                'precio_litro' => 1.65,
                'metodo' => $idx % 2 === 0 ? 'voz' : 'manual',
                'frase_dictada' => null,
                'calidad' => 'buena',
                'destino' => 'queso_yogur',
            ]);

            Acopio::create([
                'planta_id' => $planta1->id,
                'proveedor_id' => $provSecundario->id,
                'registrado_por' => $encargado->id,
                'uuid_offline' => (string) Str::uuid(),
                'fecha' => $fechaDia,
                'hora' => '07:20:00',
                'litros' => $hd['litros_acida'],
                'precio_litro' => 1.65,
                'metodo' => 'manual',
                'frase_dictada' => null,
                'calidad' => 'acida',
                'destino' => 'quesillo_4_dias',
            ]);

            if ($hd['dias_atras'] >= 2) {
                Lote::create([
                    'planta_id' => $planta1->id,
                    'tina_id' => $tina1A->id,
                    'codigo' => 'LOTE-HIST-'.Carbon::parse($fechaDia)->format('Ymd'),
                    'fecha' => $fechaDia,
                    'litros' => $hd['litros_buena'],
                    'temperatura_actual' => 38.0,
                    'cuajo_ml' => round($hd['litros_buena'] * 0.025, 2),
                    'sal_kg' => round($hd['litros_buena'] * 0.018, 2),
                    'cultivo_g' => round($hd['litros_buena'] * 0.010, 2),
                    'moldes_producidos' => $hd['moldes'],
                    'estado' => 'terminado',
                ]);
            }

            Liquidacion::create([
                'planta_id' => $planta1->id,
                'proveedor_id' => null,
                'fecha' => $fechaDia,
                'tipo' => 'venta_producto',
                'concepto' => 'Despacho diario quesería · '.Carbon::parse($fechaDia)->format('d/m'),
                'litros' => 0,
                'cantidad_unidades' => $hd['unidades'],
                'monto' => $hd['venta'],
                'comision' => 0.00,
                'descontado' => true,
            ]);

            Liquidacion::create([
                'planta_id' => $planta1->id,
                'proveedor_id' => null,
                'fecha' => $fechaDia,
                'tipo' => 'gasto_operativo',
                'concepto' => 'Insumos de empaque y combustible · '.Carbon::parse($fechaDia)->format('d/m'),
                'litros' => 0,
                'cantidad_unidades' => 0,
                'monto' => $hd['gasto'],
                'comision' => 0.00,
                'descontado' => true,
            ]);
        }
    }
}
