<?php

namespace App\Http\Controllers;

use App\Models\Acopio;
use App\Models\Liquidacion;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Services\LacteoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * ============================================================================
 * CONTROLADOR: TABLERO UNIFICADO DE OPERACIONES LÁCTEAS (PANEL LAT)
 * ============================================================================
 * Consolida los 4 módulos operativos en una arquitectura coherente y didáctica:
 * 1. Métricas del día y Acopio (Voz / Manual con prevención N+1).
 * 2. Control de Calidad Sanitaria (Triage rápido y desvío a quesillo).
 * 3. Monitor Térmico de Tina y Receta Automatizada.
 * 4. Liquidaciones Quincenales, Adelantos, Ayuda del Día y Exportación CSV.
 */
class PanelController extends Controller
{
    protected LacteoService $service;

    public function __construct(LacteoService $service)
    {
        $this->service = $service;
    }

    /**
     * Carga el estado integral de la planta lechera.
     * [OPTIMIZACIONES]: Eager loading de relaciones y cálculo sin N+1.
     */
    public function index(Request $request)
    {
        $usuario = $request->user();
        $hoy = Carbon::today()->toDateString();

        // 1. Proveedores para selectores de acopio y adelantos
        $proveedores = Proveedor::activos()
            ->select('id', 'codigo', 'nombre', 'sector')
            ->ordenadosPorNombre()
            ->get();

        // 2. Acopios de hoy con Eager Loading para evitar el problema N+1
        $acopiosHoy = Acopio::with('proveedor:id,codigo,nombre,sector')
            ->delDia($hoy)
            ->latest('id')
            ->get();

        // Estadísticas del día
        $litrosHoy = (float) $acopiosHoy->sum('litros');
        $precioLitro = 1.65;
        $totalPendientesCalidad = $acopiosHoy->where('calidad', 'pendiente')->count();
        $litrosAptosTina = (float) $acopiosHoy->where('calidad', 'buena')->sum('litros');

        // 3. Lote activo en tina de enfriamiento
        $loteActivo = Lote::enProceso()->latest('id')->first();
        if (!$loteActivo) {
            $loteActivo = Lote::firstOrCreate(
                ['fecha' => $hoy, 'estado' => 'enfriando'],
                [
                    'codigo' => 'LOTE-'.Carbon::now()->format('Ymd-His'),
                    'litros' => max(100.0, $litrosAptosTina > 0 ? $litrosAptosTina : 200.0),
                    'temperatura_actual' => 52.0,
                    'cuajo_ml' => 5.0,
                    'sal_kg' => 3.6,
                    'cultivo_g' => 2.0,
                    'moldes_producidos' => 0,
                ]
            );
        }

        // Monitoreo térmico y receta estandarizada
        $termica = $this->service->evaluarTermica($loteActivo->temperatura_actual);
        $receta = $this->service->dosificarReceta($loteActivo->litros);

        // 4. Período de quincena y liquidaciones masivas
        $diaMes = Carbon::today()->day;
        $desde = Carbon::today()->startOfMonth()->toDateString();
        $hasta = ($diaMes <= 15)
            ? Carbon::today()->startOfMonth()->addDays(14)->toDateString()
            : Carbon::today()->endOfMonth()->toDateString();

        $liquidaciones = $this->service->liquidarQuincena($desde, $hasta, $precioLitro);

        // Movimientos recientes (Gastos, Ayuda del Día sin DNI, Adelantos)
        $movimientos = Liquidacion::with('proveedor:id,nombre')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('panel', compact(
            'usuario',
            'proveedores',
            'acopiosHoy',
            'litrosHoy',
            'precioLitro',
            'totalPendientesCalidad',
            'loteActivo',
            'termica',
            'receta',
            'liquidaciones',
            'movimientos',
            'desde',
            'hasta'
        ));
    }

