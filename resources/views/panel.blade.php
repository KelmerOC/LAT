@extends('layouts.app')

@section('titulo', 'LAT · Mi Panel Operativo')

@php
  $icoMic = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M19 10v1a7 7 0 0 1-14 0v-1M12 18v4"/></svg>';
  $icoMicS = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M19 10v1a7 7 0 0 1-14 0v-1M12 18v4"/></svg>';
  $icoTec = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 10h.01M10 10h.01M14 10h.01M18 10h.01M7 14h10"/></svg>';
  $icoOk = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
  $icoAlerta = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 20h20L12 3Z"/><path d="M12 10v4M12 17h.01"/></svg>';
  $icoDescarga = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>';
  $icoEditar = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
  $icoTina = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/></svg>';
  $icoBalance = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M9 14h6M9 18h6M9 10h2"/></svg>';
  $icoStats = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 16v-5M12 16V8M17 16v-8"/></svg>';
  $icoAdmin = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>';

  $tiposTinaLabels = [
    'principal_queso' => 'Queso principal',
    'quesillo'        => 'Quesillo 4 días',
    'yogur'           => 'Yogur',
    'multiproposito'  => 'Multipropósito',
  ];
  $tiposMovLabels = [
    'adelanto_efectivo' => 'Adelanto efectivo',
    'adelanto_queso'    => 'Adelanto queso',
    'ayuda_del_dia'     => 'Ayuda del día (sin DNI)',
    'gasto_operativo'   => 'Gasto operativo',
    'venta_producto'    => 'Venta de producto',
  ];
  $cargosLabels = [
    'administracion'   => 'Administración',
    'encargado_planta' => 'Encargado de planta',
    'recolector_ruta'  => 'Recolector en ruta',
    'control_tina'     => 'Control de tina / calidad',
    'contador_externo' => 'Contador externo',
  ];
@endphp

