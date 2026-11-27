<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Actualiza acopios, tinas, lotes, plantas y crea notificaciones del panel.
     */
    public function up(): void
    {
        Schema::table('acopios', function (Blueprint $table): void {
            $table->foreignId('lote_id')
                ->nullable()
                ->after('planta_id')
                ->constrained('lotes')
                ->nullOnDelete();
            $table->timestamp('evaluado_en')->nullable()->after('destino');
            $table->index(['planta_id', 'lote_id', 'calidad'], 'idx_acopios_planta_lote_calidad');
        });

        Schema::table('tinas', function (Blueprint $table): void {
            $table->string('tipo', 80)->default('principal_queso')->change();
        });

        Schema::table('lotes', function (Blueprint $table): void {
            $table->string('receta_nombre', 80)->nullable()->after('estado');
            $table->unsignedSmallInteger('duracion_minutos')->default(45)->after('receta_nombre');
            $table->timestamp('iniciado_en')->nullable()->after('duracion_minutos');
            $table->boolean('alerta_fin_enviada')->default(false)->after('iniciado_en');
        });

        Schema::table('plantas', function (Blueprint $table): void {
            $table->json('recetas_base')->nullable()->after('activa');
        });

        Schema::create('notificaciones_panel', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planta_id')->constrained('plantas')->cascadeOnDelete();
            $table->string('tipo', 40);
            $table->string('modulo_destino', 30)->default('general');
            $table->string('titulo', 140);
            $table->string('mensaje', 320);
            $table->json('datos')->nullable();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->json('leida_por')->nullable();
            $table->timestamps();

            $table->index(['planta_id', 'id'], 'idx_notif_planta_id');
        });

        DB::table('users')
            ->where('cargo', 'recolector_ruta')
            ->select(['id', 'permisos'])
            ->orderBy('id')
            ->chunkById(200, function ($usuarios): void {
                foreach ($usuarios as $usuario) {
                    $permisos = is_string($usuario->permisos)
                        ? json_decode($usuario->permisos, true)
                        : $usuario->permisos;

                    if (! is_array($permisos)) {
                        $permisos = ['acopio.ver', 'acopio.crear', 'acopio.editar', 'acopio.proveedores'];
                    } elseif (! in_array('acopio.editar', $permisos, true)) {
                        $permisos[] = 'acopio.editar';
                    } else {
                        continue;
                    }

                    DB::table('users')
                        ->where('id', $usuario->id)
                        ->update(['permisos' => json_encode(array_values(array_unique($permisos)))]);
                }
            });
    }

    /**
     * Revierte los cambios de flujo de tinas, recetas base y notificaciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones_panel');

        Schema::table('plantas', function (Blueprint $table): void {
            $table->dropColumn('recetas_base');
        });

        Schema::table('lotes', function (Blueprint $table): void {
            $table->dropColumn(['receta_nombre', 'duracion_minutos', 'iniciado_en', 'alerta_fin_enviada']);
        });

        DB::table('tinas')
            ->whereNotIn('tipo', ['principal_queso', 'quesillo', 'yogur', 'multiproposito'])
            ->update(['tipo' => 'multiproposito']);

        Schema::table('tinas', function (Blueprint $table): void {
            $table->enum('tipo', ['principal_queso', 'quesillo', 'yogur', 'multiproposito'])
                ->default('principal_queso')
                ->change();
        });

        Schema::table('acopios', function (Blueprint $table): void {
            $table->dropIndex('idx_acopios_planta_lote_calidad');
            $table->dropConstrainedForeignId('lote_id');
            $table->dropColumn('evaluado_en');
        });
    }
};
