<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Tina de pasteurización y transformación láctea por planta.
 */
class Tina extends Model
{
    protected $table = 'tinas';

    protected $fillable = [
        'planta_id',
        'codigo',
        'nombre',
        'capacidad_litros',
        'tipo',
        'activa',
    ];

    protected $casts = [
        'capacidad_litros' => 'float',
        'activa' => 'boolean',
    ];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function loteActivo(): HasOne
    {
        return $this->hasOne(Lote::class)
            ->whereIn('estado', ['pasteurizando', 'enfriando', 'lista_para_cuajo'])
            ->latestOfMany('id');
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activa', true);
    }

    public function scopeDePlanta(Builder $query, int $plantaId): Builder
    {
        return $query->where('planta_id', $plantaId);
    }
}
