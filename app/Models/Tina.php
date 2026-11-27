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
    /** @var list<string> */
    public const TIPOS_PREESTABLECIDOS = ['principal_queso', 'quesillo', 'yogur', 'multiproposito'];

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

    public function esTipoPreestablecido(): bool
    {
        return in_array($this->tipo, self::TIPOS_PREESTABLECIDOS, true);
    }

    /**
     * Normaliza etiquetas traducidas de tipos preestablecidos a su clave canónica.
     */
    public static function normalizarTipo(string $valor): string
    {
        $limpio = trim($valor);
        $norm = strtolower(str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'],
            ['a', 'e', 'i', 'o', 'u', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'u', 'n'],
            $limpio
        ));
        $norm = preg_replace('/\s+/', ' ', $norm) ?? $norm;

        $mapa = [
            'principal_queso' => 'principal_queso',
            'queso principal' => 'principal_queso',
            'hatun kisu' => 'principal_queso',
            'main cheese' => 'principal_queso',
            'quesillo' => 'quesillo',
            'quesillo 4 dias' => 'quesillo',
            "4 p'unchaw kisillu" => 'quesillo',
            '4-day quesillo' => 'quesillo',
            '4 day quesillo' => 'quesillo',
            'yogur' => 'yogur',
            'yogurt' => 'yogur',
            'multiproposito' => 'multiproposito',
            'tukuy ruwanapaq' => 'multiproposito',
            'multipurpose' => 'multiproposito',
        ];

        return $mapa[$norm] ?? $limpio;
    }
}
