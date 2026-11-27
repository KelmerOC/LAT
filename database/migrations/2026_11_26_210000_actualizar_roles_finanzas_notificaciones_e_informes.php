<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Actualiza notificaciones, liquidaciones, permisos de roles y crea bitácora e informes por planta.
     */
    public function up(): void
    {
        Schema::table('notificaciones_panel', function (Blueprint $table): void {
            $table->foreignId('destinatario_id')
                ->nullable()
                ->after('creado_por')
                ->constrained('users')
                ->nullOnDelete();
            $table->json('roles_destino')->nullable()->after('destinatario_id');
            $table->string('color_tema', 20)->default('default')->after('roles_destino');
            $table->boolean('alcance_global')->default(false)->after('color_tema');

            $table->index(['alcance_global', 'planta_id', 'id'], 'idx_notif_global_planta_id');
        });

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE liquidaciones MODIFY COLUMN tipo VARCHAR(40) NOT NULL DEFAULT 'gasto_operativo'");
        }

        Schema::table('liquidaciones', function (Blueprint $table): void {
            $table->string('producto_categoria', 60)->nullable()->after('tipo');
            $table->string('canal_referencia', 60)->nullable()->after('producto_categoria');
            $table->decimal('precio_unitario', 10, 2)->nullable()->after('cantidad_unidades');
            $table->string('estado_pago', 20)->default('pendiente')->after('descontado');
        });

        Schema::create('actividades_planta', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planta_id')->constrained('plantas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('modulo', 30);
            $table->string('accion', 60);
            $table->string('detalle', 320);
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['planta_id', 'created_at'], 'idx_act_planta_created');
        });

        Schema::create('informes_actividad_planta', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planta_id')->constrained('plantas')->cascadeOnDelete();
            $table->foreignId('generado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('origen', 30)->default('automatico_24h');
            $table->timestamp('periodo_inicio')->nullable();
            $table->timestamp('periodo_fin')->nullable();
            $table->string('resumen_titulo', 160);
            $table->longText('contenido_texto');
            $table->unsignedInteger('total_acciones')->default(0);
            $table->json('metricas')->nullable();
            $table->timestamps();

            $table->index(['planta_id', 'created_at'], 'idx_inf_planta_created');
        });

        $plantillaEncargado = [
            'dashboard.ver',
            'acopio.ver',
            'acopio.crear',
            'acopio.editar',
            'acopio.proveedores',
            'calidad.ver',
            'calidad.evaluar',
            'tinas.ver',
            'tinas.operar',
            'tinas.gestionar',
            'finanzas.ver',
            'finanzas.registrar',
            'finanzas.editar',
            'finanzas.exportar',
            'finanzas.enviar_excel',
            'estadisticas.ver',
            'admin.usuarios',
        ];

        DB::table('users')
            ->select(['id', 'cargo', 'permisos'])
            ->orderBy('id')
            ->chunkById(200, function ($usuarios) use ($plantillaEncargado): void {
                foreach ($usuarios as $usuario) {
                    $permisos = is_string($usuario->permisos)
                        ? json_decode($usuario->permisos, true)
                        : $usuario->permisos;

                    if (is_array($permisos)) {
                        $actualizados = array_values(array_filter(
                            $permisos,
                            fn ($permiso): bool => is_string($permiso) && $permiso !== 'admin.qr'
                        ));

                        if ($usuario->cargo === 'encargado_planta') {
                            $actualizados = array_values(array_diff($actualizados, ['admin.plantas', 'dashboard.ver_red']));
                            if (! in_array('admin.usuarios', $actualizados, true)) {
                                $actualizados[] = 'admin.usuarios';
                            }
                        }

                        $actualizados = array_values(array_unique($actualizados));
                        if ($actualizados !== $permisos) {
                            DB::table('users')
                                ->where('id', $usuario->id)
                                ->update(['permisos' => json_encode($actualizados)]);
                        }
                    } elseif ($usuario->cargo === 'encargado_planta') {
                        DB::table('users')
                            ->where('id', $usuario->id)
                            ->update(['permisos' => json_encode($plantillaEncargado)]);
                    }
                }
            });
    }

    /**
     * Revierte los cambios de notificaciones, liquidaciones, bitácora e informes.
     */
    public function down(): void
    {
        Schema::dropIfExists('informes_actividad_planta');
        Schema::dropIfExists('actividades_planta');

        Schema::table('liquidaciones', function (Blueprint $table): void {
            $table->dropColumn([
                'producto_categoria',
                'canal_referencia',
                'precio_unitario',
                'estado_pago',
            ]);
        });

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("UPDATE liquidaciones SET tipo = 'gasto_operativo' WHERE tipo NOT IN ('quincena','adelanto_efectivo','adelanto_queso','ayuda_del_dia','gasto_operativo','venta_producto')");
            DB::statement("ALTER TABLE liquidaciones MODIFY COLUMN tipo ENUM('quincena','adelanto_efectivo','adelanto_queso','ayuda_del_dia','gasto_operativo','venta_producto') NOT NULL");
        }

        Schema::table('notificaciones_panel', function (Blueprint $table): void {
            $table->dropIndex('idx_notif_global_planta_id');
            $table->dropConstrainedForeignId('destinatario_id');
            $table->dropColumn(['roles_destino', 'color_tema', 'alcance_global']);
        });
    }
};
