<?php

namespace App\Services;

use App\Models\Acopio;
use App\Models\Lote;
use App\Models\NotificacionPanel;
use App\Models\Planta;
use App\Models\Tina;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Orquesta carga de entregas en tinas, recetas base, cronómetros y notificaciones internas.
 */
class TinaWorkflowService
{
    public function __construct(protected LacteoService $lacteo) {}

    /**
     * Retorna las recetas base predeterminadas por cada 100 litros de leche.
     *
     * @return array<string, array{cuajo_x_100l: float, sal_x_100l: float, cultivo_x_100l: float, litros_x_molde: float, temp_inicial: float, duracion_minutos: int}>
     */
    public static function recetasDefault(): array
    {
        return [
            'principal_queso' => [
                'cuajo_x_100l' => 2.5,
                'sal_x_100l' => 1.8,
                'cultivo_x_100l' => 1.0,
                'litros_x_molde' => 9.0,
                'temp_inicial' => 72.0,
                'duracion_minutos' => 45,
            ],
            'quesillo' => [
                'cuajo_x_100l' => 2.2,
                'sal_x_100l' => 1.5,
                'cultivo_x_100l' => 0.8,
                'litros_x_molde' => 8.5,
                'temp_inicial' => 68.0,
                'duracion_minutos' => 30,
            ],
            'yogur' => [
                'cuajo_x_100l' => 0.0,
                'sal_x_100l' => 0.0,
                'cultivo_x_100l' => 2.5,
                'litros_x_molde' => 1.0,
                'temp_inicial' => 43.0,
                'duracion_minutos' => 240,
            ],
            'multiproposito' => [
                'cuajo_x_100l' => 2.5,
                'sal_x_100l' => 1.8,
                'cultivo_x_100l' => 1.0,
                'litros_x_molde' => 9.0,
                'temp_inicial' => 72.0,
                'duracion_minutos' => 45,
            ],
        ];
    }

    /**
     * Calcula los insumos dosificados y moldes estimados según una receta base (x 100 L).
     *
     * @param  array{cuajo_x_100l?: float|int, sal_x_100l?: float|int, cultivo_x_100l?: float|int, litros_x_molde?: float|int}  $receta
     * @return array{litros: float, cuajo_ml: float, sal_kg: float, cultivo_g: float, moldes_estimados: int}
     */
    public function dosificarConRecetaBase(float $litros, array $receta): array
    {
        $litrosSeguros = max(0.0, $litros);
        $factor = $litrosSeguros / 100.0;
        $litrosPorMolde = max(0.5, (float) ($receta['litros_x_molde'] ?? 9.0));

        return [
            'litros' => round($litrosSeguros, 1),
            'cuajo_ml' => round($factor * (float) ($receta['cuajo_x_100l'] ?? 2.5), 2),
            'sal_kg' => round($factor * (float) ($receta['sal_x_100l'] ?? 1.8), 2),
            'cultivo_g' => round($factor * (float) ($receta['cultivo_x_100l'] ?? 1.0), 2),
            'moldes_estimados' => (int) floor($litrosSeguros / $litrosPorMolde),
        ];
    }

