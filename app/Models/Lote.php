<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lote de tina quesera con monitoreo térmico e insumos calculados.
 */
class Lote extends Model
{
    protected $fillable = [
        'planta_id',
        'tina_id',
        'codigo',
        'fecha',
        'litros',
        'temperatura_actual',
        'cuajo_ml',
        'sal_kg',
        'cultivo_g',
        'moldes_producidos',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'litros' => 'float',
        'temperatura_actual' => 'float',
        'cuajo_ml' => 'float',
        'sal_kg' => 'float',
        'cultivo_g' => 'float',
        'moldes_producidos' => 'integer',
    ];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    public function tina(): BelongsTo
    {
        return $this->belongsTo(Tina::class);
    }

    /** Scope: Lote que actualmente se encuentra en enfriamiento o pasteurizando */
    public function scopeEnProceso(Builder $query): Builder
    {
        return $query->whereIn('estado', ['pasteurizando', 'enfriando', 'lista_para_cuajo']);
    }

    public function scopeDePlanta(Builder $query, int $plantaId): Builder
    {
        return $query->where('planta_id', $plantaId);
    }
}
