<?php

namespace Database\Seeders;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * ============================================================================
 * SEMBRADOR CENTRAL DE DATOS (DATABASE SEEDER)
 * ============================================================================
 * Inicializa el ecosistema lácteo con datos de prueba realistas para
 * sustentaciones universitarias y pruebas operativas inmediatas.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuarios con Roles Operativos (Contraseña general: secreto123)
        $password = Hash::make('secreto123');

        $admin = User::create([
            'name'     => 'Don Justo Quispe',
            'usuario'  => 'donjusto',
            'email'    => 'donjusto@sanborreal.pe',
            'password' => $password,
            'cargo'    => 'administracion',
            'activo'   => true,
        ]);

        $encargado = User::create([
            'name'     => 'Carlos Quispe (Hijo Encargado)',
            'usuario'  => 'carlos.anta',
            'email'    => 'carlos@sanborreal.pe',
            'password' => $password,
            'cargo'    => 'encargado_planta',
            'activo'   => true,
        ]);

        $recolector = User::create([
            'name'     => 'Mateo Triveño',
            'usuario'  => 'mateo.ruta',
            'email'    => 'mateo@sanborreal.pe',
            'password' => $password,
            'cargo'    => 'recolector_ruta',
            'activo'   => true,
        ]);

        $calidad = User::create([
            'name'     => 'Sabina Huillca',
            'usuario'  => 'sabina.tina',
            'email'    => 'sabina@sanborreal.pe',
            'password' => $password,
            'cargo'    => 'control_tina',
            'activo'   => true,
        ]);

        $contador = User::create([
            'name'     => 'Marco Antonio (Contador)',
            'usuario'  => 'contador.marco',
            'email'    => 'contador@sanborreal.pe',
            'password' => $password,
            'cargo'    => 'contador_externo',
            'activo'   => true,
        ]);

        // 2. Productores Ganaderos de Anta
        $p1 = Proveedor::create(['codigo' => 'PRV-01', 'nombre' => 'Don Eloy Condori', 'sector' => 'Markjo', 'latitud' => -13.461, 'longitud' => -72.148, 'activo' => true]);
        $p2 = Proveedor::create(['codigo' => 'PRV-02', 'nombre' => 'Doña María Huamán', 'sector' => 'Compone', 'latitud' => -13.455, 'longitud' => -72.142, 'activo' => true]);
        $p3 = Proveedor::create(['codigo' => 'PRV-03', 'nombre' => 'Don Juan Bautista', 'sector' => 'Izcuchaca', 'latitud' => -13.468, 'longitud' => -72.139, 'activo' => true]);
        $p4 = Proveedor::create(['codigo' => 'PRV-04', 'nombre' => 'Sra. Juana Ccori', 'sector' => 'Huayllacocha', 'latitud' => -13.472, 'longitud' => -72.155, 'activo' => true]);
        $p5 = Proveedor::create(['codigo' => 'PRV-05', 'nombre' => 'Don Pedro Quispe', 'sector' => 'Cachimayo', 'latitud' => -13.480, 'longitud' => -72.120, 'activo' => true]);

        // 3. Entregas de leche del día (Módulo Acopio y Calidad)
        $hoy = Carbon::today()->toDateString();

        Acopio::create([
            'proveedor_id'   => $p1->id,
            'registrado_por' => $recolector->id,
            'uuid_offline'   => (string) Str::uuid(),
            'fecha'          => $hoy,
            'hora'           => '06:15:00',
            'litros'         => 35.0,
            'precio_litro'   => 1.65,
            'metodo'         => 'voz',
            'frase_dictada'  => 'Don Eloy Condori, 35 litros',
            'calidad'        => 'buena',
            'destino'        => 'queso_yogur',
        ]);

        Acopio::create([
            'proveedor_id'   => $p2->id,
            'registrado_por' => $recolector->id,
            'uuid_offline'   => (string) Str::uuid(),
            'fecha'          => $hoy,
            'hora'           => '06:40:00',
            'litros'         => 28.5,
            'precio_litro'   => 1.65,
            'metodo'         => 'voz',
            'frase_dictada'  => 'Señora María Huamán, 28 litros y medio',
            'calidad'        => 'buena',
            'destino'        => 'queso_yogur',
        ]);

        Acopio::create([
            'proveedor_id'   => $p3->id,
            'registrado_por' => $encargado->id,
            'uuid_offline'   => (string) Str::uuid(),
            'fecha'          => $hoy,
            'hora'           => '07:10:00',
            'litros'         => 18.0,
            'precio_litro'   => 1.65,
            'metodo'         => 'manual',
            'frase_dictada'  => null,
            'calidad'        => 'acida', // Prueba de alcohol a 74°: Leche ácida
            'destino'        => 'quesillo_4_dias', // Desvío sin descarte
        ]);

        Acopio::create([
            'proveedor_id'   => $p4->id,
            'registrado_por' => $recolector->id,
            'uuid_offline'   => (string) Str::uuid(),
            'fecha'          => $hoy,
            'hora'           => '07:35:00',
            'litros'         => 42.0,
            'precio_litro'   => 1.65,
            'metodo'         => 'voz',
            'frase_dictada'  => 'Juana Ccori, 42 litros',
            'calidad'        => 'buena',
            'destino'        => 'queso_yogur',
        ]);

        // 4. Lote activo en tina de pasteurización y enfriamiento
        Lote::create([
            'codigo'             => 'LOTE-'.Carbon::now()->format('Ymd').'-01',
            'fecha'              => $hoy,
            'litros'             => 105.5,
            'temperatura_actual' => 48.0, // En proceso de enfriamiento hacia 38°C
            'cuajo_ml'           => 2.64,
            'sal_kg'             => 1.90,
            'cultivo_g'          => 1.06,
            'moldes_producidos'  => 0,
            'estado'             => 'enfriando',
        ]);

        // 5. Movimientos financieros de prueba
        Liquidacion::create([
            'proveedor_id' => $p1->id,
            'fecha'        => $hoy,
            'tipo'         => 'adelanto_efectivo',
            'concepto'     => 'Adelanto en efectivo para compra de forraje',
            'litros'       => 0,
            'monto'        => 30.00,
            'comision'     => 0.00,
            'descontado'   => false,
        ]);

        Liquidacion::create([
            'proveedor_id' => $p2->id,
            'fecha'        => $hoy,
            'tipo'         => 'adelanto_queso',
            'concepto'     => '2 moldes de queso andino maduro (S/ 20 c/u)',
            'litros'       => 0,
            'monto'        => 40.00,
            'comision'     => 0.00,
            'descontado'   => false,
        ]);

        // Principio de minimización de datos (Ley 29733): Sin DNI ni cuenta obligatoria
        Liquidacion::create([
            'proveedor_id' => null,
            'fecha'        => $hoy,
            'tipo'         => 'ayuda_del_dia',
            'concepto'     => 'Pago en mano por faena de lavado de porongos y volteo de quesos',
            'litros'       => 0,
            'monto'        => 45.00,
            'comision'     => 0.00,
            'descontado'   => true,
        ]);
    }
}
