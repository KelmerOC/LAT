<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de intercambio de reportes Excel ordenados entre roles de la planta.
 */
class ReporteExcel extends Model
{
    protected $table = 'reportes_excel';

    protected $fillable = [
        'planta_id',
        'remitente_id',
        'destinatario_id',
        'proveedor_id',
        'tipo_reporte',
        'fecha_desde',
        'fecha_hasta',
        'nombre_archivo',
        'ruta_archivo',
        'notas',
        'estado',
        'leido_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
            'leido_at' => 'datetime',
        ];
    }

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remitente_id');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinatario_id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function scopeDePlanta(Builder $query, int $plantaId): Builder
    {
        return $query->where('planta_id', $plantaId);
    }
}
