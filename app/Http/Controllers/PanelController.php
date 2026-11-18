<?php

namespace App\Http\Controllers;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\Planta;
use App\Models\Proveedor;
use App\Models\Tina;
use App\Models\User;
use App\Services\ExcelExportService;
use App\Services\LacteoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * ============================================================================
 * CONTROLADOR: TABLERO UNIFICADO DE OPERACIONES LÁCTEAS (PANEL LAT)
 * ============================================================================
 * Consolida la operación por planta:
 * 1. Acopio (Voz / Manual) + Edición de datos en caso de error.
 * 2. Control de Calidad Sanitaria (con confirmación JS antes de cambiar).
 * 3. Gestión de Múltiples Tinas (agregar, editar, controlar curva térmica).
 * 4. Liquidaciones Quincenales, Ventas, Movimientos y Exportación Excel (.xlsx).
 * 5. Estadísticas Comparativas de Plantas ("¿Quién vende más?") y Admin.
 */
class PanelController extends Controller
{
    protected LacteoService $service;
    protected ExcelExportService $excel;

    public function __construct(LacteoService $service, ExcelExportService $excel)
    {
        $this->service = $service;
        $this->excel = $excel;
    }

    /**
     * Resuelve la planta activa para el usuario en sesión.
     */
    private function resolverPlantaActiva(Request $request): Planta
    {
        $usuario = $request->user();

        if ($usuario->esAdmin()) {
            $sessionId = $request->session()->get('planta_activa_id');
            if ($sessionId) {
                $planta = Planta::find($sessionId);
                if ($planta) {
                    return $planta;
                }
            }
        }

        if ($usuario->planta_id) {
            $planta = Planta::find($usuario->planta_id);
            if ($planta) {
                return $planta;
            }
        }

        return Planta::firstOrCreate(
            ['codigo' => 'PLT-01'],
            [
                'nombre'       => 'Planta Láctea San Borreal',
                'ubicacion'    => 'Anta · Cusco Provincia',
                'precio_litro' => 1.65,
                'activa'       => true,
            ]
        );
    }

    private function periodoActual(): array
    {
        $diaMes = Carbon::today()->day;
        $desde = Carbon::today()->startOfMonth()->toDateString();
        $hasta = ($diaMes <= 15)
            ? Carbon::today()->startOfMonth()->addDays(14)->toDateString()
            : Carbon::today()->endOfMonth()->toDateString();

        return [$desde, $hasta];
    }

