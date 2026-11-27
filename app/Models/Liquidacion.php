<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Movimiento financiero o liquidación quincenal asociado a una planta.
 */
class Liquidacion extends Model
{
    /** @var list<string> */
    public const TIPOS_VENTA = ['venta_producto'];

    /** @var list<string> */
    public const TIPOS_SUELDOS_PAGOS = [
        'sueldo_personal',
        'ayuda_del_dia',
        'adelanto_efectivo',
        'adelanto_queso',
        'gasto_operativo',
    ];

    /** @var list<string> */
    public const PRODUCTOS_VENTA = [
        'queso_andino',
        'quesillo_fresco',
        'yogur_natural',
        'mantequilla',
        'otro',
    ];

    /** @var list<string> */
    public const CANALES_VENTA = [
        'feria_local',
        'tienda_bodega',
        'cliente_directo',
        'planta_interno',
        'otro',
    ];

    /** @var list<string> */
    public const ESTADOS_PAGO = ['pendiente', 'parcial', 'pagado'];

    protected $table = 'liquidaciones';

    protected $fillable = [
        'planta_id',
        'proveedor_id',
        'fecha',
        'tipo',
        'producto_categoria',
        'canal_referencia',
        'concepto',
        'litros',
        'cantidad_unidades',
        'precio_unitario',
        'monto',
        'comision',
        'descontado',
        'estado_pago',
    ];

    protected $casts = [
        'fecha' => 'date',
        'litros' => 'float',
        'cantidad_unidades' => 'integer',
        'precio_unitario' => 'float',
        'monto' => 'float',
        'comision' => 'float',
        'descontado' => 'boolean',
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