    /**
     * Inicia un lote en tina bloqueando atómicamente los acopios evaluados seleccionados.
     *
     * @param  array{
     *     acopio_ids?: array<int, int|string>,
     *     receta_base?: string|null,
     *     receta_nombre?: string|null,
     *     cuajo_ml?: float|int|string|null,
     *     sal_kg?: float|int|string|null,
     *     cultivo_g?: float|int|string|null,
     *     temperatura_actual?: float|int|string|null,
     *     duracion_minutos?: int|string|null
     * }  $datos
     */
    public function iniciarLoteConAcopios(Planta $planta, Tina $tina, array $datos, int $userId): Lote
    {
        $ids = array_values(array_unique(array_map('intval', $datos['acopio_ids'] ?? [])));

        return DB::transaction(function () use ($planta, $tina, $datos, $ids): Lote {
            $tinaBloqueada = Tina::dePlanta($planta->id)->where('id', $tina->id)->lockForUpdate()->firstOrFail();

            if (! $tinaBloqueada->activa) {
                throw ValidationException::withMessages(['tina' => __('lat.vat_inactive_error')]);
            }

            if ($tinaBloqueada->loteActivo()->exists()) {
                throw ValidationException::withMessages(['tina' => __('lat.vat_already_active_error')]);
            }

            $acopios = Acopio::dePlanta($planta->id)
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            if ($ids === [] || $acopios->count() !== count($ids)) {
                throw ValidationException::withMessages(['acopio_ids' => __('lat.vat_milk_invalid_error')]);
            }

            foreach ($acopios as $acopio) {
                if ($acopio->lote_id !== null || ! in_array($acopio->calidad, ['buena', 'acida'], true)) {
                    throw ValidationException::withMessages(['acopio_ids' => __('lat.vat_milk_invalid_error')]);
                }
            }

            $litros = round((float) $acopios->sum('litros'), 2);
            if ($litros <= 0.0) {
                throw ValidationException::withMessages(['acopio_ids' => __('lat.vat_milk_invalid_error')]);
            }

            if ($litros > (float) $tinaBloqueada->capacidad_litros) {
                throw ValidationException::withMessages([
                    'acopio_ids' => __('lat.vat_capacity_exceeded_error', [
                        'liters' => number_format($litros, 1),
                        'capacity' => number_format((float) $tinaBloqueada->capacidad_litros, 0),
                    ]),
                ]);
            }

            $recetasBase = $planta->obtenerRecetasBase();
            $claveReceta = (string) ($datos['receta_base'] ?? $tinaBloqueada->tipo);
            if (! isset($recetasBase[$claveReceta])) {
                $claveReceta = isset($recetasBase[$tinaBloqueada->tipo]) ? $tinaBloqueada->tipo : 'principal_queso';
            }

            $recetaBase = $recetasBase[$claveReceta];
            $dosificacion = $this->dosificarConRecetaBase($litros, $recetaBase);

            $cuajoMl = isset($datos['cuajo_ml']) && $datos['cuajo_ml'] !== ''
                ? round((float) $datos['cuajo_ml'], 2)
                : $dosificacion['cuajo_ml'];
            $salKg = isset($datos['sal_kg']) && $datos['sal_kg'] !== ''
                ? round((float) $datos['sal_kg'], 2)
                : $dosificacion['sal_kg'];
            $cultivoG = isset($datos['cultivo_g']) && $datos['cultivo_g'] !== ''
                ? round((float) $datos['cultivo_g'], 2)
                : $dosificacion['cultivo_g'];
            $temp = isset($datos['temperatura_actual']) && $datos['temperatura_actual'] !== ''
                ? (float) $datos['temperatura_actual']
                : (float) $recetaBase['temp_inicial'];
            $duracion = isset($datos['duracion_minutos']) && $datos['duracion_minutos'] !== ''
                ? max(1, (int) $datos['duracion_minutos'])
                : (int) $recetaBase['duracion_minutos'];

            $recetaNombre = trim((string) ($datos['receta_nombre'] ?? ''));
            if ($recetaNombre === '') {
                $recetaNombre = $claveReceta;
            }

            $evaluacion = $this->lacteo->evaluarTermica($temp);
            $codigoLote = sprintf(
                'LOTE-P%d-T%d-%s-%s',
                $planta->id,
                $tinaBloqueada->id,
                Carbon::now()->format('YmdHis'),
                Str::upper(Str::random(3))
            );

            $lote = Lote::create([
                'planta_id' => $planta->id,
                'tina_id' => $tinaBloqueada->id,
                'codigo' => substr($codigoLote, 0, 40),
                'fecha' => Carbon::today()->toDateString(),
                'litros' => $litros,
                'temperatura_actual' => $temp,
                'cuajo_ml' => $cuajoMl,
                'sal_kg' => $salKg,
                'cultivo_g' => $cultivoG,
                'moldes_producidos' => 0,
                'estado' => $evaluacion['estado'] === 'muy_fria' ? 'enfriando' : $evaluacion['estado'],
                'receta_nombre' => substr($recetaNombre, 0, 80),
                'duracion_minutos' => $duracion,
                'iniciado_en' => Carbon::now(),
                'alerta_fin_enviada' => false,
            ]);

            Acopio::whereIn('id', $acopios->pluck('id')->all())
                ->update(['lote_id' => $lote->id]);

            return $lote;
        });
    }

    /**
     * Registra una notificación persistente en el panel de la planta.
     *
     * @param  array<string, mixed>  $datos
     * @param  list<string>|null  $rolesDestino
     */
    public function notificar(
        int $plantaId,
        string $tipo,
        string $modulo,
        string $titulo,
        string $mensaje,
        array $datos = [],
        ?int $creadoPor = null,
        ?int $destinatarioId = null,
        ?array $rolesDestino = null,
        string $colorTema = 'default',
        bool $alcanceGlobal = false
    ): NotificacionPanel {
        return NotificacionPanel::create([
            'planta_id' => $plantaId,
            'tipo' => substr($tipo, 0, 40),
            'modulo_destino' => substr($modulo, 0, 30),
            'titulo' => substr($titulo, 0, 140),
            'mensaje' => substr($mensaje, 0, 320),
            'datos' => $datos !== [] ? $datos : null,
            'creado_por' => $creadoPor,
            'destinatario_id' => $destinatarioId,
            'roles_destino' => $rolesDestino !== null && $rolesDestino !== [] ? array_values($rolesDestino) : null,
            'color_tema' => substr($colorTema !== '' ? $colorTema : 'default', 0, 20),
            'alcance_global' => $alcanceGlobal,
            'leida_por' => [],
        ]);
    }

