<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================================
 * MODELO DE DATOS: LOTE DE TINA QUESERA Y MONITOR TÉRMICO
 * ============================================================================
 * Controla el ciclo térmico de pasteurización (72 °C) y enfriamiento hacia
 * el punto óptimo de cuajado (36-40 °C), además de registrar los insumos.
 */
class Lote extends Model
{
    protected $fillable = [
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

    /** Scope: Lote que actualmente se encuentra en enfriamiento o pasteurizando */
    public function scopeEnProceso(Builder $query): Builder
    {
        return $query->whereIn('estado', ['pasteurizando', 'enfriando', 'lista_para_cuajo']);
    }
}
