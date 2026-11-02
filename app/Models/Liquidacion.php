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
        'proveedor_id',
        'fecha',
        'tipo',
        'concepto',
        'litros',
        'monto',
        'comision',
        'descontado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'litros' => 'float',
        'monto' => 'float',
        'comision' => 'float',
        'descontado' => 'boolean',
    ];

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
}
