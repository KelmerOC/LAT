<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * ============================================================================
 * MODELO DE DATOS: USUARIO Y CONTROL DE ACCESO POR ROLES (RBAC)
 * ============================================================================
 * Representa al personal de la microempresa quesera.
 * Los operadores se autentican mediante su identificador 'usuario' o 'email'.
 */
class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'planta_id',
        'name',
        'usuario',
        'email',
        'password',
        'cargo',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    /** Scope de optimización: Solo usuarios autorizados y activos */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    // Métodos semánticos para verificación rápida de permisos
    public function esAdmin(): bool
    {
        return $this->cargo === 'administracion';
    }

    public function puedeRegistrarAcopio(): bool
    {
        return in_array($this->cargo, ['administracion', 'encargado_planta', 'recolector_ruta']);
    }

    public function puedeEvaluarCalidad(): bool
    {
        return in_array($this->cargo, ['administracion', 'encargado_planta', 'control_tina']);
    }

    public function puedeGestionarTina(): bool
    {
        return in_array($this->cargo, ['administracion', 'encargado_planta', 'control_tina']);
    }

    public function puedeVerFinanzas(): bool
    {
        return in_array($this->cargo, ['administracion', 'contador_externo']);
    }
}