@section('contenido')
<div class="contenedor" style="padding-block: 1.5rem 3rem;">

  <!-- Encabezado de sesión -->
  <section class="panel-cabecera" aria-labelledby="titulo-panel">
    <div>
      <p style="font-size: 0.875rem; font-weight: 600;" data-i18n="panel.subtitle">Centro de acopio y transformación</p>
      <h1 id="titulo-panel">{{ $plantaActiva->nombre }} · {{ $plantaActiva->ubicacion }}</h1>
      <p style="font-size: 0.9375rem;">
        <span data-i18n="panel.operator">Operador:</span> <strong>{{ $usuario->name }}</strong> ·
        <span data-i18n="panel.role">Cargo:</span> <span class="chip chip-celeste">{{ strtoupper(str_replace('_', ' ', $usuario->cargo)) }}</span>
      </p>

      @if($usuario->esAdmin() && $plantas->count() > 1)
        <form method="POST" action="{{ route('panel.cambiar_planta') }}" class="plant-switcher-form">
          @csrf
          <label for="select-planta-activa" style="font-size: 0.8125rem; font-weight: 600;" data-i18n="panel.switch_plant">Planta activa:</label>
          <select name="planta_id" id="select-planta-activa" class="plant-switcher-select" onchange="this.form.submit()">
            @foreach($plantas as $pl)
              <option value="{{ $pl->id }}" @selected($pl->id === $plantaActiva->id)>
                {{ $pl->codigo }} · {{ $pl->nombre }}
              </option>
            @endforeach
          </select>
        </form>
      @endif
    </div>

    <div class="panel-cabecera-acciones">
      <button type="button" id="btn-abrir-editar-datos" class="btn btn-outline" data-open-modal="modal-editar-datos">
        {!! $icoEditar !!} <span data-i18n="panel.btn_edit_data">Editar datos</span>
      </button>
      <a href="{{ route('panel.exportar') }}" class="btn btn-ember">
        {!! $icoDescarga !!} <span data-i18n="panel.btn_export_excel">Exportar Excel (.xlsx)</span>
      </a>
    </div>
  </section>

  <!-- KPIs -->
  <ul class="kpis" aria-label="Indicadores del día">
    <li class="kpi">
      <span class="kpi-etq" data-i18n="kpi.milk_today">Leche recibida hoy</span>
      <span class="kpi-numero">{{ number_format($litrosHoy, 1) }}<small>L</small></span>
      <span class="kpi-nota">{{ $acopiosHoy->count() }} entregas registradas</span>
    </li>
    <li class="kpi kpi-celeste">
      <span class="kpi-etq" data-i18n="kpi.price_liter">Precio vigente por litro</span>
      <span class="kpi-numero">S/ {{ number_format($precioLitro, 2) }}</span>
      <span class="kpi-nota">Comisión LAT: S/ 0.00</span>
    </li>
    <li class="kpi kpi-oceano">
      <span class="kpi-etq"><span data-i18n="kpi.vat_temp">Tina principal</span> ({{ $tinasPanel->count() }} activas)</span>
      <span class="kpi-numero">{{ number_format($loteActivo->temperatura_actual, 1) }}<small>°C</small></span>
      <span class="kpi-nota">Estado: {{ strtoupper($termica['estado']) }} ({{ $termica['porcentaje'] }}%)</span>
    </li>
    <li class="kpi">
      <span class="kpi-etq" data-i18n="kpi.sanitary">Control sanitario 74°</span>
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
      <button type="button" class="dropdown-item activo" role="option" aria-selected="true" data-tab="tab-acopio" data-titulo="1. Acopio por voz y tacto">
        <span class="dropdown-item-num">1</span>
        <div class="dropdown-item-info"><strong data-i18n="tab.acopio">1. Acopio por voz y tacto</strong><small>Recepción y pesaje diario</small></div>
      </button>
      <button type="button" class="dropdown-item" role="option" aria-selected="false" data-tab="tab-calidad" data-titulo="2. Calidad sanitaria">
        <span class="dropdown-item-num">2</span>
        <div class="dropdown-item-info"><strong data-i18n="tab.calidad">2. Calidad sanitaria</strong><small>Prueba alcohol 74° con confirmación</small></div>
      </button>
      <button type="button" class="dropdown-item" role="option" aria-selected="false" data-tab="tab-tina" data-titulo="3. Tinas y receta">
        <span class="dropdown-item-num">3</span>
        <div class="dropdown-item-info"><strong data-i18n="tab.tina">3. Tinas y receta</strong><small>Múltiples tinas y control térmico</small></div>
      </button>
      <button type="button" class="dropdown-item" role="option" aria-selected="false" data-tab="tab-quincena" data-titulo="4. Liquidación y ventas">
        <span class="dropdown-item-num">4</span>
        <div class="dropdown-item-info"><strong data-i18n="tab.quincena">4. Liquidación y ventas</strong><small>Cuentas claras y Excel (.xlsx)</small></div>
      </button>
      <button type="button" class="dropdown-item" role="option" aria-selected="false" data-tab="tab-estadisticas" data-titulo="5. Estadísticas">
        <span class="dropdown-item-num">5</span>
        <div class="dropdown-item-info"><strong data-i18n="tab.estadisticas">5. Estadísticas</strong><small>Ranking de plantas: quién vende más</small></div>
      </button>
      @if($usuario->esAdmin())
        <button type="button" class="dropdown-item" role="option" aria-selected="false" data-tab="tab-admin" data-titulo="6. Plantas y usuarios">
          <span class="dropdown-item-num">6</span>
          <div class="dropdown-item-info"><strong data-i18n="tab.admin">6. Plantas y usuarios</strong><small>Gestión de plantas y credenciales</small></div>
        </button>
      @endif
    </div>
  </div>

  <!-- Pestañas de módulos (Desktop > 768px) -->
  <div class="tabs-bar tabs-bar-desktop" role="tablist" aria-label="Módulos operativos de la planta" id="tablist">
    <button type="button" role="tab" id="tab-btn-acopio" class="tab" aria-selected="true" aria-controls="tab-acopio" tabindex="0" data-i18n="tab.acopio">1. Acopio por voz y tacto</button>
    <button type="button" role="tab" id="tab-btn-calidad" class="tab" aria-selected="false" aria-controls="tab-calidad" tabindex="-1" data-i18n="tab.calidad">2. Calidad sanitaria</button>
    <button type="button" role="tab" id="tab-btn-tina" class="tab" aria-selected="false" aria-controls="tab-tina" tabindex="-1" data-i18n="tab.tina">3. Tinas y receta</button>
    <button type="button" role="tab" id="tab-btn-quincena" class="tab" aria-selected="false" aria-controls="tab-quincena" tabindex="-1" data-i18n="tab.quincena">4. Liquidación y ventas</button>
    <button type="button" role="tab" id="tab-btn-estadisticas" class="tab" aria-selected="false" aria-controls="tab-estadisticas" tabindex="-1" data-i18n="tab.estadisticas">5. Estadísticas</button>
    @if($usuario->esAdmin())
      <button type="button" role="tab" id="tab-btn-admin" class="tab" aria-selected="false" aria-controls="tab-admin" tabindex="-1" data-i18n="tab.admin">6. Plantas y usuarios</button>
    @endif
  </div>

  <!-- ====================================================================== -->
  <!-- MÓDULO 1: ACOPIO                                                       -->
  <!-- ====================================================================== -->
  <section id="tab-acopio" class="tab-panel" role="tabpanel" aria-labelledby="tab-btn-acopio" tabindex="0">
    <div class="panel-grid-2">
      <div class="panel-card on-fog">
        <h2 data-i18n="m1.title">Registro diario de recepción</h2>
        <p data-i18n="m1.sub">Dicte por voz con confirmación hablada o ingrese la cantidad de forma manual.</p>

        <button type="button" id="btn-voz" class="btn btn-ember btn-grande btn-bloque btn-voz" aria-pressed="false">
          {!! $icoMic !!}
          <span id="txt-btn-voz" data-i18n="m1.voice_btn">Dictar acopio por voz</span>
        </button>
        <div id="transcripcion-voz" class="transcripcion" role="status" aria-live="polite">Ejemplo: “Don Eloy Condori, 35 litros”</div>

        <form method="POST" action="{{ route('panel.acopio') }}" id="form-acopio">
          @csrf
          <input type="hidden" name="metodo" id="input-metodo" value="manual">
          <input type="hidden" name="frase_dictada" id="input-frase" value="">

          <div class="form-grupo">
            <label for="select-proveedor" class="form-label" data-i18n="m1.producer">Ganadero / productor</label>
            <select name="proveedor_id" id="select-proveedor" class="select-lat" required>
              <option value="">Seleccione el ganadero…</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->codigo }} - {{ $prov->nombre }} ({{ $prov->sector }})</option>
              @endforeach
            </select>
          </div>

          <div class="form-grupo">
            <label for="input-litros" class="form-label" data-i18n="m1.liters">Volumen en litros (L)</label>
            <input type="number" step="0.1" min="0.5" max="5000" inputmode="decimal" name="litros" id="input-litros" class="input-lat input-litros" required placeholder="0.0">
          </div>

          <div class="form-grupo" role="group" aria-labelledby="botonera-etq">
            <span class="botonera-etq" id="botonera-etq" data-i18n="m1.touch_pad">Botonera táctil rápida (suma litros)</span>
            <div class="botonera">
              @foreach([10, 15, 20, 25, 30, 35, 40, 50] as $lit)
                <button type="button" data-litros="{{ $lit }}" aria-label="Sumar {{ $lit }} litros">+{{ $lit }}</button>
              @endforeach
            </div>
          </div>

          <button type="submit" class="btn btn-ocean btn-bloque btn-grande" data-i18n="m1.save_btn">Guardar entrega en tina</button>
        </form>
      </div>

      <div class="panel-tabla-wrap">
        <div class="panel-tabla-cab">
          <h3><span data-i18n="m1.today_list">Entregas de hoy</span> ({{ Carbon\Carbon::today()->format('d/m/Y') }})</h3>
          <span class="chip chip-celeste num-tabular">Total: {{ number_format($litrosHoy, 1) }} L</span>
        </div>
        <div class="tabla-region" role="region" aria-label="Entregas de leche de hoy" tabindex="0">
          <table class="tabla-plana">
            <thead>
              <tr>
                <th scope="col" data-i18n="m1.col_time">Hora</th>
                <th scope="col" data-i18n="m1.col_producer">Productor</th>
                <th scope="col" class="num" data-i18n="m1.col_liters">Litros</th>
                <th scope="col" data-i18n="m1.col_method">Método</th>
                <th scope="col" data-i18n="m1.col_quality">Calidad</th>
                <th scope="col" data-i18n="m1.col_actions">Acciones</th>
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
                  <td>
                    <button type="button" class="btn-micro js-edit-acopio"
                      data-id="{{ $acopio->id }}"
                      data-proveedor="{{ $acopio->proveedor_id }}"
                      data-litros="{{ $acopio->litros }}"
                      data-calidad="{{ $acopio->calidad }}"
                      data-metodo="{{ $acopio->metodo }}"
                      data-hora="{{ substr($acopio->hora, 0, 5) }}">
                      {!! $icoEditar !!} Editar
                    </button>
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-graphite);">No hay entregas registradas hoy.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <!-- ====================================================================== -->
  <!-- MÓDULO 2: CALIDAD SANITARIA (CON CONFIRMACIÓN JS)                      -->
  <!-- ====================================================================== -->
  <section id="tab-calidad" class="tab-panel" role="tabpanel" aria-labelledby="tab-btn-calidad" tabindex="0" hidden>
    <div class="panel-card">
      <h2 data-i18n="m2.title">Control sanitario en recepción (prueba de alcohol a 74°)</h2>
      <p data-i18n="m2.sub"><strong>Principio de cero desperdicio:</strong> la leche con acidez no se desecha ni se multa al productor. Se clasifica de inmediato y se deriva a quesillos frescos de 4 días.</p>

      <div class="tabla-region" role="region" aria-label="Clasificación sanitaria de las entregas de hoy" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th scope="col" data-i18n="m1.col_producer">Productor</th>
              <th scope="col" class="num" data-i18n="m1.col_liters">Litros</th>
              <th scope="col" data-i18n="m2.col_status">Estado actual</th>
              <th scope="col" data-i18n="m2.col_dest">Destino operativo</th>
              <th scope="col" data-i18n="m2.col_triage">Triage inmediato</th>
            </tr>
          </thead>
          <tbody>
            @forelse($acopiosHoy as $acopio)
              <tr>
                <td><strong>{{ $acopio->proveedor->nombre }}</strong><small>{{ $acopio->proveedor->codigo }}</small></td>
                <td class="num" style="font-size: 1.1rem;">{{ number_format($acopio->litros, 1) }} L</td>
                <td>
                  @if($acopio->calidad === 'buena')
                    <span class="chip chip-verde" data-i18n="m2.status_good">{!! $icoOk !!} Óptima</span>
                  @elseif($acopio->calidad === 'acida')
                    <span class="chip chip-amarillo" data-i18n="m2.status_acid">{!! $icoAlerta !!} Con acidez</span>
                  @else
                    <span class="chip chip-amarillo" data-i18n="m2.status_pending">Sin probar</span>
                  @endif
                </td>
                <td>
                  @if($acopio->destino === 'quesillo_4_dias')
                    <span data-i18n="m2.dest_quesillo">Tina quesillo 4 días</span>
                  @else
                    <span data-i18n="m2.dest_main">Tina principal (queso / yogur)</span>
                  @endif
                </td>
                <td>
                  <div class="acciones-celda">
                    <form method="POST" action="{{ route('panel.calidad', $acopio->id) }}"
                      class="js-confirm-form"
                      data-confirm-kind="calidad"
                      data-nombre="{{ $acopio->proveedor->nombre }}"
                      data-litros="{{ number_format($acopio->litros, 1) }}"
                      data-nuevo-estado="Aprobada (Tina principal)">
                      @csrf
                      <input type="hidden" name="calidad" value="buena">
                      <button type="submit" class="btn btn-verde btn-compacto" aria-label="Aprobar leche de {{ $acopio->proveedor->nombre }}">
                        {!! $icoOk !!} <span data-i18n="m2.approve">Aprobar</span>
                      </button>
                    </form>
                    <form method="POST" action="{{ route('panel.calidad', $acopio->id) }}"
                      class="js-confirm-form"
                      data-confirm-kind="calidad"
                      data-nombre="{{ $acopio->proveedor->nombre }}"
                      data-litros="{{ number_format($acopio->litros, 1) }}"
                      data-nuevo-estado="Ácida (Desviar a quesillo 4 días)">
                      @csrf
                      <input type="hidden" name="calidad" value="acida">
                      <button type="submit" class="btn btn-ember btn-compacto" aria-label="Desviar a quesillo la leche de {{ $acopio->proveedor->nombre }}">
                        {!! $icoAlerta !!} <span data-i18n="m2.divert">Desviar a quesillo</span>
                      </button>
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

  <!-- ====================================================================== -->
  <!-- MÓDULO 3: MÚLTIPLES TINAS Y RECETA                                     -->
  <!-- ====================================================================== -->
  <section id="tab-tina" class="tab-panel" role="tabpanel" aria-labelledby="tab-btn-tina" tabindex="0" hidden>
    <div class="panel-card" style="margin-bottom: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
          <h2 data-i18n="m3.title">Control de múltiples tinas y dosificación</h2>
          <p style="margin: 0;" data-i18n="m3.sub">Agregue, edite y supervise la curva térmica y receta automática de cada tina de la planta.</p>
        </div>
        <button type="button" class="btn btn-ember" id="btn-agregar-tina" data-open-modal="modal-nueva-tina" data-i18n="m3.add_tina">
          + Agregar nueva tina
        </button>
      </div>
    </div>

    <div class="tinas-grid">
      @foreach($tinasPanel as $item)
        @php
          $tina = $item->tina;
          $lote = $item->lote;
          $tTerm = $item->termica;
          $tRec = $item->receta;
        @endphp
        <article class="tina-card">
          <div class="tina-card-header">
            <div>
              <span class="chip chip-celeste">{{ $tina->codigo }} · {{ $tiposTinaLabels[$tina->tipo] ?? $tina->tipo }}</span>
              <h3 style="margin-top: 0.45rem; font-size: 1.15rem;">{{ $tina->nombre }}</h3>
              <p style="font-size: 0.85rem; color: var(--color-graphite); margin: 0;">
                <span data-i18n="m3.capacity">Capacidad:</span> <strong>{{ number_format($tina->capacidad_litros, 0) }} L</strong> ·
                Estado: <strong>{{ $tina->activa ? 'Activa' : 'Inactiva' }}</strong>
              </p>
            </div>
            <button type="button" class="btn-micro js-edit-tina"
              data-id="{{ $tina->id }}"
              data-codigo="{{ $tina->codigo }}"
              data-nombre="{{ $tina->nombre }}"
              data-capacidad="{{ $tina->capacidad_litros }}"
              data-tipo="{{ $tina->tipo }}"
              data-activa="{{ $tina->activa ? '1' : '0' }}">
              {!! $icoEditar !!} <span data-i18n="m3.edit_tina">Editar tina</span>
            </button>
          </div>

          @if($lote)
            <div style="background: var(--color-ocean); color: var(--color-white); padding: 1.15rem; border: 1px dashed rgba(255,255,255,0.22);">
              <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <small style="color: #bae6fd; font-weight: 600;">Lote {{ $lote->codigo }}</small>
                <span class="chip {{ $tTerm['apta'] ? 'chip-verde' : 'chip-amarillo' }}">{{ strtoupper($tTerm['estado']) }} · {{ $tTerm['porcentaje'] }}%</span>
              </div>
              <div style="font-size: 2.3rem; font-weight: 300; font-variant-numeric: tabular-nums; margin: 0.4rem 0;">
                {{ number_format($lote->temperatura_actual, 1) }} °C
                <small style="font-size: 0.95rem; color: #bae6fd;">({{ number_format($lote->litros, 1) }} L)</small>
              </div>
              <div class="barra-termica" style="margin: 0.5rem 0 0;">
                <div style="width: {{ min(100, max(0, $tTerm['porcentaje'])) }}%;"></div>
              </div>
            </div>

            <div class="tina-receta-mini">
              <div><span>Cuajo líquido</span><strong>{{ $tRec['cuajo_ml'] }} ml</strong></div>
              <div><span>Sal común</span><strong>{{ $tRec['sal_kg'] }} kg</strong></div>
              <div><span>Cultivo</span><strong>{{ $tRec['cultivo_g'] }} g</strong></div>
              <div><span>Moldes est.</span><strong>{{ $tRec['moldes_estimados'] }} und</strong></div>
            </div>

            <form method="POST" action="{{ route('panel.tina', $lote->id) }}" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 0.5rem; align-items: end;">
              @csrf
              <div>
                <label class="form-label" style="font-size: 0.78rem;" for="temp-{{ $lote->id }}">Sensor (°C)</label>
                <input type="number" step="0.5" id="temp-{{ $lote->id }}" name="temperatura" class="input-lat" value="{{ $lote->temperatura_actual }}" style="min-height: 42px; padding: 0.45rem 0.65rem;">
              </div>
              <div>
                <label class="form-label" style="font-size: 0.78rem;" for="lit-{{ $lote->id }}">Litros (L)</label>
                <input type="number" step="0.5" min="5" id="lit-{{ $lote->id }}" name="litros" class="input-lat" value="{{ $lote->litros }}" style="min-height: 42px; padding: 0.45rem 0.65rem;">
              </div>
              <button type="submit" class="btn btn-ocean btn-compacto" data-i18n="m3.update_sensor">Actualizar</button>
            </form>

            <form method="POST" action="{{ route('panel.tina', $lote->id) }}"
              class="js-confirm-form"
              data-confirm-kind="finish_batch"
              data-codigo="{{ $lote->codigo }}"
              style="display: flex; gap: 0.5rem; align-items: end; padding-top: 0.75rem; border-top: 1px dashed var(--color-panel-dashed);">
              @csrf
              <div style="flex: 1;">
                <label class="form-label" style="font-size: 0.78rem;" for="moldes-{{ $lote->id }}">Moldes obtenidos (para cerrar lote)</label>
                <input type="number" min="0" id="moldes-{{ $lote->id }}" name="moldes" class="input-lat" value="{{ $tRec['moldes_estimados'] }}" style="min-height: 42px; padding: 0.45rem 0.65rem;">
              </div>
              <button type="submit" class="btn btn-verde btn-compacto" data-i18n="m3.finish_batch">Finalizar lote</button>
            </form>
          @else
            <div style="padding: 1rem; background: var(--color-fog); border: 1px dashed var(--color-panel-dashed);">
              <p style="margin: 0; font-weight: 600; color: var(--color-graphite);" data-i18n="m3.no_batch">Tina libre (sin lote en proceso)</p>
            </div>
            @if($tina->activa)
              <form method="POST" action="{{ route('panel.tinas.lote', $tina->id) }}" style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; align-items: end;">
                @csrf
                <div>
                  <label class="form-label" style="font-size: 0.78rem;" for="start-lit-{{ $tina->id }}">Litros a cargar (máx {{ (int) $tina->capacidad_litros }})</label>
                  <input type="number" step="0.5" min="5" max="{{ $tina->capacidad_litros }}" id="start-lit-{{ $tina->id }}" name="litros" class="input-lat" value="{{ min($tina->capacidad_litros, max(50, $litrosHoy ?: 100)) }}" required style="min-height: 42px;">
                </div>
                <div>
                  <label class="form-label" style="font-size: 0.78rem;" for="start-temp-{{ $tina->id }}">Temp. inicial (°C)</label>
                  <input type="number" step="0.5" min="4" max="95" id="start-temp-{{ $tina->id }}" name="temperatura_actual" class="input-lat" value="72.0" required style="min-height: 42px;">
                </div>
                <button type="submit" class="btn btn-ember btn-bloque" style="grid-column: 1 / -1;" data-i18n="m3.start_batch">Iniciar lote en tina</button>
              </form>
            @endif
          @endif
        </article>
      @endforeach
    </div>
  </section>

  <!-- ====================================================================== -->
  <!-- MÓDULO 4: LIQUIDACIÓN QUINCENAL, VENTAS Y EXCEL                        -->
  <!-- ====================================================================== -->
  <section id="tab-quincena" class="tab-panel" role="tabpanel" aria-labelledby="tab-btn-quincena" tabindex="0" hidden>
    <div class="panel-card" style="margin-bottom: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
        <div>
          <h2><span data-i18n="m4.title">Liquidación quincenal de proveedores</span> ({{ $desde }} al {{ $hasta }})</h2>
          <p style="margin: 0;" data-i18n="m4.sub">Cálculo neto transparente: bruto menos adelantos en efectivo o queso. <strong>Comisión: S/ 0.00.</strong></p>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.65rem;">
          <button type="button" class="btn btn-outline" data-open-modal="modal-editar-datos">
            {!! $icoEditar !!} <span data-i18n="panel.btn_edit_data">Editar datos</span>
          </button>
          <a href="{{ route('panel.exportar') }}" class="btn btn-ember">
            {!! $icoDescarga !!} <span data-i18n="panel.btn_download_excel">Descargar balance en Excel (.xlsx)</span>
          </a>
        </div>
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
        <h3 style="font-size: 1.1rem;" data-i18n="m4.mov_title">Registrar movimiento, venta o ayuda del día</h3>
        <p class="legal">Ley N.° 29733: los pagos en mano por faenas de lavado o volteo no requieren DNI ni datos personales sensibles. Las ventas alimentan las estadísticas de plantas.</p>

        <form method="POST" action="{{ route('panel.movimiento') }}" class="movimiento-form">
          @csrf
          <div class="form-grupo">
            <label for="mov-tipo" class="form-label">Tipo</label>
            <select name="tipo" id="mov-tipo" class="select-lat">
              <option value="venta_producto">Venta de producto lácteo (queso / yogur)</option>
              <option value="adelanto_efectivo">Adelanto en efectivo</option>
              <option value="adelanto_queso">Adelanto en moldes de queso</option>
              <option value="ayuda_del_dia">Pago de ayuda del día (sin DNI)</option>
              <option value="gasto_operativo">Gasto operativo / insumos</option>
            </select>
          </div>
          <div class="form-grupo">
            <label for="mov-proveedor" class="form-label">Productor (si aplica)</label>
            <select name="proveedor_id" id="mov-proveedor" class="select-lat">
              <option value="">Ninguno / venta o faena externa</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="mov-concepto" class="form-label">Concepto</label>
            <input type="text" name="concepto" id="mov-concepto" class="input-lat" required maxlength="160" placeholder="Ej: Venta 25 moldes feria Cusco">
          </div>
          <div class="form-grupo">
            <label for="mov-unidades" class="form-label">Moldes / Und.</label>
            <input type="number" min="0" name="cantidad_unidades" id="mov-unidades" class="input-lat" value="0">
          </div>
          <div class="form-grupo">
            <label for="mov-monto" class="form-label">Monto (S/)</label>
            <input type="number" step="0.5" min="0.5" inputmode="decimal" name="monto" id="mov-monto" class="input-lat" required placeholder="0.00">
          </div>
          <button type="submit" class="btn btn-ocean" data-i18n="m4.mov_btn">Asentar movimiento</button>
        </form>
      </div>
    </div>

    <div class="panel-tabla-wrap">
      <div class="panel-tabla-cab">
        <h3 data-i18n="m4.recent_title">Movimientos y ventas recientes</h3>
      </div>
      <div class="tabla-region" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Tipo</th>
              <th>Referencia</th>
              <th>Concepto</th>
              <th class="num">Und.</th>
              <th class="num">Monto</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($movimientos as $mov)
              <tr>
                <td class="num-tabular">{{ $mov->fecha?->format('d/m/Y') }}</td>
                <td><span class="chip {{ $mov->tipo === 'venta_producto' ? 'chip-verde' : 'chip-fog' }}">{{ $tiposMovLabels[$mov->tipo] ?? $mov->tipo }}</span></td>
                <td>{{ $mov->proveedor?->nombre ?? 'Planta / externo' }}</td>
                <td>{{ $mov->concepto }}</td>
                <td class="num">{{ $mov->cantidad_unidades ?? 0 }}</td>
                <td class="num"><strong>S/ {{ number_format($mov->monto, 2) }}</strong></td>
                <td>
                  <div style="display: flex; gap: 0.35rem;">
                    <button type="button" class="btn-micro js-edit-mov"
                      data-id="{{ $mov->id }}"
                      data-tipo="{{ $mov->tipo }}"
                      data-proveedor="{{ $mov->proveedor_id ?? '' }}"
                      data-concepto="{{ $mov->concepto }}"
                      data-unidades="{{ $mov->cantidad_unidades ?? 0 }}"
                      data-monto="{{ $mov->monto }}">
                      {!! $icoEditar !!} Editar
                    </button>
                    <form method="POST" action="{{ route('panel.movimiento.delete', $mov->id) }}"
                      class="js-confirm-form"
                      data-confirm-kind="delete"
                      data-detalle="{{ $mov->concepto }}">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-micro btn-micro-danger">Eliminar</button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" style="text-align: center; padding: 1.5rem;">No hay movimientos registrados.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- ====================================================================== -->
  <!-- MÓDULO 5: ESTADÍSTICAS Y RANKING DE PLANTAS ("¿QUIÉN VENDE MÁS?")     -->
  <!-- ====================================================================== -->
  <section id="tab-estadisticas" class="tab-panel" role="tabpanel" aria-labelledby="tab-btn-estadisticas" tabindex="0" hidden>
    <div class="panel-card" style="margin-bottom: 1.5rem;">
      <h2 data-i18n="m5.title">Estadísticas de plantas y ventas</h2>
      <p style="margin-bottom: 1.25rem;" data-i18n="m5.sub">Comparativa de desempeño: qué planta vende más, producción de moldes y ranking de proveedores.</p>

      @php
        $lider = $estadisticas['planta_lider'];
        $maxVentas = max(1, (float) ($estadisticas['ranking_plantas']->max('ventas_monto') ?? 1));
      @endphp

      <ul class="kpis" style="margin-bottom: 1.5rem;">
        <li class="kpi kpi-oceano">
          <span class="kpi-etq">Planta que vende más</span>
          <span class="kpi-numero" style="font-size: 1.45rem; font-weight: 700;">{{ $lider?->nombre ?? 'Sin ventas' }}</span>
          <span class="kpi-nota">Ventas: S/ {{ number_format($lider?->ventas_monto ?? 0, 2) }} ({{ $lider?->participacion_pct ?? 0 }}%)</span>
        </li>
        <li class="kpi">
          <span class="kpi-etq">Ventas de {{ $plantaActiva->codigo }} (período)</span>
          <span class="kpi-numero">S/ {{ number_format($estadisticas['totales_planta']['ventas_monto'], 2) }}</span>
          <span class="kpi-nota">{{ $estadisticas['totales_planta']['ventas_unidades'] }} unidades vendidas</span>
        </li>
        <li class="kpi kpi-celeste">
          <span class="kpi-etq">Litros acopiados (período)</span>
          <span class="kpi-numero">{{ number_format($estadisticas['totales_planta']['litros_periodo'], 1) }}<small>L</small></span>
          <span class="kpi-nota">{{ $estadisticas['totales_planta']['moldes_producidos'] }} moldes producidos</span>
        </li>
        <li class="kpi">
          <span class="kpi-etq">Calidad aprobada vs ácida</span>
          <span class="kpi-numero">{{ number_format($estadisticas['calidad']['buena_pct'], 0) }}<small>%</small></span>
          <span class="kpi-nota">{{ number_format($estadisticas['calidad']['acida_litros'], 1) }} L desviados a quesillo</span>
        </li>
      </ul>

      <h3 data-i18n="m5.who_sells_most">Ranking de plantas: ¿Quién vende más?</h3>
      <div class="stats-ranking-list">
        @foreach($estadisticas['ranking_plantas'] as $idx => $rp)
          @php $barPct = $maxVentas > 0 ? min(100, round(($rp->ventas_monto / $maxVentas) * 100, 1)) : 0; @endphp
          <div class="stats-bar-row {{ $idx === 0 ? 'is-leader' : '' }}">
            <div class="stats-bar-meta">
              <div>
                <strong>#{{ $idx + 1 }} · {{ $rp->nombre }}</strong>
                <span class="chip {{ $idx === 0 ? 'chip-verde' : 'chip-fog' }}">{{ $rp->ubicacion }}</span>
                @if($idx === 0)
                  <span class="chip chip-amarillo">Mayor vendedor</span>
                @endif
              </div>
              <div class="num-tabular" style="font-size: 0.95rem;">
                <strong>S/ {{ number_format($rp->ventas_monto, 2) }}</strong>
                <small style="color: var(--color-graphite);">({{ $rp->participacion_pct }}% · {{ $rp->ventas_unidades }} und · {{ number_format($rp->litros_acopiados, 1) }} L · {{ $rp->moldes_producidos }} moldes)</small>
              </div>
            </div>
            <div class="stats-bar-track" role="img" aria-label="{{ $rp->nombre }}: S/ {{ number_format($rp->ventas_monto, 2) }}">
              <div class="stats-bar-fill" style="width: {{ $barPct }}%;"></div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="panel-grid-eq">
      <div class="panel-card">
        <h3 data-i18n="m5.top_producers">Productores con mayor entrega en esta planta</h3>
        <div class="tabla-region" tabindex="0" style="margin-top: 0.75rem;">
          <table class="tabla-plana" style="min-width: 420px;">
            <thead>
              <tr>
                <th>Productor</th>
                <th class="num">Entregas</th>
                <th class="num">Litros</th>
                <th class="num">%</th>
              </tr>
            </thead>
            <tbody>
              @forelse($estadisticas['top_productores'] as $tp)
                <tr>
                  <td><strong>{{ $tp->nombre }}</strong><small>{{ $tp->sector }}</small></td>
                  <td class="num">{{ $tp->entregas }}</td>
                  <td class="num"><strong>{{ number_format($tp->litros, 1) }} L</strong></td>
                  <td class="num">{{ $tp->porcentaje }}%</td>
                </tr>
              @empty
                <tr><td colspan="4" style="text-align: center; padding: 1.5rem;">Sin entregas en el período.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="panel-card">
        <h3 data-i18n="m5.quality_split">Calidad sanitaria y método de captura</h3>
        <div style="display: grid; gap: 0.9rem; margin-top: 1rem;">
          <div style="padding: 1rem; background: var(--verde-fill); border: 1px dashed rgba(21,128,61,0.3);">
            <strong>Leche óptima (Tina principal):</strong>
            <span class="num-tabular">{{ number_format($estadisticas['calidad']['buena_litros'], 1) }} L ({{ $estadisticas['calidad']['buena_pct'] }}%)</span>
          </div>
          <div style="padding: 1rem; background: var(--amarillo-fill); border: 1px dashed rgba(180,83,9,0.3);">
            <strong>Leche con acidez (Quesillo 4 días):</strong>
            <span class="num-tabular">{{ number_format($estadisticas['calidad']['acida_litros'], 1) }} L ({{ $estadisticas['calidad']['acida_pct'] }}%)</span>
          </div>
          <div style="padding: 1rem; background: var(--color-sky-wash); border: 1px dashed var(--color-panel-dashed);">
            <strong>Uso de dictado por voz vs manual:</strong>
            <span class="num-tabular">{{ $estadisticas['metodos']['voz_count'] }} por voz ({{ $estadisticas['metodos']['voz_pct'] }}%) · {{ $estadisticas['metodos']['manual_count'] }} manual</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ====================================================================== -->
  <!-- MÓDULO 6: ADMINISTRADOR DE PLANTAS Y USUARIOS (SOLO ADMIN)             -->
  <!-- ====================================================================== -->
  @if($usuario->esAdmin())
  <section id="tab-admin" class="tab-panel" role="tabpanel" aria-labelledby="tab-btn-admin" tabindex="0" hidden>
    <!-- Administrador de Plantas -->
    <div class="panel-card" style="margin-bottom: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
        <div>
          <h2 data-i18n="m6.plants_title">Administrador de plantas lácteas</h2>
          <p style="margin: 0;">Cree, edite y configure el precio por litro de cada planta comunitaria.</p>
        </div>
        <button type="button" class="btn btn-ember" data-open-modal="modal-nueva-planta" data-i18n="m6.add_plant">+ Nueva planta</button>
      </div>

      <div class="tabla-region" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th>Código</th>
              <th>Nombre de la Planta</th>
              <th>Ubicación</th>
              <th class="num">Precio/L</th>
              <th class="num">Tinas</th>
              <th class="num">Usuarios</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($plantas as $pl)
              <tr>
                <td class="num-tabular"><strong>{{ $pl->codigo }}</strong></td>
                <td>{{ $pl->nombre }}</td>
                <td>{{ $pl->ubicacion }}</td>
                <td class="num">S/ {{ number_format($pl->precio_litro, 2) }}</td>
                <td class="num">{{ $pl->tinas_count ?? $pl->tinas()->count() }}</td>
                <td class="num">{{ $pl->users_count ?? $pl->users()->count() }}</td>
                <td>
                  <span class="chip {{ $pl->activa ? 'chip-verde' : 'chip-rojo' }}">{{ $pl->activa ? 'Activa' : 'Inactiva' }}</span>
                </td>
                <td>
                  <button type="button" class="btn-micro js-edit-planta"
                    data-id="{{ $pl->id }}"
                    data-codigo="{{ $pl->codigo }}"
                    data-nombre="{{ $pl->nombre }}"
                    data-ubicacion="{{ $pl->ubicacion }}"
                    data-precio="{{ $pl->precio_litro }}"
                    data-activa="{{ $pl->activa ? '1' : '0' }}">
                    {!! $icoEditar !!} Editar
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- Administrador de Usuarios y Credenciales -->
    <div class="panel-card">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
        <div>
          <h2 data-i18n="m6.users_title">Gestión de usuarios y credenciales</h2>
          <p style="margin: 0;">Cree credenciales de acceso para operarios de ruta, tina, calidad o contadores sin requerir correo personal.</p>
        </div>
        <button type="button" class="btn btn-ocean" data-open-modal="modal-nuevo-usuario" data-i18n="m6.add_user">+ Crear usuario / credenciales</button>
      </div>

      <div class="tabla-region" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Usuario</th>
              <th>Cargo / Rol</th>
              <th>Planta asignada</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($usuarios as $u)
              <tr>
                <td><strong>{{ $u->name }}</strong><small>{{ $u->email ?? 'Sin correo (acceso por usuario)' }}</small></td>
                <td class="num-tabular">{{ $u->usuario }}</td>
                <td><span class="chip chip-celeste">{{ $cargosLabels[$u->cargo] ?? $u->cargo }}</span></td>
                <td>{{ $u->planta?->nombre ?? 'Sin planta' }}</td>
                <td><span class="chip {{ $u->activo ? 'chip-verde' : 'chip-rojo' }}">{{ $u->activo ? 'Activo' : 'Inactivo' }}</span></td>
                <td>
                  <button type="button" class="btn-micro js-edit-usuario"
                    data-id="{{ $u->id }}"
                    data-name="{{ $u->name }}"
                    data-usuario="{{ $u->usuario }}"
                    data-email="{{ $u->email ?? '' }}"
                    data-cargo="{{ $u->cargo }}"
                    data-planta="{{ $u->planta_id ?? $plantaActiva->id }}"
                    data-activo="{{ $u->activo ? '1' : '0' }}">
                    {!! $icoEditar !!} Editar
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </section>
  @endif

