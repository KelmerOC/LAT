<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plantas', function (Blueprint $table): void {
            $table->softDeletes()->after('activa');
            $table->foreignId('eliminado_por')
                ->nullable()
                ->after('deleted_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->index('deleted_at', 'idx_plantas_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('plantas', function (Blueprint $table): void {
            $table->dropIndex('idx_plantas_deleted_at');
            $table->dropConstrainedForeignId('eliminado_por');
            $table->dropSoftDeletes();
        });
    }
};
