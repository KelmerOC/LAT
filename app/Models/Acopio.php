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
    /** @var list<string> */
    public const CALIDADES_LIQUIDABLES = ['buena', 'acida'];

    protected $fillable = [
        'planta_id',
        'lote_id',
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
        'evaluado_en',
        'motivo_descarte',
        'evaluado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
        'litros' => 'float',
        'precio_litro' => 'float',
        'evaluado_en' => 'datetime',
    ];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function evaluador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluado_por');
    }

    /** Scope: Solo entregas evaluadas aprovechables (óptimas o con acidez) para liquidación */
    public function scopeLiquidables(Builder $query): Builder
    {
        return $query->whereIn('calidad', self::CALIDADES_LIQUIDABLES);
    }

    /** Scope: Entregas correspondientes a una fecha usando índice B-Tree */
    public function scopeDelDia(Builder $query, ?string $fecha = null): Builder
    {
        $dia = $fecha ?? Carbon::today()->toDateString();

        return $this->scopeDelPeriodo($query, $dia, $dia);
    }

    /** Scope: Rango de fechas para cálculo quincenal sin invalidez de índices */
    public function scopeDelPeriodo(Builder $query, string $desde, string $hasta): Builder
    {
        $driver = $query->getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            return $query->where('fecha', '>=', $desde)
                ->where('fecha', '<=', $hasta.' 23:59:59');
        }

        if ($desde === $hasta) {
            return $query->where('fecha', $desde);
        }

        return $query->whereBetween('fecha', [$desde, $hasta]);
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

    public function scopeDisponiblesParaTina(Builder $q): Builder
    {
        return $q->whereNull('lote_id')->whereIn('calidad', ['buena', 'acida']);
    }
}
