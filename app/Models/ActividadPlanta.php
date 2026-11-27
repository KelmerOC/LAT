<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de bitácora operativa y financiera por planta para auditoría e informes.
 */
class ActividadPlanta extends Model
{
    protected $table = 'actividades_planta';

    public $timestamps = false;

    protected $fillable = [
        'planta_id',
        'user_id',
        'modulo',
        'accion',
        'detalle',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeDePlanta(Builder $q, int $plantaId): Builder
    {
        return $q->where('planta_id', $plantaId);
    }
}