</div>

<!-- ======================================================================== -->
<!-- MODAL GLOBAL DE CONFIRMACIÓN JS ("¿ESTÁ SEGURO DE QUE QUIERES CAMBIAR?")  -->
<!-- ======================================================================== -->
<div class="modal-overlay" id="modal-confirmacion-lat" hidden role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="confirm-modal-title" data-i18n="confirm.title">Confirmar cambio</h2>
      <button type="button" class="modal-close" data-close-modal="modal-confirmacion-lat" aria-label="Cerrar">&times;</button>
    </div>
    <div class="modal-body">
      <p id="confirm-modal-message" style="font-size: 1rem; color: var(--color-ink); margin: 0;"></p>
    </div>
    <div class="modal-foot">
      <button type="button" class="btn btn-outline" data-close-modal="modal-confirmacion-lat" data-i18n="confirm.cancel">Cancelar</button>
      <button type="button" class="btn btn-ember" id="btn-confirmar-accion-js" data-i18n="confirm.accept">Sí, confirmar cambio</button>
    </div>
  </div>
</div>

<!-- ======================================================================== -->
<!-- MODAL: EDITAR DATOS ("EN CASO DE HABERSE CONFUNDIDO")                     -->
<!-- ======================================================================== -->
<div class="modal-overlay" id="modal-editar-datos" hidden role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
  <div class="modal-swiss">
    <div class="modal-head">
      <h2 id="edit-modal-title" data-i18n="edit_modal.title">Editar datos registrados (Corrección rápida)</h2>
      <button type="button" class="modal-close" data-close-modal="modal-editar-datos" aria-label="Cerrar">&times;</button>
    </div>
    <div class="modal-body">
      <p style="margin-bottom: 1rem; color: var(--color-graphite);" data-i18n="edit_modal.sub">Corrija aquí entregas, movimientos o ganaderos si ocurrió un error al registrar.</p>

      <div class="subtabs-edit" role="tablist">
        <button type="button" class="subtab-btn activo" data-subtab="subtab-acopios">Entregas de hoy ({{ $acopiosHoy->count() }})</button>
        <button type="button" class="subtab-btn" data-subtab="subtab-movimientos">Movimientos y ventas ({{ $movimientos->count() }})</button>
        <button type="button" class="subtab-btn" data-subtab="subtab-ganaderos">Ganaderos ({{ $proveedores->count() }})</button>
      </div>

      <!-- Subtab Acopios -->
      <div id="subtab-acopios" class="subtab-panel">
        <div class="tabla-region">
          <table class="tabla-plana" style="min-width: 480px;">
            <thead><tr><th>Hora</th><th>Productor</th><th class="num">Litros</th><th>Calidad</th><th>Corregir</th></tr></thead>
            <tbody>
              @forelse($acopiosHoy as $a)
                <tr>
                  <td class="num-tabular">{{ substr($a->hora, 0, 5) }}</td>
                  <td>{{ $a->proveedor->nombre }}</td>
                  <td class="num"><strong>{{ number_format($a->litros, 1) }} L</strong></td>
                  <td>{{ $a->calidad }}</td>
                  <td style="display: flex; gap: 0.35rem;">
                    <button type="button" class="btn-micro js-edit-acopio"
                      data-id="{{ $a->id }}"
                      data-proveedor="{{ $a->proveedor_id }}"
                      data-litros="{{ $a->litros }}"
                      data-calidad="{{ $a->calidad }}"
                      data-metodo="{{ $a->metodo }}"
                      data-hora="{{ substr($a->hora, 0, 5) }}">Editar</button>
                    <form method="POST" action="{{ route('panel.acopio.delete', $a->id) }}" class="js-confirm-form" data-confirm-kind="delete" data-detalle="acopio de {{ $a->proveedor->nombre }}">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-micro btn-micro-danger">Borrar</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" style="text-align: center;">No hay acopios hoy.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- Subtab Movimientos -->
      <div id="subtab-movimientos" class="subtab-panel" hidden>
        <div class="tabla-region">
          <table class="tabla-plana" style="min-width: 480px;">
            <thead><tr><th>Tipo</th><th>Concepto</th><th class="num">Monto</th><th>Corregir</th></tr></thead>
            <tbody>
              @forelse($movimientos as $m)
                <tr>
                  <td>{{ $tiposMovLabels[$m->tipo] ?? $m->tipo }}</td>
                  <td>{{ $m->concepto }}</td>
                  <td class="num">S/ {{ number_format($m->monto, 2) }}</td>
                  <td>
                    <button type="button" class="btn-micro js-edit-mov"
                      data-id="{{ $m->id }}"
                      data-tipo="{{ $m->tipo }}"
                      data-proveedor="{{ $m->proveedor_id ?? '' }}"
                      data-concepto="{{ $m->concepto }}"
                      data-unidades="{{ $m->cantidad_unidades ?? 0 }}"
                      data-monto="{{ $m->monto }}">Editar</button>
                  </td>
                </tr>
              @empty
                <tr><td colspan="4" style="text-align: center;">Sin movimientos.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- Subtab Ganaderos -->
      <div id="subtab-ganaderos" class="subtab-panel" hidden>
        <form method="POST" action="{{ route('panel.proveedor.store') }}" style="display: grid; grid-template-columns: 0.8fr 1.4fr 1.2fr auto; gap: 0.5rem; margin-bottom: 1rem; align-items: end;">
          @csrf
          <div>
            <label class="form-label" style="font-size: 0.78rem;">Código</label>
            <input type="text" name="codigo" class="input-lat" required placeholder="PRV-06" style="min-height: 40px;">
          </div>
          <div>
            <label class="form-label" style="font-size: 0.78rem;">Nombre del ganadero</label>
            <input type="text" name="nombre" class="input-lat" required placeholder="Sra. Elena Quispe" style="min-height: 40px;">
          </div>
          <div>
            <label class="form-label" style="font-size: 0.78rem;">Sector</label>
            <input type="text" name="sector" class="input-lat" placeholder="Markjo" style="min-height: 40px;">
          </div>
          <button type="submit" class="btn btn-ocean btn-compacto">+ Agregar</button>
        </form>
        <div class="tabla-region">
          <table class="tabla-plana" style="min-width: 440px;">
            <thead><tr><th>Código</th><th>Nombre</th><th>Sector</th></tr></thead>
            <tbody>
              @foreach($proveedores as $pr)
                <tr><td>{{ $pr->codigo }}</td><td><strong>{{ $pr->nombre }}</strong></td><td>{{ $pr->sector }}</td></tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ======================================================================== -->
