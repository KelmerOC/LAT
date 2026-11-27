<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Informe periódico o manual de actividad operativa y financiera por planta.
 */
class InformeActividadPlanta extends Model
{
    protected $table = 'informes_actividad_planta';

    protected $fillable = [
        'planta_id',
        'generado_por',
        'origen',
        'periodo_inicio',
        'periodo_fin',
        'resumen_titulo',
        'contenido_texto',
        'total_acciones',
        'metricas',
    ];

    protected $casts = [
        'periodo_inicio' => 'datetime',
        'periodo_fin' => 'datetime',
        'total_acciones' => 'integer',
        'metricas' => 'array',
    ];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por');
    }

    /**
     * Genera un nombre de archivo .txt limpio y trazable para descargar el informe.
     */
    public function nombreArchivoTxt(): string
    {
        $codigo = $this->relationLoaded('planta')
            ? ($this->planta?->codigo ?? 'PLT')
            : ($this->planta()->value('codigo') ?? 'PLT');

        $codigoLimpio = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $codigo) ?: 'PLT';
        $fecha = ($this->periodo_fin ?? $this->created_at ?? Carbon::now())->format('Ymd-His');

        return sprintf('informe-%s-%s-%d.txt', strtolower($codigoLimpio), $fecha, (int) $this->id);
    }
}
