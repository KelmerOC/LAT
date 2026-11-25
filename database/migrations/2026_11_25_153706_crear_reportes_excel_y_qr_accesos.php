<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_excel', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planta_id')->constrained('plantas')->cascadeOnDelete();
            $table->foreignId('remitente_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('destinatario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->string('tipo_reporte', 40)->default('ventas_productor');
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->string('nombre_archivo', 180);
            $table->string('ruta_archivo', 255);
            $table->string('notas', 255)->nullable();
            $table->string('estado', 20)->default('enviado');
            $table->timestamp('leido_at')->nullable();
            $table->timestamps();

            $table->index(['planta_id', 'destinatario_id', 'estado'], 'idx_reportes_dest_estado');
            $table->index(['planta_id', 'created_at'], 'idx_reportes_planta_fecha');
        });

        Schema::create('qr_accesos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planta_id')->constrained('plantas')->cascadeOnDelete();
            $table->foreignId('creado_por')->constrained('users')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->string('etiqueta', 120);
            $table->string('cargo_asignado', 40)->default('recolector_ruta');
            $table->unsignedInteger('max_usos')->default(10);
            $table->unsignedInteger('usos_actuales')->default(0);
            $table->timestamp('expira_en');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['planta_id', 'activo', 'expira_en'], 'idx_qr_planta_activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_accesos');
        Schema::dropIfExists('reportes_excel');
    }
};
