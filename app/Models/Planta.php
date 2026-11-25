<?php

namespace App\Models;

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
        'eliminado_por',
    ];

    protected $casts = [
        'precio_litro' => 'float',
        'activa' => 'boolean',
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
