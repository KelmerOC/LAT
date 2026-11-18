<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================================
 * MIGRACIÓN EVOLUTIVA: MULTI-PLANTA, MÚLTIPLES TINAS Y VENTAS/ESTADÍSTICAS
 * ============================================================================
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Plantas Lácteas
        Schema::create('plantas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 140);
            $table->string('ubicacion', 140)->default('Cusco Provincia');
            $table->decimal('precio_litro', 6, 2)->default(1.65);
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index('activa', 'idx_plantas_activa');
        });

        // 2. Tabla de Tinas por Planta
        Schema::create('tinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planta_id')->constrained('plantas')->cascadeOnDelete();
            $table->string('codigo', 30);
            $table->string('nombre', 100);
            $table->decimal('capacidad_litros', 8, 2)->default(500.00);
            $table->enum('tipo', ['principal_queso', 'quesillo', 'yogur', 'multiproposito'])->default('principal_queso');
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->unique(['planta_id', 'codigo'], 'uq_tinas_planta_codigo');
            $table->index(['planta_id', 'activa'], 'idx_tinas_planta_activa');
        });

        // 3. Vincular entidades existentes a planta_id y tina_id
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('planta_id')->nullable()->after('id')->constrained('plantas')->nullOnDelete();
        });

        Schema::table('proveedores', function (Blueprint $table) {
            $table->foreignId('planta_id')->nullable()->after('id')->constrained('plantas')->cascadeOnDelete();
            $table->index(['planta_id', 'activo'], 'idx_proveedores_planta_activo');
        });

        Schema::table('acopios', function (Blueprint $table) {
            $table->foreignId('planta_id')->nullable()->after('id')->constrained('plantas')->cascadeOnDelete();
            $table->index(['planta_id', 'fecha'], 'idx_acopios_planta_fecha');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->foreignId('planta_id')->nullable()->after('id')->constrained('plantas')->cascadeOnDelete();
            $table->foreignId('tina_id')->nullable()->after('planta_id')->constrained('tinas')->nullOnDelete();
            $table->index(['planta_id', 'estado'], 'idx_lotes_planta_estado');
        });

        Schema::table('liquidaciones', function (Blueprint $table) {
            $table->foreignId('planta_id')->nullable()->after('id')->constrained('plantas')->cascadeOnDelete();
            $table->unsignedInteger('cantidad_unidades')->default(0)->after('litros');
            $table->index(['planta_id', 'fecha', 'tipo'], 'idx_liquidaciones_planta_fecha_tipo');
        });

        // Ampliar enum 'tipo' en liquidaciones para incluir 'venta_producto'
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE liquidaciones MODIFY COLUMN tipo ENUM('quincena','adelanto_efectivo','adelanto_queso','ayuda_del_dia','gasto_operativo','venta_producto') NOT NULL");
        } elseif ($driver === 'sqlite') {
            Schema::table('liquidaciones', function (Blueprint $table) {
                $table->dropIndex('idx_liquidaciones_fecha_tipo');
                $table->dropIndex('idx_liquidaciones_planta_fecha_tipo');
                $table->string('tipo_tmp', 40)->default('gasto_operativo');
            });
            DB::statement('UPDATE liquidaciones SET tipo_tmp = tipo');
            Schema::table('liquidaciones', function (Blueprint $table) {
                $table->dropColumn('tipo');
            });
            Schema::table('liquidaciones', function (Blueprint $table) {
                $table->renameColumn('tipo_tmp', 'tipo');
            });
            Schema::table('liquidaciones', function (Blueprint $table) {
                $table->index(['fecha', 'tipo'], 'idx_liquidaciones_fecha_tipo');
                $table->index(['planta_id', 'fecha', 'tipo'], 'idx_liquidaciones_planta_fecha_tipo');
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("DELETE FROM liquidaciones WHERE tipo = 'venta_producto'");
            DB::statement("ALTER TABLE liquidaciones MODIFY COLUMN tipo ENUM('quincena','adelanto_efectivo','adelanto_queso','ayuda_del_dia','gasto_operativo') NOT NULL");
        }

        Schema::table('liquidaciones', function (Blueprint $table) {
            $table->dropIndex('idx_liquidaciones_planta_fecha_tipo');
            $table->dropConstrainedForeignId('planta_id');
            $table->dropColumn('cantidad_unidades');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropIndex('idx_lotes_planta_estado');
            $table->dropConstrainedForeignId('tina_id');
            $table->dropConstrainedForeignId('planta_id');
        });

        Schema::table('acopios', function (Blueprint $table) {
            $table->dropIndex('idx_acopios_planta_fecha');
            $table->dropConstrainedForeignId('planta_id');
        });

        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropIndex('idx_proveedores_planta_activo');
            $table->dropConstrainedForeignId('planta_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('planta_id');
        });

        Schema::dropIfExists('tinas');
        Schema::dropIfExists('plantas');
    }
};
