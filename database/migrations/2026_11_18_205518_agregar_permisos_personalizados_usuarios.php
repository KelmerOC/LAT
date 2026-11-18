<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para soportar usuarios personalizados, permisos granulares y rango.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('rango', 40)->default('operador')->after('cargo');
            $table->json('permisos')->nullable()->after('rango');
            $table->foreignId('creado_por')->nullable()->after('activo')->constrained('users')->nullOnDelete();
        });

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY COLUMN cargo ENUM('administracion','encargado_planta','recolector_ruta','control_tina','contador_externo','personalizado') NOT NULL DEFAULT 'administracion'");
        } elseif ($driver === 'sqlite') {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('cargo_tmp', 40)->default('administracion')->after('password');
            });
            DB::statement('UPDATE users SET cargo_tmp = cargo');
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('cargo');
            });
            Schema::table('users', function (Blueprint $table): void {
                $table->renameColumn('cargo_tmp', 'cargo');
            });
        }
    }

    /**
     * Revierte los cambios en la tabla users.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("UPDATE users SET cargo = 'recolector_ruta' WHERE cargo = 'personalizado'");
            DB::statement("ALTER TABLE users MODIFY COLUMN cargo ENUM('administracion','encargado_planta','recolector_ruta','control_tina','contador_externo') NOT NULL DEFAULT 'administracion'");
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('creado_por');
            $table->dropColumn(['permisos', 'rango']);
        });
    }
};