    /**
     * Carga el estado integral de la planta lechera activa.
     */
    public function index(Request $request)
    {
        $usuario = $request->user();
        $plantaActiva = $this->resolverPlantaActiva($request);
        $plantaId = $plantaActiva->id;
        $hoy = Carbon::today()->toDateString();

        // Plantas y usuarios para selector / administración
        $plantas = $usuario->esAdmin()
            ? Planta::withCount(['tinas', 'users', 'proveedores'])->orderBy('codigo')->get()
            : collect([$plantaActiva]);

        $usuarios = $usuario->esAdmin()
            ? User::with('planta:id,codigo,nombre')->orderBy('name')->get()
            : collect();

        // 1. Proveedores de la planta activa
        $proveedores = Proveedor::dePlanta($plantaId)
            ->activos()
            ->select('id', 'planta_id', 'codigo', 'nombre', 'sector')
            ->ordenadosPorNombre()
            ->get();

        // 2. Acopios de hoy de la planta activa
        $acopiosHoy = Acopio::with('proveedor:id,codigo,nombre,sector')
            ->dePlanta($plantaId)
            ->delDia($hoy)
            ->latest('id')
            ->get();

        $litrosHoy = (float) $acopiosHoy->sum('litros');
        $precioLitro = (float) $plantaActiva->precio_litro;
        $totalPendientesCalidad = $acopiosHoy->where('calidad', 'pendiente')->count();
        $litrosAptosTina = (float) $acopiosHoy->where('calidad', 'buena')->sum('litros');

        // 3. Múltiples Tinas de la planta activa
        $tinas = Tina::dePlanta($plantaId)
            ->with('loteActivo')
            ->orderBy('codigo')
            ->get();

        // Si la planta no tiene ninguna tina registrada aún, crea la principal
        if ($tinas->isEmpty()) {
            $tinaDefault = Tina::create([
                'planta_id'        => $plantaId,
                'codigo'           => 'TINA-01',
                'nombre'           => 'Tina Principal',
                'capacidad_litros' => 500,
                'tipo'             => 'principal_queso',
                'activa'           => true,
            ]);
            $tinas = collect([$tinaDefault->load('loteActivo')]);
        }

        // Mapear evaluación térmica y receta para cada tina con lote activo
        $tinasPanel = $tinas->map(function (Tina $tina) {
            $lote = $tina->loteActivo;
            $termica = $lote ? $this->service->evaluarTermica($lote->temperatura_actual) : null;
            $receta = $lote ? $this->service->dosificarReceta($lote->litros) : null;

            return (object) [
                'tina'    => $tina,
                'lote'    => $lote,
                'termica' => $termica,
                'receta'  => $receta,
            ];
        });

        // Lote principal para el KPI resumen
        $loteActivo = $tinasPanel->first(fn ($t) => $t->lote !== null)?->lote
            ?? Lote::dePlanta($plantaId)->enProceso()->latest('id')->first();

        if (!$loteActivo) {
            $primeraTina = $tinas->first();
            $litrosBase = max(100.0, $litrosAptosTina > 0 ? $litrosAptosTina : 150.0);
            $recetaBase = $this->service->dosificarReceta($litrosBase);
            $loteActivo = Lote::create([
                'planta_id'          => $plantaId,
                'tina_id'            => $primeraTina->id,
                'codigo'             => 'LOTE-'.Carbon::now()->format('Ymd-His'),
                'fecha'              => $hoy,
                'litros'             => $litrosBase,
                'temperatura_actual' => 52.0,
                'cuajo_ml'           => $recetaBase['cuajo_ml'],
                'sal_kg'             => $recetaBase['sal_kg'],
                'cultivo_g'          => $recetaBase['cultivo_g'],
                'moldes_producidos'  => 0,
                'estado'             => 'enfriando',
            ]);
            // Refrescar colección de tinas
            $tinasPanel = $tinas->map(function (Tina $tina) {
                $tina->load('loteActivo');
                $lote = $tina->loteActivo;

                return (object) [
                    'tina'    => $tina,
                    'lote'    => $lote,
                    'termica' => $lote ? $this->service->evaluarTermica($lote->temperatura_actual) : null,
                    'receta'  => $lote ? $this->service->dosificarReceta($lote->litros) : null,
                ];
            });
        }

        $termica = $this->service->evaluarTermica($loteActivo->temperatura_actual);
        $receta = $this->service->dosificarReceta($loteActivo->litros);

        // 4. Período quincenal, liquidaciones y movimientos
        [$desde, $hasta] = $this->periodoActual();
        $liquidaciones = $this->service->liquidarQuincena($desde, $hasta, $precioLitro, $plantaId);

        $movimientos = Liquidacion::with('proveedor:id,nombre')
            ->dePlanta($plantaId)
            ->latest('id')
            ->limit(25)
            ->get();

        // 5. Estadísticas comparativas de plantas y de la planta activa
        $estadisticas = $this->service->obtenerEstadisticas($plantaId, $desde, $hasta);

        return view('panel', compact(
            'usuario',
            'plantaActiva',
            'plantas',
            'usuarios',
            'proveedores',
            'acopiosHoy',
            'litrosHoy',
            'precioLitro',
            'totalPendientesCalidad',
            'loteActivo',
            'tinasPanel',
            'termica',
            'receta',
            'liquidaciones',
            'movimientos',
            'estadisticas',
            'desde',
            'hasta'
        ));
    }

    /**
     * Cambia la planta activa en la sesión del administrador.
     */
    public function cambiarPlanta(Request $request)
    {
        if (!$request->user()->esAdmin()) {
            abort(403);
        }

        $datos = $request->validate([
            'planta_id' => ['required', 'exists:plantas,id'],
        ]);

        $request->session()->put('planta_activa_id', (int) $datos['planta_id']);

        return back()->with('exito', 'Planta activa cambiada correctamente.');
    }