<!-- MODAL: EDITAR UN ACOPIO ESPECÍFICO                                       -->
<!-- ======================================================================== -->
<div class="modal-overlay" id="modal-edit-acopio" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-acopio">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-edit-acopio">Corregir entrega de leche</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-acopio" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" id="form-edit-acopio" action="">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="form-grupo">
          <label class="form-label" for="edit-ac-prov">Productor</label>
          <select name="proveedor_id" id="edit-ac-prov" class="select-lat" required>
            @foreach($proveedores as $prov)
              <option value="{{ $prov->id }}">{{ $prov->codigo }} - {{ $prov->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-litros">Litros (L)</label>
            <input type="number" step="0.1" min="0.5" max="5000" name="litros" id="edit-ac-litros" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-hora">Hora</label>
            <input type="time" name="hora" id="edit-ac-hora" class="input-lat">
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-calidad">Calidad</label>
            <select name="calidad" id="edit-ac-calidad" class="select-lat">
              <option value="buena">Aprobada (Tina principal)</option>
              <option value="acida">Ácida (Quesillo 4 días)</option>
              <option value="pendiente">Pendiente</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-metodo">Método</label>
            <select name="metodo" id="edit-ac-metodo" class="select-lat">
              <option value="manual">Manual</option>
              <option value="voz">Voz</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-edit-acopio">Cancelar</button>
        <button type="submit" class="btn btn-ember">Guardar corrección</button>
      </div>
    </form>
  </div>
</div>

<!-- ======================================================================== -->
<!-- MODAL: EDITAR MOVIMIENTO FINANCIERO                                      -->
<!-- ======================================================================== -->
<div class="modal-overlay" id="modal-edit-mov" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-mov">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-edit-mov">Corregir movimiento / venta</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-mov" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" id="form-edit-mov" action="">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="form-grupo">
          <label class="form-label" for="edit-mov-tipo">Tipo</label>
          <select name="tipo" id="edit-mov-tipo" class="select-lat">
            <option value="venta_producto">Venta de producto lácteo</option>
            <option value="adelanto_efectivo">Adelanto en efectivo</option>
            <option value="adelanto_queso">Adelanto en moldes de queso</option>
            <option value="ayuda_del_dia">Ayuda del día (sin DNI)</option>
            <option value="gasto_operativo">Gasto operativo</option>
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="edit-mov-prov">Productor (si aplica)</label>
          <select name="proveedor_id" id="edit-mov-prov" class="select-lat">
            <option value="">Ninguno / planta</option>
            @foreach($proveedores as $prov)
              <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="edit-mov-concepto">Concepto</label>
          <input type="text" name="concepto" id="edit-mov-concepto" class="input-lat" required maxlength="160">
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="edit-mov-unidades">Unidades / Moldes</label>
            <input type="number" min="0" name="cantidad_unidades" id="edit-mov-unidades" class="input-lat">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-mov-monto">Monto (S/)</label>
            <input type="number" step="0.5" min="0.5" name="monto" id="edit-mov-monto" class="input-lat" required>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-edit-mov">Cancelar</button>
        <button type="submit" class="btn btn-ember">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<!-- ======================================================================== -->
<!-- MODALES DE MÚLTIPLES TINAS (AGREGAR / EDITAR)                            -->
<!-- ======================================================================== -->
<div class="modal-overlay" id="modal-nueva-tina" hidden role="dialog" aria-modal="true" aria-labelledby="title-nueva-tina">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-nueva-tina">Agregar nueva tina</h2>
      <button type="button" class="modal-close" data-close-modal="modal-nueva-tina" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" action="{{ route('panel.tinas.store') }}">
      @csrf
      <div class="modal-body">
        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="nt-codigo">Código</label>
            <input type="text" name="codigo" id="nt-codigo" class="input-lat" required placeholder="TINA-04">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nt-nombre">Nombre de la tina</label>
            <input type="text" name="nombre" id="nt-nombre" class="input-lat" required placeholder="Tina Queso Fresco">
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="nt-cap">Capacidad (L)</label>
            <input type="number" step="10" min="20" max="10000" name="capacidad_litros" id="nt-cap" class="input-lat" value="300" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nt-tipo">Tipo de uso</label>
            <select name="tipo" id="nt-tipo" class="select-lat">
              <option value="principal_queso">Queso principal</option>
              <option value="quesillo">Quesillo 4 días</option>
              <option value="yogur">Yogur</option>
              <option value="multiproposito">Multipropósito</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-nueva-tina">Cancelar</button>
        <button type="submit" class="btn btn-ember">Crear tina</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modal-edit-tina" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-tina">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-edit-tina">Editar tina</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-tina" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" id="form-edit-tina" action="">
      @csrf @method('PUT')
      <div class="modal-body">
        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="et-codigo">Código</label>
            <input type="text" name="codigo" id="et-codigo" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="et-nombre">Nombre</label>
            <input type="text" name="nombre" id="et-nombre" class="input-lat" required>
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="et-cap">Capacidad (L)</label>
            <input type="number" step="10" min="20" max="10000" name="capacidad_litros" id="et-cap" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="et-tipo">Tipo</label>
            <select name="tipo" id="et-tipo" class="select-lat">
              <option value="principal_queso">Queso principal</option>
              <option value="quesillo">Quesillo 4 días</option>
              <option value="yogur">Yogur</option>
              <option value="multiproposito">Multipropósito</option>
            </select>
          </div>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="et-activa">Estado operativo</label>
          <select name="activa" id="et-activa" class="select-lat">
            <option value="1">Activa</option>
            <option value="0">Inactiva / Mantenimiento</option>
          </select>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-edit-tina">Cancelar</button>
        <button type="submit" class="btn btn-ember">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<!-- ======================================================================== -->
<!-- MODALES DE ADMINISTRADOR: PLANTAS Y USUARIOS (SOLO ADMIN)                -->
<!-- ======================================================================== -->
@if($usuario->esAdmin())
<div class="modal-overlay" id="modal-nueva-planta" hidden role="dialog" aria-modal="true" aria-labelledby="title-nueva-planta">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-nueva-planta">Agregar nueva planta</h2>
      <button type="button" class="modal-close" data-close-modal="modal-nueva-planta" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.plantas.store') }}">
      @csrf
      <div class="modal-body">
        <div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="np-cod">Código</label>
            <input type="text" name="codigo" id="np-cod" class="input-lat" required placeholder="PLT-04">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="np-nom">Nombre de planta</label>
            <input type="text" name="nombre" id="np-nom" class="input-lat" required placeholder="Quesería Maras">
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="np-ubi">Ubicación</label>
            <input type="text" name="ubicacion" id="np-ubi" class="input-lat" required value="Cusco Provincia">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="np-pre">Precio / L (S/)</label>
            <input type="number" step="0.05" min="0.5" max="20" name="precio_litro" id="np-pre" class="input-lat" value="1.65" required>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-nueva-planta">Cancelar</button>
        <button type="submit" class="btn btn-ember">Crear planta</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modal-edit-planta" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-planta">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-edit-planta">Editar planta láctea</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-planta" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" id="form-edit-planta" action="">
      @csrf @method('PUT')
      <div class="modal-body">
        <div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="ep-cod">Código</label>
            <input type="text" name="codigo" id="ep-cod" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="ep-nom">Nombre</label>
            <input type="text" name="nombre" id="ep-nom" class="input-lat" required>
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="ep-ubi">Ubicación</label>
            <input type="text" name="ubicacion" id="ep-ubi" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="ep-pre">Precio / L (S/)</label>
            <input type="number" step="0.05" min="0.5" max="20" name="precio_litro" id="ep-pre" class="input-lat" required>
          </div>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="ep-act">Estado</label>
          <select name="activa" id="ep-act" class="select-lat">
            <option value="1">Activa</option>
            <option value="0">Inactiva</option>
          </select>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-edit-planta">Cancelar</button>
        <button type="submit" class="btn btn-ember">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modal-nuevo-usuario" hidden role="dialog" aria-modal="true" aria-labelledby="title-nuevo-usuario">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-nuevo-usuario">Crear credenciales de usuario</h2>
      <button type="button" class="modal-close" data-close-modal="modal-nuevo-usuario" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.usuarios.store') }}">
      @csrf
      <div class="modal-body">
        <div class="form-grupo">
          <label class="form-label" for="nu-name">Nombre completo</label>
          <input type="text" name="name" id="nu-name" class="input-lat" required placeholder="Ej: Juana Condori">
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="nu-user">Usuario de acceso</label>
            <input type="text" name="usuario" id="nu-user" class="input-lat" required placeholder="juana.ruta">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nu-email">Correo (opcional)</label>
            <input type="email" name="email" id="nu-email" class="input-lat" placeholder="opcional@planta.pe">
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="nu-cargo">Cargo / Rol</label>
            <select name="cargo" id="nu-cargo" class="select-lat">
              <option value="recolector_ruta">Recolector en ruta</option>
              <option value="control_tina">Control de tina / calidad</option>
              <option value="encargado_planta">Encargado de planta</option>
              <option value="contador_externo">Contador externo</option>
              <option value="administracion">Administración</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nu-planta">Planta asignada</label>
            <select name="planta_id" id="nu-planta" class="select-lat">
              @foreach($plantas as $pl)
                <option value="{{ $pl->id }}" @selected($pl->id === $plantaActiva->id)>{{ $pl->nombre }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="nu-pass">Contraseña inicial (mín. 6 caracteres)</label>
          <input type="password" name="password" id="nu-pass" class="input-lat" required minlength="6">
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-nuevo-usuario">Cancelar</button>
        <button type="submit" class="btn btn-ember">Crear usuario</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modal-edit-usuario" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-usuario">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-edit-usuario">Editar usuario y credenciales</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-usuario" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" id="form-edit-usuario" action="">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="form-grupo">
          <label class="form-label" for="eu-name">Nombre completo</label>
          <input type="text" name="name" id="eu-name" class="input-lat" required>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="eu-user">Usuario</label>
            <input type="text" name="usuario" id="eu-user" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="eu-email">Correo (opcional)</label>
            <input type="email" name="email" id="eu-email" class="input-lat">
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="eu-cargo">Cargo</label>
            <select name="cargo" id="eu-cargo" class="select-lat">
              <option value="recolector_ruta">Recolector en ruta</option>
              <option value="control_tina">Control de tina / calidad</option>
              <option value="encargado_planta">Encargado de planta</option>
              <option value="contador_externo">Contador externo</option>
              <option value="administracion">Administración</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="eu-planta">Planta</label>
            <select name="planta_id" id="eu-planta" class="select-lat">
              @foreach($plantas as $pl)
                <option value="{{ $pl->id }}">{{ $pl->nombre }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
          <div class="form-grupo">
            <label class="form-label" for="eu-pass">Nueva contraseña (opcional)</label>
            <input type="password" name="password" id="eu-pass" class="input-lat" minlength="6" placeholder="Dejar vacío para mantener">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="eu-activo">Estado</label>
            <select name="activo" id="eu-activo" class="select-lat">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-edit-usuario">Cancelar</button>
        <button type="submit" class="btn btn-ember">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
@endif

<script>
  // 1. Navegación de módulos (Desktop + Móvil) con persistencia de pestaña activa
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
      'tab-quincena': '{!! $icoBalance !!}',
      'tab-estadisticas': '{!! $icoStats !!}',
      'tab-admin': '{!! $icoAdmin !!}'
    };

    function activarModulo(tabId, enfocarTab) {
      if (!document.getElementById(tabId)) tabId = 'tab-acopio';

      document.querySelectorAll('.tab-panel').forEach(p => {
        p.hidden = p.id !== tabId;
      });

      tabs.forEach(t => {
        const activa = t.getAttribute('aria-controls') === tabId;
        t.setAttribute('aria-selected', activa ? 'true' : 'false');
        t.tabIndex = activa ? 0 : -1;
        if (activa && enfocarTab) t.focus();
      });

      dropdownItems.forEach(item => {
        const activa = item.dataset.tab === tabId;
        item.classList.toggle('activo', activa);
        item.setAttribute('aria-selected', activa ? 'true' : 'false');
        if (activa && tituloActivo) {
          tituloActivo.textContent = item.dataset.titulo;
          if (iconActivo && iconos[tabId]) iconActivo.innerHTML = iconos[tabId];
        }
      });

      if (btnDropdown && menuDropdown) {
        btnDropdown.setAttribute('aria-expanded', 'false');
        menuDropdown.classList.remove('abierto');
      }

      try { sessionStorage.setItem('lat_active_tab', tabId); } catch (_) {}
    }

    tabs.forEach((tab, i) => {
      tab.addEventListener('click', () => activarModulo(tab.getAttribute('aria-controls'), false));
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

    if (btnDropdown && menuDropdown) {
      btnDropdown.addEventListener('click', () => {
        const abierto = menuDropdown.classList.toggle('abierto');
        btnDropdown.setAttribute('aria-expanded', abierto ? 'true' : 'false');
      });
      dropdownItems.forEach(item => {
        item.addEventListener('click', () => activarModulo(item.dataset.tab, false));
      });
      document.addEventListener('click', e => {
        if (!e.target.closest('#panel-modulo-dropdown') && menuDropdown.classList.contains('abierto')) {
          menuDropdown.classList.remove('abierto');
          btnDropdown.setAttribute('aria-expanded', 'false');
        }
      });
    }

    // Restaurar pestaña tras recarga o envío de formulario
    try {
      const savedTab = sessionStorage.getItem('lat_active_tab');
      if (savedTab && document.getElementById(savedTab)) {
        activarModulo(savedTab, false);
      }
    } catch (_) {}
  })();

  // 2. Sistema de Modales y Confirmación JS ("¿Está seguro de que quieres cambiar?")
  (function () {
    let pendingForm = null;
    const modalConfirm = document.getElementById('modal-confirmacion-lat');
    const confirmMsgEl = document.getElementById('confirm-modal-message');
    const btnConfirmar = document.getElementById('btn-confirmar-accion-js');

    function abrirModal(id) {
      const el = document.getElementById(id);
      if (!el) return;
      el.hidden = false;
      const focusable = el.querySelector('input:not([type="hidden"]), select, button:not(.modal-close)');
      if (focusable) focusable.focus();
    }

    function cerrarModal(id) {
      const el = document.getElementById(id);
      if (el) el.hidden = true;
      if (id === 'modal-confirmacion-lat') pendingForm = null;
    }

    document.querySelectorAll('[data-open-modal]').forEach(btn => {
      btn.addEventListener('click', () => abrirModal(btn.dataset.openModal));
    });
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
      btn.addEventListener('click', () => cerrarModal(btn.dataset.closeModal));
    });
    document.querySelectorAll('.modal-overlay').forEach(ov => {
      ov.addEventListener('click', e => {
        if (e.target === ov) cerrarModal(ov.id);
      });
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay:not([hidden])').forEach(ov => cerrarModal(ov.id));
      }
    });

    // Interceptor JS para Calidad Sanitaria, Cierre de Lote y Eliminaciones
    document.addEventListener('submit', function (e) {
      const form = e.target;
      if (!form.classList || !form.classList.contains('js-confirm-form')) return;
      if (form.dataset.confirmed === 'true') return;

      e.preventDefault();
      pendingForm = form;

      const kind = form.dataset.confirmKind;
      let msg = '¿Está seguro de que desea aplicar este cambio?';
      if (window.LAT_I18N) {
        if (kind === 'calidad') {
          msg = window.LAT_I18N.t('confirm.quality_msg', {
            nombre: form.dataset.nombre || '',
            litros: form.dataset.litros || '0',
            estado: form.dataset.nuevoEstado || ''
          });
        } else if (kind === 'finish_batch') {
          msg = window.LAT_I18N.t('confirm.finish_batch_msg', {
            codigo: form.dataset.codigo || ''
          });
        } else if (kind === 'delete') {
          msg = window.LAT_I18N.t('confirm.delete_msg', {
            detalle: form.dataset.detalle || ''
          });
        }
      }

      confirmMsgEl.textContent = msg;
      abrirModal('modal-confirmacion-lat');
    });

    if (btnConfirmar) {
      btnConfirmar.addEventListener('click', function () {
        if (!pendingForm) return;
        const target = pendingForm;
        target.dataset.confirmed = 'true';
        cerrarModal('modal-confirmacion-lat');
        target.submit();
      });
    }

    // Sub-tabs del modal Editar Datos
    document.querySelectorAll('.subtab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.subtab-btn').forEach(b => b.classList.toggle('activo', b === btn));
        document.querySelectorAll('.subtab-panel').forEach(p => { p.hidden = p.id !== btn.dataset.subtab; });
      });
    });

    // Disparadores para editar registros individuales
    const baseAcopioUrl = @json(url('/panel/acopio'));
    document.querySelectorAll('.js-edit-acopio').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-acopio').action = baseAcopioUrl + '/' + btn.dataset.id;
        document.getElementById('edit-ac-prov').value = btn.dataset.proveedor;
        document.getElementById('edit-ac-litros').value = btn.dataset.litros;
        document.getElementById('edit-ac-calidad').value = btn.dataset.calidad;
        document.getElementById('edit-ac-metodo').value = btn.dataset.metodo;
        document.getElementById('edit-ac-hora').value = btn.dataset.hora;
        cerrarModal('modal-editar-datos');
        abrirModal('modal-edit-acopio');
      });
    });

    const baseMovUrl = @json(url('/panel/movimiento'));
    document.querySelectorAll('.js-edit-mov').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-mov').action = baseMovUrl + '/' + btn.dataset.id;
        document.getElementById('edit-mov-tipo').value = btn.dataset.tipo;
        document.getElementById('edit-mov-prov').value = btn.dataset.proveedor;
        document.getElementById('edit-mov-concepto').value = btn.dataset.concepto;
        document.getElementById('edit-mov-unidades').value = btn.dataset.unidades;
        document.getElementById('edit-mov-monto').value = btn.dataset.monto;
        cerrarModal('modal-editar-datos');
        abrirModal('modal-edit-mov');
      });
    });

    const baseTinaUrl = @json(url('/panel/tinas'));
    document.querySelectorAll('.js-edit-tina').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-tina').action = baseTinaUrl + '/' + btn.dataset.id;
        document.getElementById('et-codigo').value = btn.dataset.codigo;
        document.getElementById('et-nombre').value = btn.dataset.nombre;
        document.getElementById('et-cap').value = btn.dataset.capacidad;
        document.getElementById('et-tipo').value = btn.dataset.tipo;
        document.getElementById('et-activa').value = btn.dataset.activa;
        abrirModal('modal-edit-tina');
      });
    });

    const basePlantaUrl = @json(url('/panel/admin/plantas'));
    document.querySelectorAll('.js-edit-planta').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-planta').action = basePlantaUrl + '/' + btn.dataset.id;
        document.getElementById('ep-cod').value = btn.dataset.codigo;
        document.getElementById('ep-nom').value = btn.dataset.nombre;
        document.getElementById('ep-ubi').value = btn.dataset.ubicacion;
        document.getElementById('ep-pre').value = btn.dataset.precio;
        document.getElementById('ep-act').value = btn.dataset.activa;
        abrirModal('modal-edit-planta');
      });
    });

    const baseUserUrl = @json(url('/panel/admin/usuarios'));
    document.querySelectorAll('.js-edit-usuario').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-usuario').action = baseUserUrl + '/' + btn.dataset.id;
        document.getElementById('eu-name').value = btn.dataset.name;
        document.getElementById('eu-user').value = btn.dataset.usuario;
        document.getElementById('eu-email').value = btn.dataset.email;
        document.getElementById('eu-cargo').value = btn.dataset.cargo;
        document.getElementById('eu-planta').value = btn.dataset.planta;
        document.getElementById('eu-activo').value = btn.dataset.activo;
        document.getElementById('eu-pass').value = '';
        abrirModal('modal-edit-usuario');
      });
    });
  })();

  // 3. Botonera táctil de una mano
  document.querySelectorAll('.botonera button').forEach(b => {
    b.addEventListener('click', () => {
      const input = document.getElementById('input-litros');
      input.value = ((parseFloat(input.value) || 0) + parseFloat(b.dataset.litros)).toFixed(1);
    });
  });

  // 4. Reconocimiento de voz (Web Speech API) con confirmación hablada
  (function () {
    const btnVoz = document.getElementById('btn-voz');
    const txtBtn = document.getElementById('txt-btn-voz');
    const transcripcion = document.getElementById('transcripcion-voz');
    if (!btnVoz) return;

    function reposo() {
      txtBtn.textContent = window.LAT_I18N ? window.LAT_I18N.t('m1.voice_btn') : 'Dictar acopio por voz';
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
