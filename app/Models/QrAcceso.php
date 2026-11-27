<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Enlace de acceso/registro móvil mediante código QR con vigencia y límite de usos.
 */
class QrAcceso extends Model
{
    protected $table = 'qr_accesos';

    protected $fillable = [
        'planta_id',
        'creado_por',
        'token_hash',
        'etiqueta',
        'cargo_asignado',
        'max_usos',
        'usos_actuales',
        'expira_en',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'max_usos' => 'integer',
            'usos_actuales' => 'integer',
            'expira_en' => 'datetime',
            'activo' => 'boolean',
        ];
    }

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function scopeDePlanta(Builder $query, int $plantaId): Builder
    {
        return $query->where('planta_id', $plantaId);
    }

    public function estaVigente(): bool
    {
        if (! $this->activo) {
            return false;
        }

        if ($this->expira_en === null || ! $this->expira_en->isFuture()) {
            return false;
        }

        return $this->usos_actuales < $this->max_usos;
    }
}
