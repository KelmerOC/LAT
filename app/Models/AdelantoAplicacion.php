<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro puente que vincula cuánto de un adelanto se aplicó a una liquidación quincenal.
 */
class AdelantoAplicacion extends Model
{
    protected $table = 'adelanto_aplicaciones';

    protected $fillable = [
        'planta_id',
        'quincena_liquidacion_id',
        'adelanto_liquidacion_id',
        'monto_aplicado',
    ];

    protected $casts = [
        'monto_aplicado' => 'float',
    ];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    public function quincena(): BelongsTo
    {
        return $this->belongsTo(Liquidacion::class, 'quincena_liquidacion_id');
    }

    public function adelanto(): BelongsTo
    {
        return $this->belongsTo(Liquidacion::class, 'adelanto_liquidacion_id');
    }
}
