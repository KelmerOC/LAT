<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notificación operativa interna por planta con lectura registrada por usuario.
 */
class NotificacionPanel extends Model
{
    protected $table = 'notificaciones_panel';

    protected $fillable = [
        'planta_id',
        'tipo',
        'modulo_destino',
        'titulo',
        'mensaje',
        'datos',
        'creado_por',
        'leida_por',
    ];

    protected $casts = [
        'datos' => 'array',
        'leida_por' => 'array',
    ];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function scopeDePlanta(Builder $q, int $plantaId): Builder
    {
        return $q->where('planta_id', $plantaId);
    }

    public function estaLeidaPor(int $userId): bool
    {
        $leidaPor = $this->leida_por;
        if (! is_array($leidaPor)) {
            return false;
        }

        return in_array($userId, array_map('intval', $leidaPor), true);
    }

    public function marcarLeidaPor(int $userId): void
    {
        if ($this->estaLeidaPor($userId)) {
            return;
        }

        $leidaPor = is_array($this->leida_por) ? array_map('intval', $this->leida_por) : [];
        $leidaPor[] = $userId;

        $this->update([
            'leida_por' => array_values(array_unique($leidaPor)),
        ]);
    }
}
