<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================================
 * PROYECTO UNIVERSITARIO: SISTEMA LAT (VERSIÓN COMPACTA ARQUITECTURADA)
 * MIGRACIÓN UNIFICADA DE BASE DE DATOS Y OPTIMIZACIÓN DE ÍNDICES
 * ============================================================================
 * Estructura de persistencia relacional que consolida los 4 módulos operativos:
 * 1. Usuarios y Control de Acceso basado en Roles (RBAC).
 * 2. Proveedores ganaderos comunitarios de la microcuenca.
 * 3. Acopios de leche con dictado de voz y triage de calidad sanitaria.
 * 4. Lotes de tina de pasteurización, monitoreo térmico y dosificación de receta.
 * 5. Liquidaciones quincenales a comisión S/ 0.00, adelantos y jornales de apoyo.
 *
 * [BUENAS PRÁCTICAS]: Se incluyen índices compuestos para evitar escaneos
 * completos de tablas (Full Table Scan) en consultas de alta frecuencia.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Usuarios del sistema (Planta familiar: Administración y Operarios)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('usuario', 60)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->enum('cargo', [
                'administracion',
                'encargado_planta',
                'recolector_ruta',
                'control_tina',
                'contador_externo'
            ])->default('administracion');
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Ganaderos y productores comunitarios (Código comunal, sin DNI obligatorio)
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 120);
            $table->string('sector', 120)->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índice para filtrado inmediato de proveedores activos
            $table->index('activo', 'idx_proveedores_activo');
        });

        // 3. Acopio diario de leche y control sanitario
        Schema::create('acopios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->cascadeOnDelete();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('uuid_offline')->unique();
            $table->date('fecha');
            $table->time('hora');
            $table->decimal('litros', 8, 2);
            $table->decimal('precio_litro', 6, 2)->default(1.65);
            $table->enum('metodo', ['voz', 'manual'])->default('voz');
            $table->string('frase_dictada', 255)->nullable();
            $table->enum('calidad', ['pendiente', 'buena', 'acida'])->default('pendiente');
            $table->enum('destino', ['pendiente', 'queso_yogur', 'quesillo_4_dias'])->default('pendiente');
            $table->timestamps();

            // Índices compuestos de optimización para consultas agregadas y reportes
            $table->index(['fecha', 'calidad'], 'idx_acopios_fecha_calidad');
            $table->index(['proveedor_id', 'fecha'], 'idx_acopios_proveedor_fecha');
        });

        // 4. Lotes de tina de pasteurización y curva térmica
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40)->unique();
            $table->date('fecha');
            $table->decimal('litros', 8, 2);
            $table->decimal('temperatura_actual', 5, 1)->default(72.0);
            $table->decimal('cuajo_ml', 8, 2);
            $table->decimal('sal_kg', 8, 2);
            $table->decimal('cultivo_g', 8, 2);
            $table->unsignedInteger('moldes_producidos')->default(0);
            $table->enum('estado', ['pasteurizando', 'enfriando', 'lista_para_cuajo', 'terminado'])->default('enfriando');
            $table->timestamps();

            // Índice de optimización para monitoreo del lote activo
            $table->index(['fecha', 'estado'], 'idx_lotes_fecha_estado');
        });

        // 5. Movimientos financieros: Adelantos, Ayuda del Día (Ley 29733) y Liquidaciones
        Schema::create('liquidaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->date('fecha');
            $table->enum('tipo', ['quincena', 'adelanto_efectivo', 'adelanto_queso', 'ayuda_del_dia', 'gasto_operativo']);
            $table->string('concepto', 160);
            $table->decimal('litros', 9, 2)->default(0);
            $table->decimal('monto', 10, 2);
            $table->decimal('comision', 6, 2)->default(0.00); // Principio ético LAT: Comisión S/ 0.00
            $table->boolean('descontado')->default(false);
            $table->timestamps();

            // Índices para liquidaciones quincenales y balances contables
            $table->index(['proveedor_id', 'descontado'], 'idx_liquidaciones_prov_descontado');
            $table->index(['fecha', 'tipo'], 'idx_liquidaciones_fecha_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liquidaciones');
        Schema::dropIfExists('lotes');
        Schema::dropIfExists('acopios');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('users');
    }
};
