<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ============================================================================
 * MODELO DE DATOS: ACOPIO DIARIO DE LECHE Y TRIAGE DE CALIDAD
 * ============================================================================
 * Registra cada entrega de leche (por voz o manual) y su evaluación sanitaria
 * mediante la prueba rápida de alcohol (buena -> tina queso / ácida -> quesillos).
 */
class Acopio extends Model
{
    protected $fillable = [
        'proveedor_id',
        'registrado_por',
        'uuid_offline',
        'fecha',
        'hora',
        'litros',
        'precio_litro',
        'metodo',
        'frase_dictada',
        'calidad',
        'destino',
    ];

    protected $casts = [
        'fecha' => 'date',
        'litros' => 'float',
        'precio_litro' => 'float',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /** Scope: Entregas correspondientes al día de hoy */
    public function scopeDelDia(Builder $query, ?string $fecha = null): Builder
    {
        return $query->where('fecha', $fecha ?? Carbon::today()->toDateString());
    }

    /** Scope: Rango de fechas para cálculo quincenal */
    public function scopeDelPeriodo(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    /** Scope: Solo leche apta para pasteurización en tina */
    public function scopeBuena(Builder $query): Builder
    {
        return $query->where('calidad', 'buena');
    }
}