    /**
     * MÓDULO 1: Guarda un acopio de leche (entrada por voz o botonera táctil).
     */
    public function guardarAcopio(Request $request)
    {
        $datos = $request->validate([
            'proveedor_id'  => ['required', 'exists:proveedores,id'],
            'litros'        => ['required', 'numeric', 'min:0.5', 'max:5000'],
            'metodo'        => ['nullable', 'in:voz,manual'],
            'frase_dictada' => ['nullable', 'string', 'max:255'],
        ]);

        Acopio::create([
            'proveedor_id'   => $datos['proveedor_id'],
            'registrado_por' => $request->user()->id,
            'uuid_offline'   => (string) Str::uuid(),
            'fecha'          => Carbon::today()->toDateString(),
            'hora'           => Carbon::now()->toTimeString(),
            'litros'         => $datos['litros'],
            'precio_litro'   => 1.65,
            'metodo'         => $datos['metodo'] ?? 'manual',
            'frase_dictada'  => $datos['frase_dictada'] ?? null,
            'calidad'        => 'buena', // Por defecto buena, triage ágil
            'destino'        => 'queso_yogur',
        ]);

        return back()->with('exito', 'Acopio registrado con éxito.');
    }

    /**
     * MÓDULO 2: Clasificación Sanitaria de Leche (Triage rápido y desvío).
     */
    public function evaluarCalidad(Request $request, int $id)
    {
        $acopio = Acopio::findOrFail($id);
        $calidad = $request->input('calidad'); // 'buena' o 'acida'

        if ($calidad === 'acida') {
            $acopio->update([
                'calidad' => 'acida',
                'destino' => 'quesillo_4_dias', // Cero desperdicio ético
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
     * MÓDULO 3: Simula / ajusta la temperatura de la tina de pasteurización.
     */
    public function actualizarTina(Request $request, int $id)
    {
        $lote = Lote::findOrFail($id);

        if ($request->has('temperatura')) {
            $nuevaTemp = (float) $request->input('temperatura');
            $evaluacion = $this->service->evaluarTermica($nuevaTemp);

            $lote->update([
                'temperatura_actual' => $nuevaTemp,
                'estado'             => $evaluacion['estado'],
            ]);
        }

        if ($request->has('moldes')) {
            $lote->update([
                'moldes_producidos' => (int) $request->input('moldes'),
                'estado'            => 'terminado',
            ]);
        }

        return back()->with('exito', 'Tina actualizada correctamente.');
    }

    /**
     * MÓDULO 4: Registro de Adelantos y Pago de Ayuda del Día (Sin DNI - Ley 29733).
     */
    public function registrarMovimiento(Request $request)
    {
        $datos = $request->validate([
            'tipo'         => ['required', 'in:adelanto_efectivo,adelanto_queso,ayuda_del_dia,gasto_operativo'],
            'proveedor_id' => ['nullable', 'exists:proveedores,id'],
            'concepto'     => ['required', 'string', 'max:160'],
            'monto'        => ['required', 'numeric', 'min:0.5'],
        ]);

        Liquidacion::create([
            'proveedor_id' => $datos['proveedor_id'] ?? null,
            'fecha'        => Carbon::today()->toDateString(),
            'tipo'         => $datos['tipo'],
            'concepto'     => $datos['concepto'],
            'litros'       => 0,
            'monto'        => $datos['monto'],
            'comision'     => 0.00,
            'descontado'   => false,
        ]);

        return back()->with('exito', 'Movimiento registrado correctamente.');
    }

    /**
     * MÓDULO 5: Exportación formal del balance quincenal a CSV.
     */
    public function exportarCsv()
    {
        $diaMes = Carbon::today()->day;
        $desde = Carbon::today()->startOfMonth()->toDateString();
        $hasta = ($diaMes <= 15)
            ? Carbon::today()->startOfMonth()->addDays(14)->toDateString()
            : Carbon::today()->endOfMonth()->toDateString();

        $liquidaciones = $this->service->liquidarQuincena($desde, $hasta, 1.65);
        $csv = $this->service->exportarCsv($liquidaciones);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="balance_quincenal_lat_'.Carbon::now()->format('Ymd').'.csv"',
        ]);
    }
}
