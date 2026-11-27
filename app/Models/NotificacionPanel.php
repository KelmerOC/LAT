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
        'destinatario_id',
        'roles_destino',
        'color_tema',
        'alcance_global',
        'leida_por',
    ];

    protected $casts = [
        'datos' => 'array',
        'roles_destino' => 'array',
        'alcance_global' => 'boolean',
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

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinatario_id');
    }

    public function scopeDePlanta(Builder $q, int $plantaId): Builder
    {
        return $q->where('planta_id', $plantaId);
    }

    /**
     * Determina si la notificación debe mostrarse al usuario según destinatario, rol y planta activa.
     */
    public function esVisiblePara(User $usuario, int $plantaActivaId): bool
    {
        if ($this->destinatario_id !== null) {
            return (int) $this->destinatario_id === (int) $usuario->id;
        }

        if (is_array($this->roles_destino) && $this->roles_destino !== []) {
            if (in_array('administracion', $this->roles_destino, true) && $usuario->esAdmin()) {
                return $this->alcance_global || (int) $this->planta_id === $plantaActivaId;
            }

            if ((int) $this->planta_id !== $plantaActivaId) {
                return false;
            }

            return $this->coincideRolDestino($usuario, $this->roles_destino);
        }

        if ((int) $this->planta_id !== $plantaActivaId) {
            return false;
        }

        if ($this->modulo_destino === 'tinas') {
            return $this->coincideRolDestino($usuario, ['encargado_planta', 'control_tina']);
        }

        return true;
    }

    /**
     * @param  array<int, string>  $rolesDestino
     */
    private function coincideRolDestino(User $usuario, array $rolesDestino): bool
    {
        if (in_array($usuario->cargo, $rolesDestino, true)) {
            return true;
        }

        return $usuario->cargo === 'personalizado'
            && in_array('control_tina', $rolesDestino, true)
            && $usuario->tienePermiso('tinas.operar');
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