    /**
     * MÓDULO 1: Guarda un acopio de leche (voz o manual).
     */
    public function guardarAcopio(Request $request)
    {
        $planta = $this->resolverPlantaActiva($request);

        $datos = $request->validate([
            'proveedor_id'  => ['required', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'litros'        => ['required', 'numeric', 'min:0.5', 'max:5000'],
            'metodo'        => ['nullable', 'in:voz,manual'],
            'frase_dictada' => ['nullable', 'string', 'max:255'],
        ]);

        Acopio::create([
            'planta_id'      => $planta->id,
            'proveedor_id'   => $datos['proveedor_id'],
            'registrado_por' => $request->user()->id,
            'uuid_offline'   => (string) Str::uuid(),
            'fecha'          => Carbon::today()->toDateString(),
            'hora'           => Carbon::now()->toTimeString(),
            'litros'         => $datos['litros'],
            'precio_litro'   => $planta->precio_litro,
            'metodo'         => $datos['metodo'] ?? 'manual',
            'frase_dictada'  => $datos['frase_dictada'] ?? null,
            'calidad'        => 'buena',
            'destino'        => 'queso_yogur',
        ]);

        return back()->with('exito', 'Acopio registrado con éxito.');
    }

    /**
     * EDICIÓN DE DATOS: Corrige un acopio en caso de error de digitación o dictado.
     */
    public function actualizarAcopio(Request $request, int $id)
    {
        $planta = $this->resolverPlantaActiva($request);
        $acopio = Acopio::dePlanta($planta->id)->findOrFail($id);

        $datos = $request->validate([
            'proveedor_id' => ['required', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'litros'       => ['required', 'numeric', 'min:0.5', 'max:5000'],
            'calidad'      => ['required', 'in:pendiente,buena,acida'],
            'metodo'       => ['required', 'in:voz,manual'],
            'hora'         => ['nullable', 'string', 'max:8'],
        ]);

        $destino = $datos['calidad'] === 'acida' ? 'quesillo_4_dias' : ($datos['calidad'] === 'buena' ? 'queso_yogur' : 'pendiente');

        $acopio->update([
            'proveedor_id' => $datos['proveedor_id'],
            'litros'       => $datos['litros'],
            'calidad'      => $datos['calidad'],
            'destino'      => $destino,
            'metodo'       => $datos['metodo'],
            'hora'         => !empty($datos['hora']) ? (strlen($datos['hora']) === 5 ? $datos['hora'].':00' : $datos['hora']) : $acopio->hora,
        ]);

        return back()->with('exito', 'Registro de acopio actualizado correctamente.');
    }

    /**
     * EDICIÓN DE DATOS: Elimina un acopio registrado por error.
     */
    public function eliminarAcopio(Request $request, int $id)
    {
        $planta = $this->resolverPlantaActiva($request);
        $acopio = Acopio::dePlanta($planta->id)->findOrFail($id);
        $acopio->delete();

        return back()->with('exito', 'Registro de acopio eliminado.');
    }

    /**
     * MÓDULO 2: Clasificación Sanitaria de Leche (Triage rápido y desvío).
     */
    public function evaluarCalidad(Request $request, int $id)
    {
        $planta = $this->resolverPlantaActiva($request);
        $acopio = Acopio::dePlanta($planta->id)->findOrFail($id);
        $calidad = $request->input('calidad');

        if ($calidad === 'acida') {
            $acopio->update([
                'calidad' => 'acida',
                'destino' => 'quesillo_4_dias',
            ]);
            $mensaje = 'Leche ácida clasificada y derivada a quesillos frescos de 4 días.';
        } else {
            $acopio->update([
                'calidad' => 'buena',
                'destino' => 'queso_yogur',
            ]);
            $mensaje = 'Leche aprobada para pasteurización en tina principal.';
        }

        return back()->with('exito', $mensaje);
    }

    /**
     * MÓDULO 3: Agrega una nueva tina a la planta activa.
     */
    public function crearTina(Request $request)
    {
        $planta = $this->resolverPlantaActiva($request);

        $datos = $request->validate([
            'codigo'           => ['required', 'string', 'max:30', Rule::unique('tinas', 'codigo')->where('planta_id', $planta->id)],
            'nombre'           => ['required', 'string', 'max:100'],
            'capacidad_litros' => ['required', 'numeric', 'min:20', 'max:10000'],
            'tipo'             => ['required', 'in:principal_queso,quesillo,yogur,multiproposito'],
        ]);

        Tina::create([
            'planta_id'        => $planta->id,
            'codigo'           => strtoupper($datos['codigo']),
            'nombre'           => $datos['nombre'],
            'capacidad_litros' => $datos['capacidad_litros'],
            'tipo'             => $datos['tipo'],
            'activa'           => true,
        ]);

        return back()->with('exito', 'Nueva tina agregada a la planta.');
    }

    /**
     * MÓDULO 3: Edita una tina existente.
     */
    public function editarTina(Request $request, int $id)
    {
        $planta = $this->resolverPlantaActiva($request);
        $tina = Tina::dePlanta($planta->id)->findOrFail($id);

        $datos = $request->validate([
            'codigo'           => ['required', 'string', 'max:30', Rule::unique('tinas', 'codigo')->where('planta_id', $planta->id)->ignore($tina->id)],
            'nombre'           => ['required', 'string', 'max:100'],
            'capacidad_litros' => ['required', 'numeric', 'min:20', 'max:10000'],
            'tipo'             => ['required', 'in:principal_queso,quesillo,yogur,multiproposito'],
            'activa'           => ['nullable', 'boolean'],
        ]);

        $tina->update([
            'codigo'           => strtoupper($datos['codigo']),
            'nombre'           => $datos['nombre'],
            'capacidad_litros' => $datos['capacidad_litros'],
            'tipo'             => $datos['tipo'],
            'activa'           => $request->has('activa') ? $request->boolean('activa') : $tina->activa,
        ]);

        return back()->with('exito', 'Tina actualizada correctamente.');
    }

    /**
     * MÓDULO 3: Inicia un nuevo lote térmico en una tina libre.
     */
    public function iniciarLoteEnTina(Request $request, int $tinaId)
    {
        $planta = $this->resolverPlantaActiva($request);
        $tina = Tina::dePlanta($planta->id)->findOrFail($tinaId);

        $datos = $request->validate([
            'litros'             => ['required', 'numeric', 'min:5', 'max:'.$tina->capacidad_litros],
            'temperatura_actual' => ['nullable', 'numeric', 'min:4', 'max:95'],
        ]);

        if ($tina->loteActivo()->exists()) {
            return back()->withErrors(['tina' => 'Esta tina ya tiene un lote en proceso. Finalízalo antes de iniciar otro.']);
        }

        $temp = (float) ($datos['temperatura_actual'] ?? 72.0);
        $evaluacion = $this->service->evaluarTermica($temp);
        $receta = $this->service->dosificarReceta((float) $datos['litros']);

        Lote::create([
            'planta_id'          => $planta->id,
            'tina_id'            => $tina->id,
            'codigo'             => 'LOTE-'.$tina->codigo.'-'.Carbon::now()->format('Ymd-His'),
            'fecha'              => Carbon::today()->toDateString(),
            'litros'             => $datos['litros'],
            'temperatura_actual' => $temp,
            'cuajo_ml'           => $receta['cuajo_ml'],
            'sal_kg'             => $receta['sal_kg'],
            'cultivo_g'          => $receta['cultivo_g'],
            'moldes_producidos'  => 0,
            'estado'             => $evaluacion['estado'] === 'muy_fria' ? 'enfriando' : $evaluacion['estado'],
        ]);

        return back()->with('exito', 'Lote iniciado en '.$tina->nombre.' con dosificación calculada.');
    }

    /**
     * MÓDULO 3: Ajusta temperatura, litros o finaliza un lote en tina.
     */
    public function actualizarTina(Request $request, int $id)
    {
        $planta = $this->resolverPlantaActiva($request);
        $lote = Lote::dePlanta($planta->id)->findOrFail($id);

        if ($request->filled('litros')) {
            $litros = (float) $request->input('litros');
            $receta = $this->service->dosificarReceta($litros);
            $lote->update([
                'litros'    => $litros,
                'cuajo_ml'  => $receta['cuajo_ml'],
                'sal_kg'    => $receta['sal_kg'],
                'cultivo_g' => $receta['cultivo_g'],
            ]);
        }

        if ($request->has('temperatura')) {
            $nuevaTemp = (float) $request->input('temperatura');
            $evaluacion = $this->service->evaluarTermica($nuevaTemp);

            $lote->update([
                'temperatura_actual' => $nuevaTemp,
                'estado'             => $evaluacion['estado'] === 'muy_fria' ? 'enfriando' : $evaluacion['estado'],
            ]);
        }

        if ($request->has('moldes')) {
            $lote->update([
                'moldes_producidos' => max(0, (int) $request->input('moldes')),
                'estado'            => 'terminado',
            ]);

            return back()->with('exito', 'Lote finalizado y moldes registrados en la tina.');
        }

        return back()->with('exito', 'Tina actualizada correctamente.');
    }

    /**
     * MÓDULO 4: Registro de Adelantos, Ayuda del Día, Gastos o Ventas de Producto.
     */
    public function registrarMovimiento(Request $request)
    {
        $planta = $this->resolverPlantaActiva($request);

        $datos = $request->validate([
            'tipo'              => ['required', 'in:adelanto_efectivo,adelanto_queso,ayuda_del_dia,gasto_operativo,venta_producto'],
            'proveedor_id'      => ['nullable', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'concepto'          => ['required', 'string', 'max:160'],
            'cantidad_unidades' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'monto'             => ['required', 'numeric', 'min:0.5'],
        ]);

        Liquidacion::create([
            'planta_id'         => $planta->id,
            'proveedor_id'      => !empty($datos['proveedor_id']) ? $datos['proveedor_id'] : null,
            'fecha'             => Carbon::today()->toDateString(),
            'tipo'              => $datos['tipo'],
            'concepto'          => $datos['concepto'],
            'litros'            => 0,
            'cantidad_unidades' => $datos['cantidad_unidades'] ?? 0,
            'monto'             => $datos['monto'],
            'comision'          => 0.00,
            'descontado'        => $datos['tipo'] === 'venta_producto' || $datos['tipo'] === 'ayuda_del_dia',
        ]);

        return back()->with('exito', 'Movimiento registrado correctamente.');
    }

    /**
     * EDICIÓN DE DATOS: Corrige un movimiento financiero o venta.
     */
    public function actualizarMovimiento(Request $request, int $id)
    {
        $planta = $this->resolverPlantaActiva($request);
        $mov = Liquidacion::dePlanta($planta->id)->findOrFail($id);

        $datos = $request->validate([
            'tipo'              => ['required', 'in:adelanto_efectivo,adelanto_queso,ayuda_del_dia,gasto_operativo,venta_producto'],
            'proveedor_id'      => ['nullable', Rule::exists('proveedores', 'id')->where('planta_id', $planta->id)],
            'concepto'          => ['required', 'string', 'max:160'],
            'cantidad_unidades' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'monto'             => ['required', 'numeric', 'min:0.5'],
        ]);

        $mov->update([
            'tipo'              => $datos['tipo'],
            'proveedor_id'      => !empty($datos['proveedor_id']) ? $datos['proveedor_id'] : null,
            'concepto'          => $datos['concepto'],
            'cantidad_unidades' => $datos['cantidad_unidades'] ?? 0,
            'monto'             => $datos['monto'],
        ]);

        return back()->with('exito', 'Movimiento actualizado correctamente.');
    }

    /**
     * EDICIÓN DE DATOS: Elimina un movimiento financiero erróneo.
     */
    public function eliminarMovimiento(Request $request, int $id)
    {
        $planta = $this->resolverPlantaActiva($request);
        $mov = Liquidacion::dePlanta($planta->id)->findOrFail($id);
        $mov->delete();

        return back()->with('exito', 'Movimiento eliminado.');
    }

    /**
     * Agrega o corrige un proveedor ganadero de la planta activa.
     */
    public function guardarProveedor(Request $request)
    {
        $planta = $this->resolverPlantaActiva($request);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20', 'unique:proveedores,codigo'],
            'nombre' => ['required', 'string', 'max:120'],
            'sector' => ['nullable', 'string', 'max:120'],
        ]);

        Proveedor::create([
            'planta_id' => $planta->id,
            'codigo'    => strtoupper($datos['codigo']),
            'nombre'    => $datos['nombre'],
            'sector'    => $datos['sector'] ?? $planta->ubicacion,
            'activo'    => true,
        ]);

        return back()->with('exito', 'Ganadero registrado en la planta.');
    }

    public function actualizarProveedor(Request $request, int $id)
    {
        $planta = $this->resolverPlantaActiva($request);
        $prov = Proveedor::dePlanta($planta->id)->findOrFail($id);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('proveedores', 'codigo')->ignore($prov->id)],
            'nombre' => ['required', 'string', 'max:120'],
            'sector' => ['nullable', 'string', 'max:120'],
        ]);

        $prov->update([
            'codigo' => strtoupper($datos['codigo']),
            'nombre' => $datos['nombre'],
            'sector' => $datos['sector'] ?? $prov->sector,
        ]);

        return back()->with('exito', 'Datos del ganadero actualizados.');
    }

    /**
     * MÓDULO 5: Exportación ordenada en formato Excel (.xlsx) real.
     */
    public function exportarExcel(Request $request)
    {
        $planta = $this->resolverPlantaActiva($request);
        [$desde, $hasta] = $this->periodoActual();

        $liquidaciones = $this->service->liquidarQuincena($desde, $hasta, (float) $planta->precio_litro, $planta->id);
        $acopios = Acopio::with('proveedor:id,codigo,nombre,sector')
            ->dePlanta($planta->id)
            ->delPeriodo($desde, $hasta)
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();
        $movimientos = Liquidacion::with('proveedor:id,nombre')
            ->dePlanta($planta->id)
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();
        $estadisticas = $this->service->obtenerEstadisticas($planta->id, $desde, $hasta);

        $xlsx = $this->excel->generarBalanceXlsx(
            $planta,
            $desde,
            $hasta,
            $liquidaciones,
            $acopios,
            $movimientos,
            $estadisticas['ranking_plantas']
        );

        $slug = Str::slug($planta->codigo ?: 'lat');
        $filename = "balance_lat_{$slug}_".Carbon::now()->format('Ymd').'.xlsx';

        return response($xlsx, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
