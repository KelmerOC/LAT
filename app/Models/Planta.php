<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Planta láctea o quesería gestionada en la plataforma.
 */
class Planta extends Model
{
    protected $table = 'plantas';

    protected $fillable = [
        'codigo',
        'nombre',
        'ubicacion',
        'precio_litro',
        'activa',
    ];

    protected $casts = [
        'precio_litro' => 'float',
        'activa' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class);
    }

    public function tinas(): HasMany
    {
        return $this->hasMany(Tina::class);
    }

    public function acopios(): HasMany
    {
        return $this->hasMany(Acopio::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function liquidaciones(): HasMany
    {
        return $this->hasMany(Liquidacion::class);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activa', true);
    }
}
