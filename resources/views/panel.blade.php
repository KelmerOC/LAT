@extends('layouts.app')

@section('titulo', 'LAT · Mi Panel Operativo')

@php
  $icoMic = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M19 10v1a7 7 0 0 1-14 0v-1M12 18v4"/></svg>';
  $icoMicS = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M19 10v1a7 7 0 0 1-14 0v-1M12 18v4"/></svg>';
  $icoTec = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 10h.01M10 10h.01M14 10h.01M18 10h.01M7 14h10"/></svg>';
  $icoOk = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
  $icoAlerta = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 20h20L12 3Z"/><path d="M12 10v4M12 17h.01"/></svg>';
  $icoDescarga = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>';
  $icoTina = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/></svg>';
  $icoBalance = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M9 14h6M9 18h6M9 10h2"/></svg>';
@endphp

@section('contenido')
<div class="contenedor" style="padding-block: 1.5rem 3rem;">

  <!-- Encabezado de sesión -->
  <section class="panel-cabecera" aria-labelledby="titulo-panel">
    <div>
      <p style="font-size: 0.875rem; font-weight: 600;">Centro de acopio y transformación</p>
      <h1 id="titulo-panel">Planta Láctea San Borreal · Cusco Provincia</h1>
      <p style="font-size: 0.9375rem;">
        Operador: <strong>{{ $usuario->name }}</strong> ·
        Cargo: <span class="chip chip-celeste">{{ strtoupper(str_replace('_', ' ', $usuario->cargo)) }}</span>
      </p>
    </div>
    <div class="panel-cabecera-acciones">
      <a href="{{ route('panel.exportar') }}" class="btn btn-ember">{!! $icoDescarga !!} Exportar balance CSV</a>
    </div>
  </section>

  <!-- KPIs -->
  <ul class="kpis" aria-label="Indicadores del día">
    <li class="kpi">
      <span class="kpi-etq">Leche recibida hoy</span>
      <span class="kpi-numero">{{ number_format($litrosHoy, 1) }}<small>L</small></span>
      <span class="kpi-nota">{{ $acopiosHoy->count() }} entregas registradas</span>
    </li>
    <li class="kpi kpi-celeste">
      <span class="kpi-etq">Precio vigente por litro</span>
      <span class="kpi-numero">S/ {{ number_format($precioLitro, 2) }}</span>
      <span class="kpi-nota">Comisión LAT: S/ 0.00</span>
    </li>
    <li class="kpi kpi-oceano">
      <span class="kpi-etq">Tina pasteurizadora</span>
      <span class="kpi-numero">{{ number_format($loteActivo->temperatura_actual, 1) }}<small>°C</small></span>
      <span class="kpi-nota">Estado: {{ strtoupper($termica['estado']) }} ({{ $termica['porcentaje'] }}%)</span>
    </li>
    <li class="kpi">
      <span class="kpi-etq">Control sanitario 74°</span>
      <span class="kpi-numero">
        @if($totalPendientesCalidad === 0) 100<small>%</small> @else {{ $totalPendientesCalidad }} @endif
      </span>
      <span class="kpi-nota">{{ $totalPendientesCalidad === 0 ? 'Todas las entregas verificadas' : 'Porongos por verificar' }}</span>
    </li>
  </ul>

  <!-- Selector Desplegable de Módulos (Exclusivo Celulares <= 768px) -->
  <div class="panel-modulo-dropdown" id="panel-modulo-dropdown">
    <label for="btn-dropdown-modulos" class="sr-only">Seleccionar módulo operativo</label>
    <button type="button" class="dropdown-trigger" id="btn-dropdown-modulos" aria-expanded="false" aria-haspopup="listbox" aria-controls="dropdown-lista-modulos" aria-label="Cambiar módulo operativo">
      <span class="dropdown-trigger-icon" id="dropdown-icon-activo" aria-hidden="true">
        {!! $icoMicS !!}
      </span>
      <span class="dropdown-trigger-text">
        <small class="dropdown-trigger-sub">Módulo Operativo Activo</small>
        <strong id="dropdown-titulo-activo" class="dropdown-trigger-tit">1. Acopio por voz y tacto</strong>
      </span>
      <svg class="dropdown-trigger-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </button>

    <div class="dropdown-menu-list" id="dropdown-lista-modulos" role="listbox" aria-label="Módulos de la planta">
      <button type="button" class="dropdown-item activo" role="option" aria-selected="true" data-tab="tab-acopio" data-titulo="1. Acopio por voz y tacto" data-icon="voz">
        <span class="dropdown-item-num">1</span>
        <div class="dropdown-item-info">
          <strong>Acopio por voz y tacto</strong>
          <small>Recepción y pesaje diario en porongo</small>
        </div>
      </button>

      <button type="button" class="dropdown-item" role="option" aria-selected="false" data-tab="tab-calidad" data-titulo="2. Calidad sanitaria (Triage 74°)" data-icon="calidad">
        <span class="dropdown-item-num">2</span>
        <div class="dropdown-item-info">
          <strong>Calidad sanitaria (Triage 74°)</strong>
          <small>Prueba alcohol 74° y desvío a quesillo</small>
        </div>
      </button>

      <button type="button" class="dropdown-item" role="option" aria-selected="false" data-tab="tab-tina" data-titulo="3. Tina y receta automática" data-icon="tina">
        <span class="dropdown-item-num">3</span>
        <div class="dropdown-item-info">
          <strong>Tina y receta automática</strong>
          <small>Control térmico 72° a 38°C y dosificación</small>
        </div>
      </button>

      <button type="button" class="dropdown-item" role="option" aria-selected="false" data-tab="tab-quincena" data-titulo="4. Liquidación quincenal" data-icon="quincena">
        <span class="dropdown-item-num">4</span>
        <div class="dropdown-item-info">
          <strong>Liquidación quincenal</strong>
          <small>Cuentas claras S/ 0.00 y balances CSV</small>
        </div>
      </button>
    </div>
  </div>

  <!-- Pestañas de módulos (Desktop > 768px) -->
  <div class="tabs-bar tabs-bar-desktop" role="tablist" aria-label="Módulos operativos de la planta" id="tablist">
    <button type="button" role="tab" id="tab-btn-acopio" class="tab" aria-selected="true" aria-controls="tab-acopio" tabindex="0">1. Acopio por voz y tacto</button>
    <button type="button" role="tab" id="tab-btn-calidad" class="tab" aria-selected="false" aria-controls="tab-calidad" tabindex="-1">2. Calidad sanitaria</button>
    <button type="button" role="tab" id="tab-btn-tina" class="tab" aria-selected="false" aria-controls="tab-tina" tabindex="-1">3. Tina y receta</button>
    <button type="button" role="tab" id="tab-btn-quincena" class="tab" aria-selected="false" aria-controls="tab-quincena" tabindex="-1">4. Liquidación quincenal</button>
  </div>

  <!-- MÓDULO 1: ACOPIO -->
  <section id="tab-acopio" class="tab-panel" role="tabpanel" aria-labelledby="tab-btn-acopio" tabindex="0">
    <div class="panel-grid-2">
      <div class="panel-card on-fog">
        <h2>Registro diario de recepción</h2>
        <p>Dicte por voz con confirmación hablada o ingrese la cantidad de forma manual.</p>

        <button type="button" id="btn-voz" class="btn btn-ember btn-grande btn-bloque btn-voz" aria-pressed="false">
          {!! $icoMic !!}
          <span id="txt-btn-voz">Dictar acopio por voz</span>
        </button>
        <div id="transcripcion-voz" class="transcripcion" role="status" aria-live="polite">Ejemplo: “Don Eloy Condori, 35 litros”</div>

        <form method="POST" action="{{ route('panel.acopio') }}" id="form-acopio">
          @csrf
          <input type="hidden" name="metodo" id="input-metodo" value="manual">
          <input type="hidden" name="frase_dictada" id="input-frase" value="">

          <div class="form-grupo">
            <label for="select-proveedor" class="form-label">Ganadero / productor</label>
            <select name="proveedor_id" id="select-proveedor" class="select-lat" required>
              <option value="">Seleccione el ganadero…</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->codigo }} - {{ $prov->nombre }} ({{ $prov->sector }})</option>
              @endforeach
            </select>
          </div>

          <div class="form-grupo">
            <label for="input-litros" class="form-label">Volumen en litros (L)</label>
            <input type="number" step="0.1" min="0.5" max="5000" inputmode="decimal" name="litros" id="input-litros" class="input-lat input-litros" required placeholder="0.0">
          </div>

          <div class="form-grupo" role="group" aria-labelledby="botonera-etq">
            <span class="botonera-etq" id="botonera-etq">Botonera táctil rápida (suma litros)</span>
            <div class="botonera">
              @foreach([10, 15, 20, 25, 30, 35, 40, 50] as $lit)
                <button type="button" data-litros="{{ $lit }}" aria-label="Sumar {{ $lit }} litros">+{{ $lit }}</button>
              @endforeach
            </div>
          </div>

          <button type="submit" class="btn btn-ocean btn-bloque btn-grande">Guardar entrega en tina</button>
        </form>
      </div>

      <div class="panel-tabla-wrap">
        <div class="panel-tabla-cab">
          <h3>Entregas de hoy ({{ Carbon\Carbon::today()->format('d/m/Y') }})</h3>
          <span class="chip chip-celeste num-tabular">Total: {{ number_format($litrosHoy, 1) }} L</span>
        </div>
        <div class="tabla-region" role="region" aria-label="Entregas de leche de hoy" tabindex="0">
          <table class="tabla-plana">
            <thead>
              <tr>
                <th scope="col">Hora</th>
                <th scope="col">Productor</th>
                <th scope="col" class="num">Litros</th>
                <th scope="col">Método</th>
                <th scope="col">Calidad</th>
              </tr>
            </thead>
            <tbody>
              @forelse($acopiosHoy as $acopio)
                <tr>
                  <td class="num-tabular">{{ substr($acopio->hora, 0, 5) }}</td>
                  <td><strong>{{ $acopio->proveedor->nombre }}</strong><small>{{ $acopio->proveedor->sector }}</small></td>
                  <td class="num" style="font-size: 1.1rem;">{{ number_format($acopio->litros, 1) }}</td>
                  <td>
                    @if($acopio->metodo === 'voz')
                      <span class="chip chip-celeste">{!! $icoMicS !!} Voz</span>
                    @else
                      <span class="chip chip-fog">{!! $icoTec !!} Manual</span>
                    @endif
                  </td>
                  <td>
                    @if($acopio->calidad === 'buena')
                      <span class="chip chip-verde">{!! $icoOk !!} Aprobada</span>
                    @elseif($acopio->calidad === 'acida')
                      <span class="chip chip-amarillo">{!! $icoAlerta !!} Ácida (quesillo)</span>
                    @else
                      <span class="chip chip-amarillo">Pendiente</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" style="text-align: center; padding: 2rem; color: var(--color-graphite);">No hay entregas registradas hoy.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <!-- MÓDULO 2: CALIDAD -->
  <section id="tab-calidad" class="tab-panel" role="tabpanel" aria-labelledby="tab-btn-calidad" tabindex="0" hidden>
    <div class="panel-card">
      <h2>Control sanitario en recepción (prueba de alcohol a 74°)</h2>
      <p><strong>Principio de cero desperdicio:</strong> la leche con acidez no se desecha ni se multa al productor. Se clasifica de inmediato y se deriva a quesillos frescos de 4 días.</p>

      <div class="tabla-region" role="region" aria-label="Clasificación sanitaria de las entregas de hoy" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th scope="col">Productor</th>
              <th scope="col" class="num">Litros</th>
              <th scope="col">Estado actual</th>
              <th scope="col">Destino operativo</th>
              <th scope="col">Triage inmediato</th>
            </tr>
          </thead>
          <tbody>
            @forelse($acopiosHoy as $acopio)
              <tr>
                <td><strong>{{ $acopio->proveedor->nombre }}</strong><small>{{ $acopio->proveedor->codigo }}</small></td>
                <td class="num" style="font-size: 1.1rem;">{{ number_format($acopio->litros, 1) }} L</td>
                <td>
                  @if($acopio->calidad === 'buena')
                    <span class="chip chip-verde">{!! $icoOk !!} Óptima</span>
                  @elseif($acopio->calidad === 'acida')
                    <span class="chip chip-amarillo">{!! $icoAlerta !!} Con acidez</span>
                  @else
                    <span class="chip chip-amarillo">Sin probar</span>
                  @endif
                </td>
                <td>{{ $acopio->destino === 'quesillo_4_dias' ? 'Tina quesillo 4 días' : 'Tina principal (queso / yogur)' }}</td>
                <td>
                  <div class="acciones-celda">
                    <form method="POST" action="{{ route('panel.calidad', $acopio->id) }}">
                      @csrf
                      <input type="hidden" name="calidad" value="buena">
                      <button type="submit" class="btn btn-verde btn-compacto" aria-label="Aprobar leche de {{ $acopio->proveedor->nombre }}">{!! $icoOk !!} Aprobar</button>
                    </form>
                    <form method="POST" action="{{ route('panel.calidad', $acopio->id) }}">
                      @csrf
                      <input type="hidden" name="calidad" value="acida">
                      <button type="submit" class="btn btn-ember btn-compacto" aria-label="Desviar a quesillo la leche de {{ $acopio->proveedor->nombre }}">{!! $icoAlerta !!} Desviar a quesillo</button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" style="text-align: center; padding: 2rem; color: var(--color-graphite);">No hay entregas para clasificar hoy.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- MÓDULO 3: TINA -->
  <section id="tab-tina" class="tab-panel" role="tabpanel" aria-labelledby="tab-btn-tina" tabindex="0" hidden>
    <div class="panel-grid-eq">
      <div class="panel-card panel-card-oceano">
        <p style="font-size: 0.875rem; font-weight: 600;">Monitoreo térmico</p>
        <h2>Lote {{ $loteActivo->codigo }}</h2>
        <p>Curva de enfriamiento: pasteurización a 72 °C hasta el punto de cuajado (36–40 °C).</p>

        <div class="termico">
          <div class="termico-valor">{{ number_format($loteActivo->temperatura_actual, 1) }} °C</div>
          <div class="barra-termica" role="progressbar" aria-label="Avance del enfriamiento de la tina" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $termica['porcentaje'] }}" aria-valuetext="{{ $termica['porcentaje'] }}% de avance, estado {{ $termica['estado'] }}">
            <div style="width: {{ min(100, max(0, $termica['porcentaje'])) }}%;"></div>
          </div>
          <span class="chip {{ $termica['apta'] ? 'chip-verde' : 'chip-amarillo' }}">{{ strtoupper($termica['estado']) }} · Avance {{ $termica['porcentaje'] }}%</span>
        </div>

        <form method="POST" action="{{ route('panel.tina', $loteActivo->id) }}">
          @csrf
          <div class="fila-sensor on-fog">
            <div class="form-grupo">
              <label for="input-temperatura" class="form-label">Simular o ajustar sensor térmico (°C)</label>
              <input type="number" step="0.5" id="input-temperatura" name="temperatura" class="input-lat input-litros" value="{{ $loteActivo->temperatura_actual }}">
            </div>
            <button type="submit" class="btn btn-ember">Actualizar sensor</button>
          </div>
        </form>
      </div>

      <div class="panel-card">
        <p style="font-size: 0.875rem; font-weight: 600;">Estandarización de calidad</p>
        <h2>Dosificación de tina ({{ $receta['litros'] }} L)</h2>
        <p>Cálculo para mantener uniformidad en acidez, salinidad y firmeza del cuajo.</p>

        <div class="receta">
          <div class="receta-item"><span class="etq">Cuajo líquido</span><span class="val">{{ $receta['cuajo_ml'] }} <small>ml</small></span><span class="ratio">Ratio: 2.5 ml / 100 L</span></div>
          <div class="receta-item"><span class="etq">Sal común</span><span class="val">{{ $receta['sal_kg'] }} <small>kg</small></span><span class="ratio">Ratio: 1.8 kg / 100 L</span></div>
          <div class="receta-item"><span class="etq">Cultivo iniciador</span><span class="val">{{ $receta['cultivo_g'] }} <small>g</small></span><span class="ratio">Ratio: 1.0 g / 100 L</span></div>
          <div class="receta-item"><span class="etq">Moldes estimados</span><span class="val" style="color: var(--color-ember-action);">{{ $receta['moldes_estimados'] }} <small>und (1 kg)</small></span><span class="ratio">Rendimiento estimado por volumen</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- MÓDULO 4: QUINCENA -->
  <section id="tab-quincena" class="tab-panel" role="tabpanel" aria-labelledby="tab-btn-quincena" tabindex="0" hidden>
    <div class="panel-card">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <h2>Liquidación quincenal de proveedores ({{ $desde }} al {{ $hasta }})</h2>
          <p style="margin: 0;">Cálculo neto transparente: bruto menos adelantos en efectivo o queso. <strong>Comisión: S/ 0.00.</strong></p>
        </div>
        <a href="{{ route('panel.exportar') }}" class="btn btn-ember">{!! $icoDescarga !!} Descargar balance en CSV</a>
      </div>

      <div class="tabla-region" role="region" aria-label="Liquidación quincenal de proveedores" tabindex="0" style="margin-bottom: 1.5rem;">
        <table class="tabla-plana" style="min-width: 720px;">
          <thead>
            <tr>
              <th scope="col">Código</th>
              <th scope="col">Ganadero</th>
              <th scope="col">Sector</th>
              <th scope="col" class="num">Litros</th>
              <th scope="col" class="num">Bruto</th>
              <th scope="col" class="num">Adelantos</th>
              <th scope="col" class="num">Comisión</th>
              <th scope="col" class="num" style="background: var(--color-sky-wash);">Neto a pagar</th>
            </tr>
          </thead>
          <tbody>
            @forelse($liquidaciones as $liq)
              <tr>
                <td class="num-tabular">{{ $liq->codigo }}</td>
                <td><strong>{{ $liq->nombre }}</strong></td>
                <td>{{ $liq->sector }}</td>
                <td class="num">{{ number_format($liq->litros, 1) }} L</td>
                <td class="num">S/ {{ number_format($liq->bruto, 2) }}</td>
                <td class="num" style="color: var(--rojo-text);">- S/ {{ number_format($liq->adelantos, 2) }}</td>
                <td class="num" style="color: var(--verde-text);">S/ 0.00</td>
                <td class="num" style="font-size: 1.15rem; background: #f7fbfe;">S/ {{ number_format($liq->neto, 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="8" style="text-align: center; padding: 2rem; color: var(--color-graphite);">No hay liquidaciones en este período.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="panel-card panel-card-celeste on-fog" style="padding: 1.5rem;">
        <h3 style="font-size: 1.1rem;">Registrar adelanto o pago de ayuda del día (mano de obra eventual)</h3>
        <p class="legal">Ley N.° 29733: los pagos en mano por faenas de lavado o volteo no requieren DNI ni datos personales sensibles.</p>

        <form method="POST" action="{{ route('panel.movimiento') }}" class="movimiento-form">
          @csrf
          <div class="form-grupo">
            <label for="mov-tipo" class="form-label">Tipo</label>
            <select name="tipo" id="mov-tipo" class="select-lat">
              <option value="adelanto_efectivo">Adelanto en efectivo</option>
              <option value="adelanto_queso">Adelanto en moldes de queso</option>
              <option value="ayuda_del_dia">Pago de ayuda del día (sin DNI)</option>
              <option value="gasto_operativo">Gasto operativo / insumos</option>
            </select>
          </div>
          <div class="form-grupo">
            <label for="mov-proveedor" class="form-label">Productor (si aplica)</label>
            <select name="proveedor_id" id="mov-proveedor" class="select-lat">
              <option value="">Ninguno / faena externa</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="mov-concepto" class="form-label">Concepto</label>
            <input type="text" name="concepto" id="mov-concepto" class="input-lat" required maxlength="160" placeholder="Ej: compra de salmuera o faena">
          </div>
          <div class="form-grupo">
            <label for="mov-monto" class="form-label">Monto (S/)</label>
            <input type="number" step="0.5" min="0.5" inputmode="decimal" name="monto" id="mov-monto" class="input-lat" required placeholder="0.00">
          </div>
          <button type="submit" class="btn btn-ocean">Asentar movimiento</button>
        </form>
      </div>
    </div>
  </section>
</div>

<script>
  // Navegación de módulos (sincronizada entre pestañas Desktop y selector desplegable Móvil)
  (function () {
    const tabs = Array.from(document.querySelectorAll('#tablist [role="tab"]'));
    const dropdownItems = Array.from(document.querySelectorAll('#dropdown-lista-modulos .dropdown-item'));
    const btnDropdown = document.getElementById('btn-dropdown-modulos');
    const menuDropdown = document.getElementById('dropdown-lista-modulos');
    const tituloActivo = document.getElementById('dropdown-titulo-activo');
    const iconActivo = document.getElementById('dropdown-icon-activo');

    const iconos = {
      'tab-acopio': '{!! $icoMicS !!}',
      'tab-calidad': '{!! $icoOk !!}',
      'tab-tina': '{!! $icoTina !!}',
      'tab-quincena': '{!! $icoBalance !!}'
    };

    function activarModulo(tabId, enfocarTab) {
      // 1. Mostrar el panel correspondiente
      document.querySelectorAll('.tab-panel').forEach(p => {
        p.hidden = p.id !== tabId;
      });

      // 2. Actualizar las pestañas de escritorio
      tabs.forEach(t => {
        const activa = t.getAttribute('aria-controls') === tabId;
        t.setAttribute('aria-selected', activa ? 'true' : 'false');
        t.tabIndex = activa ? 0 : -1;
        if (activa && enfocarTab) t.focus();
      });

      // 3. Actualizar el selector desplegable móvil
      dropdownItems.forEach(item => {
        const activa = item.dataset.tab === tabId;
        item.classList.toggle('activo', activa);
        item.setAttribute('aria-selected', activa ? 'true' : 'false');
        if (activa && tituloActivo) {
          tituloActivo.textContent = item.dataset.titulo;
          if (iconActivo && iconos[tabId]) iconActivo.innerHTML = iconos[tabId];
        }
      });

      // 4. Cerrar el menú desplegable móvil si está abierto
      if (btnDropdown && menuDropdown) {
        btnDropdown.setAttribute('aria-expanded', 'false');
        menuDropdown.classList.remove('abierto');
      }
    }

    // Interacción de pestañas de escritorio
    tabs.forEach((tab, i) => {
      tab.addEventListener('click', () => {
        activarModulo(tab.getAttribute('aria-controls'), false);
      });
      tab.addEventListener('keydown', e => {
        let destino = null;
        if (e.key === 'ArrowRight') destino = tabs[(i + 1) % tabs.length];
        else if (e.key === 'ArrowLeft') destino = tabs[(i - 1 + tabs.length) % tabs.length];
        else if (e.key === 'Home') destino = tabs[0];
        else if (e.key === 'End') destino = tabs[tabs.length - 1];
        if (destino) {
          e.preventDefault();
          activarModulo(destino.getAttribute('aria-controls'), true);
        }
      });
    });

    // Interacción del selector desplegable móvil
    if (btnDropdown && menuDropdown) {
      btnDropdown.addEventListener('click', () => {
        const abierto = menuDropdown.classList.toggle('abierto');
        btnDropdown.setAttribute('aria-expanded', abierto ? 'true' : 'false');
      });

      dropdownItems.forEach(item => {
        item.addEventListener('click', () => {
          activarModulo(item.dataset.tab, false);
        });
      });

      document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && menuDropdown.classList.contains('abierto')) {
          menuDropdown.classList.remove('abierto');
          btnDropdown.setAttribute('aria-expanded', 'false');
          btnDropdown.focus();
        }
      });

      // Cerrar al hacer clic fuera del dropdown
      document.addEventListener('click', e => {
        if (!e.target.closest('#panel-modulo-dropdown') && menuDropdown.classList.contains('abierto')) {
          menuDropdown.classList.remove('abierto');
          btnDropdown.setAttribute('aria-expanded', 'false');
        }
      });
    }
  })();

  // Botonera táctil de una mano
  document.querySelectorAll('.botonera button').forEach(b => {
    b.addEventListener('click', () => {
      const input = document.getElementById('input-litros');
      input.value = ((parseFloat(input.value) || 0) + parseFloat(b.dataset.litros)).toFixed(1);
    });
  });

  // Reconocimiento de voz (Web Speech API) con confirmación hablada
  (function () {
    const btnVoz = document.getElementById('btn-voz');
    const txtBtn = document.getElementById('txt-btn-voz');
    const transcripcion = document.getElementById('transcripcion-voz');

    function reposo() {
      txtBtn.textContent = 'Dictar acopio por voz';
      btnVoz.setAttribute('aria-pressed', 'false');
    }

    btnVoz.addEventListener('click', () => {
      const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SR) {
        transcripcion.textContent = 'Este navegador no admite dictado por voz. Use la botonera táctil o escriba los litros.';
        return;
      }
      const rec = new SR();
      rec.lang = 'es-PE';
      rec.interimResults = false;

      txtBtn.textContent = 'Escuchando… hable ahora';
      btnVoz.setAttribute('aria-pressed', 'true');

      rec.onresult = e => {
        const frase = e.results[0][0].transcript;
        transcripcion.textContent = 'Escuchado: “' + frase + '”';
        document.getElementById('input-frase').value = frase;
        document.getElementById('input-metodo').value = 'voz';
        const m = frase.match(/(\d+([\.,]\d+)?)/);
        if (m) document.getElementById('input-litros').value = parseFloat(m[1].replace(',', '.'));
        if ('speechSynthesis' in window) {
          const u = new SpeechSynthesisUtterance('Acopio capturado correctamente');
          u.lang = 'es-PE';
          window.speechSynthesis.speak(u);
        }
        reposo();
      };
      rec.onerror = () => { transcripcion.textContent = 'No se pudo escuchar. Intente de nuevo o use la botonera táctil.'; reposo(); };
      rec.onend = reposo;
      rec.start();
    });
  })();
</script>
@endsection
