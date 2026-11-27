<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de aplicaciones de adelantos y conserva entregas descartadas con trazabilidad.
     */
    public function up(): void
    {
        Schema::create('adelanto_aplicaciones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planta_id')->constrained('plantas')->cascadeOnDelete();
            $table->foreignId('quincena_liquidacion_id')->constrained('liquidaciones')->cascadeOnDelete();
            $table->foreignId('adelanto_liquidacion_id')->constrained('liquidaciones')->cascadeOnDelete();
            $table->decimal('monto_aplicado', 10, 2);
            $table->timestamps();

            $table->unique(['quincena_liquidacion_id', 'adelanto_liquidacion_id'], 'uq_adelanto_aplicacion_quincena');
            $table->index(['planta_id', 'adelanto_liquidacion_id'], 'idx_adel_apl_planta_adelanto');
        });

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE acopios MODIFY COLUMN calidad VARCHAR(20) NOT NULL DEFAULT 'pendiente'");
            DB::statement("ALTER TABLE acopios MODIFY COLUMN destino VARCHAR(30) NOT NULL DEFAULT 'pendiente'");
        } else {
            Schema::table('acopios', function (Blueprint $table): void {
                $table->string('calidad', 20)->default('pendiente')->change();
                $table->string('destino', 30)->default('pendiente')->change();
            });
        }

        Schema::table('acopios', function (Blueprint $table): void {
            $table->string('motivo_descarte', 255)->nullable()->after('evaluado_en');
            $table->foreignId('evaluado_por')
                ->nullable()
                ->after('motivo_descarte')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Revierte la tabla de aplicaciones de adelantos y columnas de descarte en acopios.
     */
    public function down(): void
    {
        Schema::table('acopios', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('evaluado_por');
            $table->dropColumn('motivo_descarte');
        });

        DB::table('acopios')->where('calidad', 'descartada')->update(['calidad' => 'pendiente']);
        DB::table('acopios')->where('destino', 'descartada')->update(['destino' => 'pendiente']);

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE acopios MODIFY COLUMN calidad ENUM('pendiente', 'buena', 'acida') NOT NULL DEFAULT 'pendiente'");
            DB::statement("ALTER TABLE acopios MODIFY COLUMN destino ENUM('pendiente', 'queso_yogur', 'quesillo_4_dias') NOT NULL DEFAULT 'pendiente'");
        } else {
            Schema::table('acopios', function (Blueprint $table): void {
                $table->enum('calidad', ['pendiente', 'buena', 'acida'])->default('pendiente')->change();
                $table->enum('destino', ['pendiente', 'queso_yogur', 'quesillo_4_dias'])->default('pendiente')->change();
            });
        }

        Schema::dropIfExists('adelanto_aplicaciones');
    }
};
