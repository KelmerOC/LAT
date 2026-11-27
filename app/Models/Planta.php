<?php

namespace App\Models;

use App\Services\TinaWorkflowService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Planta láctea o quesería gestionada en la plataforma, con papelera de 30 días.
 */
class Planta extends Model
{
    use SoftDeletes;

    public const DIAS_RETENCION_PAPELERA = 30;

    protected $table = 'plantas';

    protected $fillable = [
        'codigo',
        'nombre',
        'ubicacion',
        'precio_litro',
        'activa',
        'recetas_base',
        'eliminado_por',
    ];

    protected $casts = [
        'precio_litro' => 'float',
        'activa' => 'boolean',
        'recetas_base' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function eliminador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'eliminado_por');
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

    public function reportesExcel(): HasMany
    {
        return $this->hasMany(ReporteExcel::class);
    }

    public function qrAccesos(): HasMany
    {
        return $this->hasMany(QrAcceso::class);
    }

    public function notificacionesPanel(): HasMany
    {
        return $this->hasMany(NotificacionPanel::class);
    }

    /**
     * Retorna las recetas base de la planta combinadas con los valores por defecto.
     *
     * @return array<string, array{cuajo_x_100l: float, sal_x_100l: float, cultivo_x_100l: float, litros_x_molde: float, temp_inicial: float, duracion_minutos: int}>
     */
    public function obtenerRecetasBase(): array
    {
        $defaults = TinaWorkflowService::recetasDefault();
        $personalizadas = is_array($this->recetas_base) ? $this->recetas_base : [];
        $resultado = [];

        foreach ($defaults as $clave => $base) {
            $override = isset($personalizadas[$clave]) && is_array($personalizadas[$clave])
                ? $personalizadas[$clave]
                : [];
            $merged = array_merge($base, array_intersect_key($override, $base));

            $resultado[$clave] = [
                'cuajo_x_100l' => (float) $merged['cuajo_x_100l'],
                'sal_x_100l' => (float) $merged['sal_x_100l'],
                'cultivo_x_100l' => (float) $merged['cultivo_x_100l'],
                'litros_x_molde' => max(0.5, (float) $merged['litros_x_molde']),
                'temp_inicial' => (float) $merged['temp_inicial'],
                'duracion_minutos' => max(1, (int) $merged['duracion_minutos']),
            ];
        }

        return $resultado;
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activa', true);
    }

    public function expiraPapeleraEn(): ?Carbon
    {
        if (! $this->deleted_at) {
            return null;
        }

        return $this->deleted_at->copy()->addDays(self::DIAS_RETENCION_PAPELERA);
    }

    public function diasRestantesPapelera(): int
    {
        $expira = $this->expiraPapeleraEn();
        if (! $expira) {
            return self::DIAS_RETENCION_PAPELERA;
        }

        $restantes = Carbon::now()->diffInDays($expira, false);

        return max(0, (int) ceil($restantes));
    }

    /**
     * Elimina definitivamente las plantas que superaron la retención de 30 días en papelera.
     */
    public static function purgarExpiradas(): int
    {
        $limite = Carbon::now()->subDays(self::DIAS_RETENCION_PAPELERA);
        $expiradas = self::onlyTrashed()->where('deleted_at', '<=', $limite)->get();
        $purgadas = 0;

        foreach ($expiradas as $planta) {
            $planta->forceDelete();
            $purgadas++;
        }

        return $purgadas;
    }
}