    /**
     * Verifica lotes activos con temporizador cumplido y emite alerta una única vez.
     *
     * @param  Collection<int, Lote>|null  $lotesActivos
     */
    public function verificarCronometrosVencidos(int $plantaId, ?Collection $lotesActivos = null): void
    {
        $lotes = $lotesActivos ?? Lote::with('tina:id,codigo,nombre')
            ->dePlanta($plantaId)
            ->enProceso()
            ->where('alerta_fin_enviada', false)
            ->get();

        if ($lotes->isEmpty()) {
            return;
        }

        $ahora = Carbon::now();

        foreach ($lotes as $lote) {
            if ($lote->alerta_fin_enviada) {
                continue;
            }

            $fin = $lote->finEstimadoEn();
            if (! $fin instanceof Carbon || $fin->gt($ahora)) {
                continue;
            }

            $actualizado = Lote::where('id', $lote->id)
                ->where('alerta_fin_enviada', false)
                ->update(['alerta_fin_enviada' => true]);

            if ($actualizado === 0) {
                continue;
            }

            $lote->alerta_fin_enviada = true;
            $tina = $lote->relationLoaded('tina')
                ? $lote->tina
                : $lote->tina()->first(['id', 'codigo', 'nombre']);
            $tinaCodigo = $tina?->codigo ?? 'TINA';
            $tinaNombre = $tina?->nombre ?? $tinaCodigo;

            $tituloClave = 'lat.notif_timer_done_title';
            $msgClave = 'lat.notif_timer_done_msg';
            $titulo = __($tituloClave, ['vat' => $tinaCodigo]);
            $mensaje = __($msgClave, [
                'vat' => $tinaNombre,
                'batch' => $lote->codigo,
                'minutes' => (int) ($lote->duracion_minutos ?? 45),
            ]);

            if ($titulo === $tituloClave) {
                $titulo = "Tiempo cumplido en {$tinaCodigo}";
            }

            if ($mensaje === $msgClave) {
                $mensaje = "El lote {$lote->codigo} en {$tinaNombre} cumplió {$lote->duracion_minutos} min.";
            }

            $this->notificar(
                $plantaId,
                'tina_tiempo_cumplido',
                'tinas',
                $titulo,
                $mensaje,
                [
                    'lote_id' => $lote->id,
                    'tina_id' => $lote->tina_id,
                    'tina_codigo' => $tinaCodigo,
                ],
                rolesDestino: ['encargado_planta', 'control_tina']
            );
        }
    }

    /**
     * Obtiene notificaciones recientes visibles para el usuario en una sola consulta SQL.
     *
     * @return array{
     *     items: list<array{
     *         id: int,
     *         tipo: string,
     *         modulo_destino: string,
     *         color_tema: string,
     *         titulo: string,
     *         mensaje: string,
     *         datos: array<string, mixed>,
     *         leida: bool,
     *         creada_en: string,
     *         iso: string
     *     }>,
     *     no_leidas: int,
     *     ultimo_id: int
     * }
     */
    public function obtenerNotificacionesParaUsuario(int $plantaId, int|User $usuarioOrId, int $afterId = 0, int $limit = 15): array
    {
        if ($usuarioOrId instanceof User) {
            $userId = (int) $usuarioOrId->id;
            $query = NotificacionPanel::query();

            if ($usuarioOrId->esAdmin()) {
                $query->where(function ($q) use ($plantaId, $userId): void {
                    $q->where('planta_id', $plantaId)
                        ->orWhere('alcance_global', true)
                        ->orWhere('destinatario_id', $userId);
                });
            } else {
                $query->where('planta_id', $plantaId)
                    ->where('alcance_global', false);
            }

            $visibles = $query->latest('id')
                ->limit($limit * 3)
                ->get()
                ->filter(fn (NotificacionPanel $notif): bool => $notif->esVisiblePara($usuarioOrId, $plantaId))
                ->take($limit)
                ->values();
        } else {
            $userId = $usuarioOrId;
            $visibles = NotificacionPanel::dePlanta($plantaId)
                ->latest('id')
                ->limit($limit)
                ->get();
        }

        $mapeadas = $visibles->map(function (NotificacionPanel $notif) use ($userId): array {
            return [
                'id' => $notif->id,
                'tipo' => $notif->tipo,
                'modulo_destino' => $notif->modulo_destino,
                'color_tema' => $notif->color_tema ?? 'default',
                'titulo' => $notif->titulo,
                'mensaje' => $notif->mensaje,
                'datos' => is_array($notif->datos) ? $notif->datos : [],
                'leida' => $notif->estaLeidaPor($userId),
                'creada_en' => $notif->created_at?->format('d/m H:i') ?? '',
                'iso' => $notif->created_at?->toIso8601String() ?? '',
            ];
        });

        $items = $afterId > 0
            ? $mapeadas->filter(fn (array $item): bool => $item['id'] > $afterId)->values()->all()
            : $mapeadas->values()->all();

        return [
            'items' => $items,
            'no_leidas' => $mapeadas->where('leida', false)->count(),
            'ultimo_id' => max($afterId, (int) ($visibles->max('id') ?? 0)),
        ];
    }
}
