<section id="sec-calidad" class="tab-panel" aria-label="Control de calidad sanitaria" data-i18n-aria="calidad.section_aria" hidden>
  <div class="panel-card">
    <h2 data-i18n="m2.title">Control sanitario en recepción (prueba de alcohol a 74°)</h2>
    <p><strong data-i18n="m2.sub_lead">Principio de cero desperdicio:</strong> <span data-i18n="m2.sub_body">la leche con acidez no se desecha ni se multa al productor. Se clasifica de inmediato y se deriva a quesillos frescos de 4 días.</span></p>

    @if($usuario->tienePermiso('calidad.ver'))
      @if(($pendientesOtrosDias ?? 0) > 0 && ($filtrosCalidad['vista'] ?? 'hoy') !== 'pendientes')
        <div class="alerta alerta-amarillo" role="status" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.65rem; margin-bottom: 1rem;">
          <span>
            <strong class="num-tabular">{{ $pendientesOtrosDias }}</strong>
            <span data-i18n="m2.other_days_pending_notice">entrega(s) de días anteriores siguen pendientes de evaluación sanitaria.</span>
          </span>
          <a href="{{ route('panel', ['cal_vista' => 'pendientes']) }}#sec-calidad" class="btn btn-ember btn-compacto" data-i18n="m2.view_other_days_pending">Ver pendientes de otros días</a>
        </div>
      @endif

      <form method="GET" action="{{ route('panel') }}#sec-calidad" class="acopio-filtros-bar" aria-label="Filtrar entregas en calidad sanitaria" style="margin-bottom: 1rem;">
        <div>
          <label for="f-cal-vista" class="form-label" data-i18n="m2.filter_view">Vista</label>
          <select id="f-cal-vista" name="cal_vista" class="select-lat">
            <option value="hoy" @selected(($filtrosCalidad['vista'] ?? 'hoy') === 'hoy') data-i18n="m2.view_today">Hoy</option>
            <option value="pendientes" @selected(($filtrosCalidad['vista'] ?? 'hoy') === 'pendientes') data-i18n="m2.view_pending_all">Pendientes (todos los días)</option>
            <option value="todos" @selected(($filtrosCalidad['vista'] ?? 'hoy') === 'todos') data-i18n="m2.view_all">Todos / por fecha</option>
          </select>
        </div>
        <div>
          <label for="f-cal-desde" class="form-label" data-i18n="m2.filter_from">Desde fecha</label>
          <input type="date" id="f-cal-desde" name="cal_fecha_desde" class="input-lat" value="{{ $filtrosCalidad['fecha_desde'] ?? '' }}">
        </div>
        <div>
          <label for="f-cal-hasta" class="form-label" data-i18n="m2.filter_to">Hasta fecha</label>
          <input type="date" id="f-cal-hasta" name="cal_fecha_hasta" class="input-lat" value="{{ $filtrosCalidad['fecha_hasta'] ?? '' }}">
        </div>
        <div>
          <label for="f-cal-estado" class="form-label" data-i18n="m2.filter_status">Estado</label>
          <select id="f-cal-estado" name="cal_estado" class="select-lat">
            <option value="" data-i18n="m2.filter_all_status">Todos</option>
            <option value="pendiente" @selected(($filtrosCalidad['estado'] ?? '') === 'pendiente') data-i18n="m2.status_pending">Sin probar</option>
            <option value="buena" @selected(($filtrosCalidad['estado'] ?? '') === 'buena') data-i18n="m2.status_good">Óptima</option>
            <option value="acida" @selected(($filtrosCalidad['estado'] ?? '') === 'acida') data-i18n="m2.status_acid">Con acidez</option>
            <option value="descartada" @selected(($filtrosCalidad['estado'] ?? '') === 'descartada') data-i18n="m2.status_discarded">Descartada</option>
          </select>
        </div>
        <div>
          <label for="f-cal-prov" class="form-label" data-i18n="m2.filter_producer">Productor</label>
          <select id="f-cal-prov" name="cal_proveedor_id" class="select-lat">
            <option value="" data-i18n="m2.filter_all_producers">Todos los productores</option>
            @foreach($proveedores as $prov)
              <option value="{{ $prov->id }}" @selected((string) ($filtrosCalidad['proveedor_id'] ?? '') === (string) $prov->id)>{{ $prov->codigo }} · {{ $prov->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="acopio-filtros-btns">
          <button type="submit" class="btn btn-ocean btn-compacto" data-i18n="common.filter">Filtrar</button>
          @if($hayFiltrosCalidad ?? false)
            <a href="{{ route('panel') }}#sec-calidad" class="btn btn-outline btn-compacto" data-i18n="dash.range_hoy">Hoy</a>
          @endif
        </div>
      </form>

      <div class="tabla-region" role="region" aria-label="Clasificación sanitaria de las entregas" data-i18n-aria="calidad.queue_aria" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th scope="col">Fecha / Hora</th>
              <th scope="col" data-i18n="m1.col_producer">Productor</th>
              <th scope="col" class="num" data-i18n="m1.col_liters">Litros</th>
              <th scope="col" data-i18n="m2.col_status">Estado actual</th>
              <th scope="col" data-i18n="m2.col_dest">Destino operativo</th>
              @if($usuario->tienePermiso('calidad.evaluar'))
                <th scope="col" data-i18n="m2.col_triage">Triage inmediato</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @forelse($acopiosCalidad as $acopio)
              <tr>
                <td class="num-tabular">{{ $acopio->fecha?->format('d/m/Y') }}<small>{{ substr($acopio->hora, 0, 5) }}</small></td>
                <td><strong>{{ $acopio->proveedor->nombre }}</strong><small>{{ $acopio->proveedor->codigo }}</small></td>
                <td class="num" style="font-size: 1.1rem;">{{ number_format($acopio->litros, 1) }} L</td>
                <td>
                  @if($acopio->calidad === 'buena')
                    <span class="chip chip-verde">{!! $icoOk !!} <span data-i18n="m2.status_good">Óptima</span></span>
                  @elseif($acopio->calidad === 'acida')
                    <span class="chip chip-amarillo">{!! $icoAlerta !!} <span data-i18n="m2.status_acid">Con acidez</span></span>
                  @elseif($acopio->calidad === 'descartada')
                    <span class="chip chip-rojo" data-i18n="m2.status_discarded">Descartada</span>
                  @else
                    <span class="chip chip-amarillo" data-i18n="m2.status_pending">Sin probar</span>
                  @endif
                </td>
                <td>
                  @if($acopio->destino === 'quesillo_4_dias')
                    <span data-i18n="m2.dest_quesillo">Tina quesillo 4 días</span>
                  @elseif($acopio->destino === 'queso_yogur')
                    <span data-i18n="m2.dest_main">Tina principal (queso / yogur)</span>
                  @elseif($acopio->destino === 'descartada')
                    <span data-i18n="m2.dest_discarded">Descartada (excluida de tina y pago)</span>
                    @if(! empty($acopio->motivo_descarte))
                      <small>{{ $acopio->motivo_descarte }}</small>
                    @endif
                  @else
                    <span data-i18n="m2.dest_pending">Pendiente de prueba</span>
                  @endif
                  @if($acopio->evaluado_en)
                    <small class="num-tabular">{{ $acopio->evaluado_en->format('d/m H:i') }}@if($acopio->evaluador) · {{ $acopio->evaluador->name }}@endif</small>
                  @endif
                </td>
                @if($usuario->tienePermiso('calidad.evaluar'))
                  @php $bloqueadoEnLote = $acopio->lote_id !== null; @endphp
                  <td>
                    @if($bloqueadoEnLote)
                      <span class="chip chip-celeste" data-i18n="m2.used_in_batch">En proceso en tina</span>
                    @else
                      <div class="acciones-celda">
                        <form method="POST" action="{{ route('panel.calidad', $acopio->id) }}"
                          class="js-confirm-form"
                          data-confirm-kind="calidad"
                          data-nombre="{{ $acopio->proveedor->nombre }}"
                          data-litros="{{ number_format($acopio->litros, 1) }}"
                          data-estado-key="buena">
                          @csrf
                          <input type="hidden" name="calidad" value="buena">
                          <button type="submit" class="btn btn-verde btn-compacto" aria-label="Aprobar leche de {{ $acopio->proveedor->nombre }}" data-i18n-aria="calidad.approve_aria">
                            {!! $icoOk !!} <span data-i18n="m2.approve">Aprobar</span>
                          </button>
                        </form>
                        <form method="POST" action="{{ route('panel.calidad', $acopio->id) }}"
                          class="js-confirm-form"
                          data-confirm-kind="calidad"
                          data-nombre="{{ $acopio->proveedor->nombre }}"
                          data-litros="{{ number_format($acopio->litros, 1) }}"
                          data-estado-key="acida">
                          @csrf
                          <input type="hidden" name="calidad" value="acida">
                          <button type="submit" class="btn btn-ember btn-compacto" aria-label="Desviar a quesillo la leche de {{ $acopio->proveedor->nombre }}" data-i18n-aria="calidad.divert_aria">
                            {!! $icoAlerta !!} <span data-i18n="m2.divert">Desviar a quesillo</span>
                          </button>
                        </form>
                        @if($acopio->calidad !== 'descartada')
                          <form method="POST" action="{{ route('panel.calidad', $acopio->id) }}"
                            class="js-confirm-form"
                            data-confirm-kind="calidad"
                            data-nombre="{{ $acopio->proveedor->nombre }}"
                            data-litros="{{ number_format($acopio->litros, 1) }}"
                            data-estado-key="descartada">
                            @csrf
                            <input type="hidden" name="calidad" value="descartada">
                            <button type="submit" class="btn btn-rojo btn-compacto" aria-label="Descartar entrega de {{ $acopio->proveedor->nombre }}" data-i18n-aria="calidad.discard_aria">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                              <span data-i18n="m2.discard">Descartar</span>
                            </button>
                          </form>
                        @endif
                      </div>
                    @endif
                  </td>
                @endif
              </tr>
            @empty
              <tr><td colspan="{{ $usuario->tienePermiso('calidad.evaluar') ? 6 : 5 }}" style="text-align: center; padding: 2rem; color: var(--color-graphite);" data-i18n="m2.empty">No hay entregas para clasificar.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    @else
      <p class="legal">Tu cuenta tiene permisos operativos sin acceso de lectura a la lista sanitaria.</p>
    @endif
  </div>
</section>
