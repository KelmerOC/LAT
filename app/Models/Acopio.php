<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entrega diaria de leche y evaluación sanitaria por prueba de alcohol.
 */
class Acopio extends Model
{
    protected $fillable = [
        'planta_id',
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

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

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
        return $query->whereDate('fecha', $fecha ?? Carbon::today()->toDateString());
    }

    /** Scope: Rango de fechas para cálculo quincenal */
    public function scopeDelPeriodo(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta);
    }

    /** Scope: Solo leche apta para pasteurización en tina */
    public function scopeBuena(Builder $query): Builder
    {
        return $query->where('calidad', 'buena');
    }

    public function scopeDePlanta(Builder $query, int $plantaId): Builder
    {
        return $query->where('planta_id', $plantaId);
    }
}
