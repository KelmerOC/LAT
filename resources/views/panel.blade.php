@extends('layouts.app')

@section('titulo', 'LAT · Mi Panel Operativo')

@section('contenido')
  <!-- Encabezado de Sesión y Perfil -->
  <div style="background-color: var(--color-fog); border-radius: 12px; padding: 1.5rem 2rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <span style="font-size: 0.8rem; font-weight: 600; color: var(--color-celeste); text-transform: uppercase; letter-spacing: 0.5px;">Centro de Acopio y Transformación</span>
      <h2 style="font-family: var(--font-display); font-size: 1.8rem; color: var(--color-slate-deep); margin: 0.25rem 0;">
        Planta Láctea San Borreal
      </h2>
      <p style="color: var(--color-graphite); font-size: 0.88rem; margin: 0;">
        Operador: <strong>{{ $usuario->name }}</strong> · Cargo: <span style="background: var(--color-sky-wash); color: var(--color-slate-deep); padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.78rem;">{{ strtoupper(str_replace('_', ' ', $usuario->cargo)) }}</span>
      </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center;">
      <span style="background-color: var(--verde-fill); color: var(--verde-text); padding: 0.45rem 0.85rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600;">
        ● Sistema Operativo en Línea
      </span>
      <a href="{{ route('panel.exportar') }}" class="btn-lat btn-ember" style="padding: 0.5rem 1rem; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
        Exportar Balance CSV
      </a>
    </div>
  </div>

  <!-- TARJETAS SUPERIORES DE MÉTRICAS (Flat Fill Contrast y Cifras Delgadas 300) -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <!-- Métrica 1 -->
    <div style="background-color: var(--color-fog); padding: 1.5rem; border-radius: 12px;">
      <span style="font-size: 0.82rem; color: var(--color-pewter); text-transform: uppercase; letter-spacing: 0.5px; display: block;">Leche Recibida Hoy</span>
      <div style="font-size: 2.3rem; color: var(--color-slate-deep); margin-top: 0.35rem;" class="cifra-delgada">
        {{ number_format($litrosHoy, 1) }} <span style="font-size: 1.1rem; color: var(--color-graphite);">L</span>
      </div>
      <span style="font-size: 0.75rem; color: var(--color-celeste); font-weight: 500;">{{ $acopiosHoy->count() }} entregas registradas</span>
    </div>

    <!-- Métrica 2 -->
    <div style="background-color: var(--color-sky-wash); padding: 1.5rem; border-radius: 12px;">
      <span style="font-size: 0.82rem; color: var(--color-slate-deep); text-transform: uppercase; letter-spacing: 0.5px; display: block;">Precio Vigente</span>
      <div style="font-size: 2.3rem; color: var(--color-slate-deep); margin-top: 0.35rem;" class="cifra-delgada">
        S/ {{ number_format($precioLitro, 2) }}
      </div>
      <span style="font-size: 0.75rem; color: #15803d; font-weight: 600;">Comisión LAT: S/ 0.00</span>
    </div>

    <!-- Métrica 3 -->
    <div style="background-color: var(--color-ocean); color: #ffffff; padding: 1.5rem; border-radius: 12px;">
      <span style="font-size: 0.82rem; color: #bae6fd; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Tina Pasteurizadora</span>
      <div style="font-size: 2.3rem; color: #ffffff; margin-top: 0.35rem;" class="cifra-delgada">
        {{ number_format($loteActivo->temperatura_actual, 1) }} <span style="font-size: 1.1rem; color: #7dd3fc;">°C</span>
      </div>
      <span style="font-size: 0.75rem; color: #fef08a; font-weight: 500;">
        Estado: {{ strtoupper($termica['estado']) }} ({{ $termica['porcentaje'] }}%)
      </span>
    </div>

    <!-- Métrica 4 -->
    <div style="background-color: var(--color-fog); padding: 1.5rem; border-radius: 12px;">
      <span style="font-size: 0.82rem; color: var(--color-pewter); text-transform: uppercase; letter-spacing: 0.5px; display: block;">Control Sanitario 74°</span>
      <div style="font-size: 2.3rem; color: var(--color-slate-deep); margin-top: 0.35rem;" class="cifra-delgada">
        @if($totalPendientesCalidad === 0)
          <span style="color: #15803d;">100%</span>
        @else
          <span style="color: #b45309;">{{ $totalPendientesCalidad }}</span>
        @endif
      </div>
      <span style="font-size: 0.75rem; color: var(--color-pewter);">
        {{ $totalPendientesCalidad === 0 ? 'Todas verificadas' : 'Porongos por verificar' }}
      </span>
    </div>
  </div>

  <!-- BARRA DE PESTAÑAS OPERATIVAS -->
  <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid var(--color-sky-wash); padding-bottom: 0.5rem; overflow-x: auto;">
    <button type="button" onclick="mostrarPestana('tab-acopio')" id="btn-tab-acopio" class="btn-lat nav-tab-activa" style="padding: 0.6rem 1.25rem; font-size: 0.9rem; font-weight: 600; border-radius: 6px; cursor: pointer; border: none;">
      1. Acopio por Voz y Tacto
    </button>
    <button type="button" onclick="mostrarPestana('tab-calidad')" id="btn-tab-calidad" class="btn-lat" style="padding: 0.6rem 1.25rem; font-size: 0.9rem; font-weight: 600; border-radius: 6px; cursor: pointer; border: none; background: var(--color-fog); color: var(--color-slate-deep);">
      2. Calidad Sanitaria (Triage)
    </button>
    <button type="button" onclick="mostrarPestana('tab-tina')" id="btn-tab-tina" class="btn-lat" style="padding: 0.6rem 1.25rem; font-size: 0.9rem; font-weight: 600; border-radius: 6px; cursor: pointer; border: none; background: var(--color-fog); color: var(--color-slate-deep);">
      3. Tina y Receta Automática
    </button>
    <button type="button" onclick="mostrarPestana('tab-quincena')" id="btn-tab-quincena" class="btn-lat" style="padding: 0.6rem 1.25rem; font-size: 0.9rem; font-weight: 600; border-radius: 6px; cursor: pointer; border: none; background: var(--color-fog); color: var(--color-slate-deep);">
      4. Liquidación Quincenal
    </button>
  </div>

  <!-- PESTAÑA 1: MÓDULO ACOPIO POR VOZ Y TECLADO 1 MANO -->
  <div id="tab-acopio" class="seccion-pestana">
    <div style="display: grid; grid-template-columns: 1fr 1.3fr; gap: 1.5rem; align-items: start;">
      <!-- Panel de Registro -->
      <div style="background-color: var(--color-fog); padding: 1.75rem; border-radius: 12px;">
        <h3 style="font-family: var(--font-display); font-size: 1.35rem; color: var(--color-slate-deep); margin-bottom: 0.5rem;">
          Registro Diario de Recepción
        </h3>
        <p style="color: var(--color-graphite); font-size: 0.85rem; margin-bottom: 1.25rem;">
          Dictado por voz con síntesis confirmatoria o ingreso manual de alta velocidad.
        </p>

        <!-- Botón de Reconocimiento por Voz -->
        <div style="margin-bottom: 1.5rem; text-align: center;">
          <button type="button" id="btn-voz" onclick="iniciarReconocimientoVoz()" class="btn-lat btn-ember" style="width: 100%; padding: 0.9rem; font-size: 1rem; font-weight: 600; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
            <span id="txt-btn-voz">Dictar Acopio por Voz</span>
          </button>
          <div id="transcripcion-voz" style="margin-top: 0.5rem; font-size: 0.82rem; color: var(--color-graphite); min-height: 20px; font-style: italic;">
            Ejemplo: "Don Eloy Condori, 35 litros"
          </div>
        </div>

        <form method="POST" action="{{ route('panel.acopio') }}" id="form-acopio">
          @csrf
          <input type="hidden" name="metodo" id="input-metodo" value="manual">
          <input type="hidden" name="frase_dictada" id="input-frase" value="">

          <div style="margin-bottom: 1rem;">
            <label style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--color-slate-deep); margin-bottom: 0.25rem;">
              Ganadero / Productor
            </label>
            <select name="proveedor_id" id="select-proveedor" required style="width: 100%; padding: 0.65rem 0.85rem; border: none; border-radius: 6px; background-color: #ffffff; color: var(--color-slate-deep); font-size: 0.9rem;">
              <option value="">Seleccione el ganadero...</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->codigo }} - {{ $prov->nombre }} ({{ $prov->sector }})</option>
              @endforeach
            </select>
          </div>

          <div style="margin-bottom: 1rem;">
            <label style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--color-slate-deep); margin-bottom: 0.25rem;">
              Volumen en Litros (L)
            </label>
            <input type="number" step="0.1" name="litros" id="input-litros" required placeholder="0.0"
                   style="width: 100%; padding: 0.75rem 1rem; border: none; border-radius: 6px; background-color: #ffffff; color: var(--color-slate-deep); font-size: 1.4rem; outline: none;" class="cifra-delgada">
          </div>

          <!-- Teclado Rápido Modo 1 Mano (Botones 54px) -->
          <div style="margin-bottom: 1.25rem;">
            <span style="font-size: 0.75rem; color: var(--color-pewter); text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 0.5rem;">
              Botonera Táctil Rápida (1 Mano)
            </span>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;">
              @foreach([10, 15, 20, 25, 30, 35, 40, 50] as $lit)
                <button type="button" onclick="sumarLitros({{ $lit }})" style="height: 48px; background: #ffffff; border: none; border-radius: 6px; font-weight: 600; color: var(--color-slate-deep); cursor: pointer; font-size: 0.95rem;">
                  +{{ $lit }}
                </button>
              @endforeach
            </div>
          </div>

          <button type="submit" class="btn-lat btn-ocean" style="width: 100%; padding: 0.8rem; font-size: 0.95rem; font-weight: 600; border-radius: 6px; border: none; cursor: pointer; background-color: var(--color-ocean); color: #ffffff;">
            Guardar Entrega en Tina
          </button>
        </form>
      </div>

      <!-- Tabla de Entregas del Día -->
      <div style="background-color: #ffffff; border: 1px solid var(--color-sky-wash); border-radius: 12px; overflow: hidden;">
        <div style="background-color: var(--color-sky-wash); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
          <h4 style="margin: 0; color: var(--color-slate-deep); font-size: 1rem;">Entregas de Hoy ({{ Carbon\Carbon::today()->format('d/m/Y') }})</h4>
          <span style="font-size: 0.8rem; font-weight: 600; color: var(--color-celeste);">Total: {{ number_format($litrosHoy, 1) }} L</span>
        </div>

        <table class="tabla-plana">
          <thead>
            <tr>
              <th>Hora</th>
              <th>Productor</th>
              <th style="text-align: right;">Litros</th>
              <th>Método</th>
              <th>Calidad</th>
            </tr>
          </thead>
          <tbody>
            @forelse($acopiosHoy as $acopio)
              <tr>
                <td style="font-size: 0.85rem;" class="cifra-delgada">{{ substr($acopio->hora, 0, 5) }}</td>
                <td>
                  <strong>{{ $acopio->proveedor->nombre }}</strong>
                  <span style="display: block; font-size: 0.75rem; color: var(--color-pewter);">{{ $acopio->proveedor->sector }}</span>
                </td>
                <td style="text-align: right; font-size: 1.1rem; color: var(--color-slate-deep);" class="cifra-delgada">
                  {{ number_format($acopio->litros, 1) }}
                </td>
                <td>
                  <span style="background: {{ $acopio->metodo === 'voz' ? 'var(--color-celeste-suave)' : 'var(--color-fog)' }}; color: var(--color-slate-deep); font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; font-weight: 500;">
                    {{ $acopio->metodo === 'voz' ? '🎙 Voz' : '⌨ Manual' }}
                  </span>
                </td>
                <td>
                  @if($acopio->calidad === 'buena')
                    <span style="background: var(--verde-fill); color: var(--verde-text); font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Aprobada</span>
                  @elseif($acopio->calidad === 'acida')
                    <span style="background: var(--rojo-fill); color: var(--rojo-text); font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Ácida (Quesillo)</span>
                  @else
                    <span style="background: var(--amarillo-fill); color: var(--amarillo-text); font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Pendiente</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" style="text-align: center; color: var(--color-pewter); padding: 2rem;">No hay entregas registradas hoy.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- PESTAÑA 2: MÓDULO CALIDAD SANITARIA (TRIAGE ÉTICO 74°) -->
  <div id="tab-calidad" class="seccion-pestana" style="display: none;">
    <div style="background-color: var(--color-fog); border-radius: 12px; padding: 1.75rem; margin-bottom: 1.5rem;">
      <h3 style="font-family: var(--font-display); font-size: 1.35rem; color: var(--color-slate-deep); margin-bottom: 0.35rem;">
        Control Sanitario en Recepción (Prueba Rápida de Alcohol a 74°)
      </h3>
      <p style="color: var(--color-graphite); font-size: 0.88rem; line-height: 1.6; margin-bottom: 1.25rem;">
        <strong>Principio de Cero Desperdicio:</strong> La leche que presenta acidez no se desecha ni se multa al campesino. Se clasifica de inmediato y se deriva éticamente a la producción de quesillos frescos de 4 días, protegiendo la economía comunitaria.
      </p>

      <table class="tabla-plana" style="background: #ffffff;">
        <thead>
          <tr>
            <th>Productor</th>
            <th>Litros</th>
            <th>Estado Actual</th>
            <th>Destino Operativo</th>
            <th style="text-align: center;">Triage Inmediato</th>
          </tr>
        </thead>
        <tbody>
          @foreach($acopiosHoy as $acopio)
            <tr>
              <td>
                <strong>{{ $acopio->proveedor->nombre }}</strong>
                <span style="font-size: 0.75rem; color: var(--color-pewter); display: block;">{{ $acopio->proveedor->codigo }}</span>
              </td>
              <td style="font-size: 1.1rem; color: var(--color-slate-deep);" class="cifra-delgada">
                {{ number_format($acopio->litros, 1) }} L
              </td>
              <td>
                @if($acopio->calidad === 'buena')
                  <span style="background: var(--verde-fill); color: var(--verde-text); font-size: 0.78rem; padding: 3px 8px; border-radius: 4px; font-weight: 600;">Óptima</span>
                @elseif($acopio->calidad === 'acida')
                  <span style="background: var(--rojo-fill); color: var(--rojo-text); font-size: 0.78rem; padding: 3px 8px; border-radius: 4px; font-weight: 600;">Con Acidez</span>
                @else
                  <span style="background: var(--amarillo-fill); color: var(--amarillo-text); font-size: 0.78rem; padding: 3px 8px; border-radius: 4px; font-weight: 600;">Sin Probar</span>
                @endif
              </td>
              <td>
                <span style="font-size: 0.85rem; color: var(--color-slate-deep); font-weight: 500;">
                  {{ $acopio->destino === 'quesillo_4_dias' ? 'Tina Quesillo 4 Días' : 'Tina Principal (Queso/Yogur)' }}
                </span>
              </td>
              <td style="text-align: center;">
                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                  <form method="POST" action="{{ route('panel.calidad', $acopio->id) }}">
                    @csrf
                    <input type="hidden" name="calidad" value="buena">
                    <button type="submit" style="background: #15803d; color: #ffffff; border: none; padding: 0.4rem 0.75rem; border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                      ✓ Aprobar Leche
                    </button>
                  </form>
                  <form method="POST" action="{{ route('panel.calidad', $acopio->id) }}">
                    @csrf
                    <input type="hidden" name="calidad" value="acida">
                    <button type="submit" style="background: #b91c1c; color: #ffffff; border: none; padding: 0.4rem 0.75rem; border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                      ⚠ Desviar a Quesillo
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- PESTAÑA 3: MÓDULO TINA Y RECETA AUTOMÁTICA -->
  <div id="tab-tina" class="seccion-pestana" style="display: none;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
      <!-- Monitor Térmico -->
      <div style="background-color: var(--color-ocean); color: #ffffff; padding: 2rem; border-radius: 12px;">
        <span style="font-size: 0.82rem; color: #bae6fd; text-transform: uppercase; letter-spacing: 0.5px;">Monitoreo Térmico</span>
        <h3 style="font-family: var(--font-display); font-size: 1.5rem; color: #ffffff; margin-top: 0.25rem;">
          Lote: {{ $loteActivo->codigo }}
        </h3>
        <p style="color: #bae6fd; font-size: 0.85rem; margin-bottom: 1.5rem;">
          Curva de enfriamiento: Pasteurización a 72 °C hasta punto de cuajado (36-40 °C).
        </p>

        <div style="background: rgba(255,255,255,0.08); padding: 1.5rem; border-radius: 8px; text-align: center; margin-bottom: 1.5rem;">
          <div style="font-size: 3.5rem; font-weight: 300;" class="cifra-delgada">
            {{ number_format($loteActivo->temperatura_actual, 1) }} °C
          </div>
          <span style="display: inline-block; background-color: {{ $termica['apta'] ? '#22c55e' : '#f59e0b' }}; color: #ffffff; padding: 4px 12px; border-radius: 12px; font-size: 0.82rem; font-weight: 600; margin-top: 0.5rem;">
            {{ strtoupper($termica['estado']) }} (Avance: {{ $termica['porcentaje'] }}%)
          </span>
        </div>

        <form method="POST" action="{{ route('panel.tina', $loteActivo->id) }}">
          @csrf
          <label style="display: block; font-size: 0.82rem; color: #bae6fd; margin-bottom: 0.35rem;">
            Simular / Ajustar Sensor Térmico (°C):
          </label>
          <div style="display: flex; gap: 0.5rem;">
            <input type="number" step="0.5" name="temperatura" value="{{ $loteActivo->temperatura_actual }}" style="flex: 1; padding: 0.65rem 1rem; border: none; border-radius: 6px; font-size: 1.1rem; color: var(--color-slate-deep);" class="cifra-delgada">
            <button type="submit" class="btn-lat btn-ember" style="padding: 0.65rem 1.25rem; font-size: 0.88rem; font-weight: 600; border: none; border-radius: 6px; cursor: pointer;">
              Actualizar Sensor
            </button>
          </div>
        </form>
      </div>

      <!-- Receta Parametrizada -->
      <div style="background-color: var(--color-fog); padding: 2rem; border-radius: 12px;">
        <span style="font-size: 0.82rem; color: var(--color-celeste); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Estandarización de Calidad</span>
        <h3 style="font-family: var(--font-display); font-size: 1.5rem; color: var(--color-slate-deep); margin-top: 0.25rem;">
          Dosificación de Tina ({{ $receta['litros'] }} L)
        </h3>
        <p style="color: var(--color-graphite); font-size: 0.85rem; margin-bottom: 1.5rem;">
          Cálculo exacto para garantizar uniformidad en acidez, salinidad y firmeza del cuajo.
        </p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div style="background: #ffffff; padding: 1.25rem; border-radius: 8px;">
            <span style="font-size: 0.78rem; color: var(--color-pewter); text-transform: uppercase; display: block;">Cuajo Líquido</span>
            <div style="font-size: 1.8rem; color: var(--color-slate-deep);" class="cifra-delgada">
              {{ $receta['cuajo_ml'] }} <span style="font-size: 0.9rem; color: var(--color-graphite);">ml</span>
            </div>
            <span style="font-size: 0.75rem; color: var(--color-celeste);">Ratio: 2.5 ml / 100 L</span>
          </div>

          <div style="background: #ffffff; padding: 1.25rem; border-radius: 8px;">
            <span style="font-size: 0.78rem; color: var(--color-pewter); text-transform: uppercase; display: block;">Sal Común</span>
            <div style="font-size: 1.8rem; color: var(--color-slate-deep);" class="cifra-delgada">
              {{ $receta['sal_kg'] }} <span style="font-size: 0.9rem; color: var(--color-graphite);">kg</span>
            </div>
            <span style="font-size: 0.75rem; color: var(--color-celeste);">Ratio: 1.8 kg / 100 L</span>
          </div>

          <div style="background: #ffffff; padding: 1.25rem; border-radius: 8px;">
            <span style="font-size: 0.78rem; color: var(--color-pewter); text-transform: uppercase; display: block;">Cultivo Iniciador</span>
            <div style="font-size: 1.8rem; color: var(--color-slate-deep);" class="cifra-delgada">
              {{ $receta['cultivo_g'] }} <span style="font-size: 0.9rem; color: var(--color-graphite);">g</span>
            </div>
            <span style="font-size: 0.75rem; color: var(--color-celeste);">Ratio: 1.0 g / 100 L</span>
          </div>

          <div style="background: #ffffff; padding: 1.25rem; border-radius: 8px;">
            <span style="font-size: 0.78rem; color: var(--color-pewter); text-transform: uppercase; display: block;">Moldes Estimados</span>
            <div style="font-size: 1.8rem; color: var(--color-ember);" class="cifra-delgada">
              {{ $receta['moldes_estimados'] }} <span style="font-size: 0.9rem; color: var(--color-graphite);">und (1kg)</span>
            </div>
            <span style="font-size: 0.75rem; color: var(--color-pewter);">Rendimiento: 9 L / molde</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- PESTAÑA 4: MÓDULO LIQUIDACIÓN QUINCENAL Y CUENTAS CLARAS -->
  <div id="tab-quincena" class="seccion-pestana" style="display: none;">
    <div style="background-color: var(--color-fog); border-radius: 12px; padding: 1.75rem; margin-bottom: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <h3 style="font-family: var(--font-display); font-size: 1.35rem; color: var(--color-slate-deep); margin-bottom: 0.25rem;">
            Liquidación Quincenal de Proveedores ({{ $desde }} al {{ $hasta }})
          </h3>
          <p style="color: var(--color-graphite); font-size: 0.85rem; margin: 0;">
            Cálculo neto transparente: Bruto - Adelantos en Efectivo / Queso. <strong>Comisión: S/ 0.00</strong>.
          </p>
        </div>
        <a href="{{ route('panel.exportar') }}" class="btn-lat btn-ember" style="padding: 0.65rem 1.25rem; font-size: 0.88rem; font-weight: 600; text-decoration: none; border-radius: 6px;">
          Descargar Balance Oficial en CSV
        </a>
      </div>

      <table class="tabla-plana" style="background: #ffffff; margin-bottom: 1.5rem;">
        <thead>
          <tr>
            <th>Código</th>
            <th>Ganadero</th>
            <th>Sector</th>
            <th style="text-align: right;">Litros</th>
            <th style="text-align: right;">Bruto</th>
            <th style="text-align: right;">Adelantos</th>
            <th style="text-align: right;">Comisión</th>
            <th style="text-align: right; background-color: var(--color-sky-wash);">Neto a Pagar</th>
          </tr>
        </thead>
        <tbody>
          @foreach($liquidaciones as $liq)
            <tr>
              <td style="font-size: 0.85rem;" class="cifra-delgada">{{ $liq->codigo }}</td>
              <td><strong>{{ $liq->nombre }}</strong></td>
              <td style="color: var(--color-pewter); font-size: 0.85rem;">{{ $liq->sector }}</td>
              <td style="text-align: right;" class="cifra-delgada">{{ number_format($liq->litros, 1) }} L</td>
              <td style="text-align: right;" class="cifra-delgada">S/ {{ number_format($liq->bruto, 2) }}</td>
              <td style="text-align: right; color: #b91c1c;" class="cifra-delgada">- S/ {{ number_format($liq->adelantos, 2) }}</td>
              <td style="text-align: right; color: #15803d; font-weight: 600;" class="cifra-delgada">S/ 0.00</td>
              <td style="text-align: right; font-size: 1.15rem; font-weight: 500; color: var(--color-slate-deep); background-color: #f7fbfe;" class="cifra-delgada">
                S/ {{ number_format($liq->neto, 2) }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <!-- Subformulario: Adelanto o Jornal Eventual (Ley 29733) -->
      <div style="background-color: var(--color-sky-wash); padding: 1.25rem; border-radius: 8px;">
        <h4 style="margin: 0 0 0.5rem; color: var(--color-slate-deep); font-size: 0.95rem;">
          Registrar Adelanto o Pago de Ayuda del Día (Mano de Obra Eventual)
        </h4>
        <p style="font-size: 0.8rem; color: var(--color-graphite); margin-bottom: 1rem;">
          Cumplimiento Ley N.° 29733: Los pagos en mano por faenas de lavado o volteo no requieren DNI ni datos personales sensibles.
        </p>

        <form method="POST" action="{{ route('panel.movimiento') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto; gap: 0.75rem; align-items: end;">
          @csrf
          <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-slate-deep); margin-bottom: 0.25rem;">Tipo</label>
            <select name="tipo" style="width: 100%; padding: 0.6rem; border: none; border-radius: 4px; font-size: 0.85rem; background: #ffffff;">
              <option value="adelanto_efectivo">Adelanto en Efectivo</option>
              <option value="adelanto_queso">Adelanto en Moldes de Queso</option>
              <option value="ayuda_del_dia">Pago de Ayuda del Día (Sin DNI)</option>
              <option value="gasto_operativo">Gasto Operativo / Insumos</option>
            </select>
          </div>

          <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-slate-deep); margin-bottom: 0.25rem;">Productor (si aplica)</label>
            <select name="proveedor_id" style="width: 100%; padding: 0.6rem; border: none; border-radius: 4px; font-size: 0.85rem; background: #ffffff;">
              <option value="">Ninguno / Faena externa</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-slate-deep); margin-bottom: 0.25rem;">Concepto</label>
            <input type="text" name="concepto" required placeholder="Ej: Compra de salmuera o faena" style="width: 100%; padding: 0.6rem; border: none; border-radius: 4px; font-size: 0.85rem; background: #ffffff;">
          </div>

          <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--color-slate-deep); margin-bottom: 0.25rem;">Monto (S/)</label>
            <input type="number" step="0.5" name="monto" required placeholder="0.00" style="width: 100%; padding: 0.6rem; border: none; border-radius: 4px; font-size: 0.85rem; background: #ffffff;" class="cifra-delgada">
          </div>

          <button type="submit" class="btn-lat btn-ocean" style="padding: 0.65rem 1.25rem; font-size: 0.85rem; font-weight: 600; border: none; border-radius: 4px; cursor: pointer; background: var(--color-ocean); color: #fff;">
            Asentar
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- SCRIPT DE NAVEGACIÓN Y WEB SPEECH API -->
  <script>
    function mostrarPestana(id) {
      document.querySelectorAll('.seccion-pestana').forEach(el => el.style.display = 'none');
      document.querySelectorAll('.nav-tab-activa').forEach(el => {
        el.classList.remove('nav-tab-activa');
        el.style.background = 'var(--color-fog)';
        el.style.color = 'var(--color-slate-deep)';
      });

      const seccion = document.getElementById(id);
      if (seccion) seccion.style.display = 'block';

      const btn = document.getElementById('btn-' + id);
      if (btn) {
        btn.classList.add('nav-tab-activa');
        btn.style.background = 'var(--color-ember)';
        btn.style.color = '#ffffff';
      }
    }

    function sumarLitros(cant) {
      const input = document.getElementById('input-litros');
      const actual = parseFloat(input.value) || 0;
      input.value = (actual + cant).toFixed(1);
    }

    // Reconocimiento de Voz nativo (Web Speech API) para Acopio a 1 Mano
    function iniciarReconocimientoVoz() {
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SpeechRecognition) {
        alert("El navegador no soporta Web Speech API. Use el teclado táctil de 54px.");
        return;
      }

      const recognition = new SpeechRecognition();
      recognition.lang = 'es-PE';
      recognition.interimResults = false;

      const btnVoz = document.getElementById('btn-voz');
      const txtBtn = document.getElementById('txt-btn-voz');
      const transcripcion = document.getElementById('transcripcion-voz');

      txtBtn.innerText = "Escuchando... Hable ahora";
      btnVoz.style.background = "#b91c1c";

      recognition.onresult = function(event) {
        const frase = event.results[0][0].transcript;
        transcripcion.innerText = `Escuchado: "${frase}"`;
        document.getElementById('input-frase').value = frase;
        document.getElementById('input-metodo').value = 'voz';

        // Extracción regex de números
        const match = frase.match(/(\d+([\.,]\d+)?)/);
        if (match) {
          const litros = parseFloat(match[1].replace(',', '.'));
          document.getElementById('input-litros').value = litros;
        }

        // Síntesis de voz confirmatoria
        if ('speechSynthesis' in window) {
          const utter = new SpeechSynthesisUtterance("Acopio capturado correctamente");
          utter.lang = 'es-PE';
          window.speechSynthesis.speak(utter);
        }

        txtBtn.innerText = "Dictar Acopio por Voz";
        btnVoz.style.background = "var(--color-ember)";
      };

      recognition.onerror = function() {
        txtBtn.innerText = "Dictar Acopio por Voz";
        btnVoz.style.background = "var(--color-ember)";
      };

      recognition.start();
    }
  </script>
@endsection
