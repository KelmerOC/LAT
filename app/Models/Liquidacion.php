<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ============================================================================
 * MODELO DE DATOS: LIQUIDACIÓN QUINCENAL Y MOVIMIENTOS FINANCIEROS
 * ============================================================================
 * Maneja adelantos, pagos netos a ganaderos (comisión S/ 0.00), ventas y
 * gastos operativos de mano de obra eventual ("Pago de Ayuda del Día").
 */
class Liquidacion extends Model
{
    protected $table = 'liquidaciones';

    protected $fillable = [
        'planta_id',
        'proveedor_id',
        'fecha',
        'tipo',
        'concepto',
        'litros',
        'cantidad_unidades',
        'monto',
        'comision',
        'descontado',
    ];

    protected $casts = [
        'fecha'             => 'date',
        'litros'            => 'float',
        'cantidad_unidades' => 'integer',
        'monto'             => 'float',
        'comision'          => 'float',
        'descontado'        => 'boolean',
    ];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    /** Scope: Adelantos pendientes de descuento */
    public function scopeAdelantosPendientes(Builder $query, int $proveedorId): Builder
    {
        return $query->where('proveedor_id', $proveedorId)
            ->whereIn('tipo', ['adelanto_efectivo', 'adelanto_queso'])
            ->where('descontado', false);
    }

    public function scopeDePlanta(Builder $query, int $plantaId): Builder
    {
        return $query->where('planta_id', $plantaId);
    }
}
