<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'receta_nombre',
        'duracion_minutos',
        'iniciado_en',
        'alerta_fin_enviada',
    ];

    protected $casts = [
        'fecha' => 'date',
        'litros' => 'float',
        'temperatura_actual' => 'float',
        'cuajo_ml' => 'float',
        'sal_kg' => 'float',
        'cultivo_g' => 'float',
        'moldes_producidos' => 'integer',
        'duracion_minutos' => 'integer',
        'iniciado_en' => 'datetime',
        'alerta_fin_enviada' => 'boolean',
    ];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    public function tina(): BelongsTo
    {
        return $this->belongsTo(Tina::class);
    }

    public function acopios(): HasMany
    {
        return $this->hasMany(Acopio::class);
    }

    public function finEstimadoEn(): ?Carbon
    {
        $inicio = $this->iniciado_en ?? $this->created_at;
        if (! $inicio instanceof Carbon) {
            return null;
        }

        return $inicio->copy()->addMinutes(max(1, (int) ($this->duracion_minutos ?? 45)));
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
