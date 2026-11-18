@php
  $k = $dashboardData['kpis'];
  $deltaLitros = $k['litros']['delta_pct'];
  $deltaVentas = $k['ventas']['delta_pct'];
  $deltaMoldes = $k['produccion']['delta_pct'];
@endphp

<section id="sec-dashboard" class="tab-panel" aria-label="Dashboard general y gráficas">
  <!-- Fila 1: 4 Tarjetas KPI con variación vs período anterior -->
  <div class="dash-kpi-grid">
    <article class="dash-kpi-card">
      <div class="dash-kpi-top">
        <div>
          <span class="dash-kpi-label"><span data-i18n="dash.kpi_intake">Acopio</span> (<span data-i18n="dash.range_{{ $rango }}">{{ $dashboardData['etiqueta_rango'] }}</span>)</span>
          <strong class="dash-kpi-value">{{ number_format($k['litros']['valor'], 1) }}<small>L</small></strong>
        </div>
        <div class="dash-kpi-icon is-ocean" aria-hidden="true">{!! $icoMicS !!}</div>
      </div>
      <div class="dash-kpi-foot">
        @if($deltaLitros !== null)
          <span class="dash-delta {{ $deltaLitros >= 0 ? 'up' : 'down' }}">{{ $deltaLitros >= 0 ? '+' : '' }}{{ $deltaLitros }}%</span>
          <span data-i18n="dash.kpi_vs_prev">vs período anterior</span>
        @else
          <span class="dash-delta neutral">{{ $k['litros']['entregas'] }} <span data-i18n="dash.kpi_deliveries">entregas</span></span>
          <span><span data-i18n="dash.kpi_price">Precio:</span> S/ {{ number_format($precioLitro, 2) }}/L</span>
        @endif
      </div>
    </article>

    <article class="dash-kpi-card">
      <div class="dash-kpi-top">
        <div>
          <span class="dash-kpi-label" data-i18n="dash.kpi_sales">Ventas de productos</span>
          <strong class="dash-kpi-value">S/ {{ number_format($k['ventas']['valor'], 2) }}</strong>
        </div>
        <div class="dash-kpi-icon is-ember" aria-hidden="true">{!! $icoBalance !!}</div>
      </div>
      <div class="dash-kpi-foot">
        @if($deltaVentas !== null)
          <span class="dash-delta {{ $deltaVentas >= 0 ? 'up' : 'down' }}">{{ $deltaVentas >= 0 ? '+' : '' }}{{ $deltaVentas }}%</span>
          <span>{{ $k['ventas']['unidades'] }} <span data-i18n="dash.kpi_units_sold">unidades vendidas</span></span>
        @else
          <span class="dash-delta neutral">{{ $k['ventas']['unidades'] }} und.</span>
          <span><span data-i18n="dash.kpi_expenses">Gastos:</span> S/ {{ number_format($k['ventas']['gastos'], 2) }}</span>
        @endif
      </div>
    </article>

    <article class="dash-kpi-card">
      <div class="dash-kpi-top">
        <div>
          <span class="dash-kpi-label" data-i18n="dash.kpi_molds">Moldes producidos</span>
          <strong class="dash-kpi-value">{{ number_format($k['produccion']['moldes']) }}<small>und</small></strong>
        </div>
        <div class="dash-kpi-icon is-celeste" aria-hidden="true">{!! $icoTina !!}</div>
      </div>
      <div class="dash-kpi-foot">
        @if($deltaMoldes !== null)
          <span class="dash-delta {{ $deltaMoldes >= 0 ? 'up' : 'down' }}">{{ $deltaMoldes >= 0 ? '+' : '' }}{{ $deltaMoldes }}%</span>
        @endif
        <span>{{ $k['produccion']['tinas_en_uso'] }}/{{ $k['produccion']['tinas_total'] }} <span data-i18n="dash.kpi_vats_active">tinas activas</span></span>
      </div>
    </article>

    <article class="dash-kpi-card">
      <div class="dash-kpi-top">
        <div>
          <span class="dash-kpi-label" data-i18n="dash.kpi_quality">Calidad óptima (74°)</span>
          <strong class="dash-kpi-value">{{ number_format($k['calidad']['buena_pct'], 1) }}<small>%</small></strong>
        </div>
        <div class="dash-kpi-icon is-verde" aria-hidden="true">{!! $icoOk !!}</div>
      </div>
      <div class="dash-kpi-foot">
        <span class="dash-delta neutral">{{ number_format($k['calidad']['acida_litros'], 1) }} L</span>
        <span><span data-i18n="dash.kpi_to_quesillo">a quesillo</span> · {{ $k['calidad']['pendientes'] }} <span data-i18n="dash.kpi_pending">pendientes</span></span>
      </div>
    </article>
  </div>

  <!-- Fila 2: 3 Gráficas interactivas (Chart.js) -->
  <div class="dash-charts-grid">
    <article class="dash-card">
      <div class="dash-card-head">
        <div>
          <h2 data-i18n="dash.c1_title">Acopio diario (Litros)</h2>
          <p class="dash-card-sub" data-i18n="dash.c1_sub">Leche óptima vs ácida por día</p>
        </div>
        <span class="chip chip-celeste" data-i18n="dash.range_{{ $rango }}">{{ $dashboardData['etiqueta_rango'] }}</span>
      </div>
      <div class="dash-chart-box">
        <canvas id="chart-acopio-diario" aria-label="Gráfica de barras de acopio diario"></canvas>
      </div>
      <div class="dash-chart-foot">
        <span><span data-i18n="dash.c1_good">Óptima:</span> <strong>{{ number_format($k['calidad']['buena_litros'], 1) }} L</strong></span>
        <span><span data-i18n="dash.c1_acid">Ácida:</span> <strong>{{ number_format($k['calidad']['acida_litros'], 1) }} L</strong></span>
      </div>
    </article>

    <article class="dash-card">
      <div class="dash-card-head">
        <div>
          <h2 data-i18n="dash.c2_title">Ventas vs Gastos (S/)</h2>
          <p class="dash-card-sub" data-i18n="dash.c2_sub">Flujo diario de ingresos y egresos</p>
        </div>
        <span class="chip chip-verde">S/ {{ number_format($k['ventas']['valor'], 0) }}</span>
      </div>
      <div class="dash-chart-box">
        <canvas id="chart-ventas-gastos" aria-label="Gráfica de líneas de ventas y gastos"></canvas>
      </div>
      <div class="dash-chart-foot">
        <span data-i18n="dash.c2_comm">Comisión LAT: S/ 0.00</span>
        <span><span data-i18n="dash.c2_exp">Gastos:</span> <strong>S/ {{ number_format($k['ventas']['gastos'], 2) }}</strong></span>
      </div>
    </article>

    <article class="dash-card">
      <div class="dash-card-head">
        <div>
          <h2 data-i18n="dash.c3_title">Producción de moldes</h2>
          <p class="dash-card-sub" data-i18n="dash.c3_sub">Unidades cerradas en tina por día</p>
        </div>
        <span class="chip chip-fog">{{ $k['produccion']['moldes'] }} und</span>
      </div>
      <div class="dash-chart-box">
        <canvas id="chart-moldes-diarios" aria-label="Gráfica de producción diaria de moldes"></canvas>
      </div>
      <div class="dash-chart-foot">
        <span data-i18n="dash.c3_yield">Rendimiento: ~11 moldes / 100 L</span>
        <span>{{ $plantaActiva->codigo }}</span>
      </div>
    </article>
  </div>

  <!-- Fila 3: Estado de Tinas / Ranking + Actividad Reciente -->
  <div class="dash-bottom-grid">
    <article class="dash-card">
      <div class="dash-card-head">
        <div>
          @if($usuario->tienePermiso('dashboard.ver_red') && $dashboardData['ranking_plantas']->isNotEmpty())
            <h2 data-i18n="dash.tinas_rank_title">Desempeño de plantas y tinas activas</h2>
            <p class="dash-card-sub" data-i18n="dash.tinas_rank_sub">Comparativa de ventas de red y ocupación actual de tinas</p>
          @else
            <h2 data-i18n="dash.tinas_only_title">Estado de tinas y principales productores</h2>
            <p class="dash-card-sub"><span data-i18n="dash.tinas_only_sub">Ocupación térmica actual</span> · {{ $plantaActiva->nombre }}</p>
          @endif
        </div>
        @if($usuario->puedeVerSeccion('tinas'))
          <button type="button" class="btn-micro js-jump-section" data-section="sec-tina" data-i18n="dash.go_vats">Ir a tinas</button>
        @endif
      </div>

      <div class="tabla-region" style="margin-bottom: 1rem;">
        <table class="tabla-plana" style="min-width: 460px;">
          <thead>
            <tr>
              <th data-i18n="dash.col_vat">Tina</th>
              <th data-i18n="dash.col_thermal">Estado térmico</th>
              <th class="num" data-i18n="dash.col_temp">Temp.</th>
              <th data-i18n="dash.col_occ">Ocupación</th>
            </tr>
          </thead>
          <tbody>
            @forelse($dashboardData['tinas_resumen'] as $tr)
              <tr>
                <td>
                  <strong>{{ $tr->nombre }}</strong>
                  <small>{{ $tr->codigo }} · @if($tr->lote) {{ $tr->lote }} @else <span data-i18n="dash.no_batch">Sin lote activo</span> @endif</small>
                </td>
                <td>
                  <span class="chip {{ $tr->apta_cuajo ? 'chip-verde' : ($tr->lote ? 'chip-amarillo' : 'chip-fog') }}">
                    {{ strtoupper(str_replace('_', ' ', $tr->estado)) }}
                  </span>
                </td>
                <td class="num">{{ $tr->temp !== null ? number_format($tr->temp, 1).' °C' : '—' }}</td>
                <td style="min-width: 130px;">
                  <div style="display: flex; justify-content: space-between; font-size: 0.78rem;">
                    <span>{{ number_format($tr->litros, 0) }}/{{ number_format($tr->capacidad, 0) }} L</span>
                    <strong>{{ $tr->ocupacion }}%</strong>
                  </div>
                  <div class="dash-progress-track">
                    <div class="dash-progress-fill {{ $tr->apta_cuajo ? 'is-verde' : '' }}" style="width: {{ $tr->ocupacion }}%;"></div>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" style="text-align: center; padding: 1.25rem;" data-i18n="dash.no_vats">Sin tinas registradas.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($usuario->tienePermiso('dashboard.ver_red') && $dashboardData['ranking_plantas']->isNotEmpty())
        <h3 style="font-size: 0.95rem; margin: 0.75rem 0 0.5rem;" data-i18n="dash.rank_plants_title">Ranking de ventas por planta</h3>
        <div class="tabla-region">
          <table class="tabla-plana" style="min-width: 460px;">
            <thead>
              <tr>
                <th data-i18n="dash.col_plant">Planta</th>
                <th class="num" data-i18n="dash.col_liters">Litros</th>
                <th class="num" data-i18n="dash.col_sales">Ventas (S/)</th>
                <th data-i18n="dash.col_share">Participación</th>
              </tr>
            </thead>
            <tbody>
              @foreach($dashboardData['ranking_plantas'] as $rp)
                <tr>
                  <td><strong>{{ $rp->nombre }}</strong><small>{{ $rp->ubicacion }}</small></td>
                  <td class="num">{{ number_format($rp->litros_acopiados, 1) }} L</td>
                  <td class="num"><strong>S/ {{ number_format($rp->ventas_monto, 2) }}</strong></td>
                  <td style="min-width: 120px;">
                    <div style="font-size: 0.78rem; font-weight: 700;">{{ $rp->participacion_pct }}%</div>
                    <div class="dash-progress-track">
                      <div class="dash-progress-fill is-ember" style="width: {{ min(100, $rp->participacion_pct) }}%;"></div>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @elseif($dashboardData['top_productores']->isNotEmpty())
        <h3 style="font-size: 0.95rem; margin: 0.75rem 0 0.5rem;" data-i18n="dash.top_prod_title">Top ganaderos del período</h3>
        <div class="tabla-region">
          <table class="tabla-plana" style="min-width: 420px;">
            <thead>
              <tr>
                <th data-i18n="dash.col_producer">Productor</th>
                <th class="num" data-i18n="dash.col_deliveries">Entregas</th>
                <th class="num" data-i18n="dash.col_liters">Litros</th>
                <th class="num" data-i18n="dash.col_share_prod">Aporte</th>
              </tr>
            </thead>
            <tbody>
              @foreach($dashboardData['top_productores']->take(4) as $tp)
                <tr>
                  <td><strong>{{ $tp->nombre }}</strong><small>{{ $tp->sector }}</small></td>
                  <td class="num">{{ $tp->entregas }}</td>
                  <td class="num"><strong>{{ number_format($tp->litros, 1) }} L</strong></td>
                  <td class="num">{{ $tp->porcentaje }}%</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </article>

    <article class="dash-card">
      <div class="dash-card-head">
        <div>
          <h2 data-i18n="dash.activity_title">Actividad reciente</h2>
          <p class="dash-card-sub" data-i18n="dash.c1_sub">Últimos registros de acopio, ventas y alertas</p>
        </div>
        <span class="chip chip-celeste">{{ $dashboardData['actividad_reciente']->count() }} <span data-i18n="dash.events_count">eventos</span></span>
      </div>

      <ul class="dash-timeline">
        @forelse($dashboardData['actividad_reciente'] as $ev)
          <li class="dash-timeline-item">
            <span class="dash-timeline-dot is-{{ $ev->tipo }}" aria-hidden="true"></span>
            <strong class="dash-timeline-title">{{ $ev->titulo }}</strong>
            <span class="dash-timeline-meta">{{ $ev->detalle }} · {{ $ev->fecha_txt }}</span>
          </li>
        @empty
          <li style="padding: 1.5rem 0; text-align: center; color: #64748b;" data-i18n="dash.no_activity">Aún no hay actividad registrada en esta planta.</li>
        @endforelse
      </ul>
    </article>
  </div>
</section>
