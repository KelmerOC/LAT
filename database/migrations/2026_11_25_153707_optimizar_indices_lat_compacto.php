<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('liquidaciones', function (Blueprint $table): void {
            $table->index(['planta_id', 'tipo', 'fecha'], 'idx_liq_planta_tipo_fecha');
            $table->index(['planta_id', 'descontado', 'tipo', 'fecha'], 'idx_liq_planta_desc_tipo_fecha');
            $table->index(['planta_id', 'id'], 'idx_liq_planta_id');
        });

        Schema::table('acopios', function (Blueprint $table): void {
            $table->index(['planta_id', 'proveedor_id', 'fecha'], 'idx_acop_planta_prov_fecha');
            $table->index(['planta_id', 'id'], 'idx_acop_planta_id');
        });

        Schema::table('lotes', function (Blueprint $table): void {
            $table->index(['planta_id', 'fecha'], 'idx_lotes_planta_fecha');
            $table->index(['tina_id', 'estado', 'id'], 'idx_lotes_tina_estado_id');
        });

        Schema::table('proveedores', function (Blueprint $table): void {
            $table->dropUnique(['codigo']);
            $table->unique(['planta_id', 'codigo'], 'uq_proveedores_planta_codigo');
            $table->index(['planta_id', 'activo', 'nombre'], 'idx_prov_planta_activo_nombre');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index(['planta_id', 'cargo', 'name'], 'idx_users_planta_cargo_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('idx_users_planta_cargo_name');
        });

        Schema::table('proveedores', function (Blueprint $table): void {
            $table->dropIndex('idx_prov_planta_activo_nombre');
            $table->dropUnique('uq_proveedores_planta_codigo');
            $table->unique('codigo');
        });

        Schema::table('lotes', function (Blueprint $table): void {
            $table->dropIndex('idx_lotes_tina_estado_id');
            $table->dropIndex('idx_lotes_planta_fecha');
        });

        Schema::table('acopios', function (Blueprint $table): void {
            $table->dropIndex('idx_acop_planta_id');
            $table->dropIndex('idx_acop_planta_prov_fecha');
        });

        Schema::table('liquidaciones', function (Blueprint $table): void {
            $table->dropIndex('idx_liq_planta_id');
            $table->dropIndex('idx_liq_planta_desc_tipo_fecha');
            $table->dropIndex('idx_liq_planta_tipo_fecha');
        });
    }
};
