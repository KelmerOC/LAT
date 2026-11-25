<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Productor ganadero comunitario asociado a una planta.
 */
class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'planta_id',
        'codigo',
        'nombre',
        'sector',
        'latitud',
        'longitud',
        'activo',
    ];

    protected $casts = [
        'latitud' => 'float',
        'longitud' => 'float',
        'activo' => 'boolean',
    ];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    /** Relación: Entregas de leche registradas por el ganadero */
    public function acopios(): HasMany
    {
        return $this->hasMany(Acopio::class);
    }

    /** Relación: Adelantos y liquidaciones del productor */
    public function liquidaciones(): HasMany
    {
        return $this->hasMany(Liquidacion::class);
    }

    /** Scope: Solo ganaderos activos en la ruta de acopio */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /** Scope: Ordenamiento alfabético para listas de selección */
    public function scopeOrdenadosPorNombre(Builder $query): Builder
    {
        return $query->orderBy('nombre', 'asc');
    }

    public function scopeDePlanta(Builder $query, int $plantaId): Builder
    {
        return $query->where('planta_id', $plantaId);
    }
}
