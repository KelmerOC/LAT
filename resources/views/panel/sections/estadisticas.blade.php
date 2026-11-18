<section id="sec-estadisticas" class="tab-panel" aria-label="Estadísticas comparativas y ranking" hidden>
  @php
    $lider = $estadisticas['planta_lider'];
    $maxVentas = max(1, (float) ($estadisticas['ranking_plantas']->max('ventas_monto') ?? 1));
  @endphp

  <div class="panel-card" style="margin-bottom: 1.5rem;">
    <h2 data-i18n="m5.title">Estadísticas de plantas y ventas</h2>
    <p style="margin-bottom: 1.25rem;" data-i18n="m5.sub">Comparativa de desempeño: qué planta vende más, producción de moldes y ranking de proveedores.</p>

    <div class="dash-kpi-grid" style="margin-bottom: 1.5rem;">
      <article class="dash-kpi-card">
        <div class="dash-kpi-top">
          <div>
            <span class="dash-kpi-label" data-i18n="m5.top_plant">Planta que vende más</span>
            <strong class="dash-kpi-value" style="font-size: 1.2rem;">{{ $lider?->nombre ?? 'Sin ventas' }}</strong>
          </div>
          <div class="dash-kpi-icon is-ocean" aria-hidden="true">{!! $icoStats !!}</div>
        </div>
        <div class="dash-kpi-foot">
          <span><span data-i18n="dash.col_sales">Ventas:</span> <strong>S/ {{ number_format($lider?->ventas_monto ?? 0, 2) }}</strong> ({{ $lider?->participacion_pct ?? 0 }}%)</span>
        </div>
      </article>

      <article class="dash-kpi-card">
        <div class="dash-kpi-top">
          <div>
            <span class="dash-kpi-label"><span data-i18n="m5.plant_sales">Ventas del período</span> · {{ $plantaActiva->codigo }}</span>
            <strong class="dash-kpi-value">S/ {{ number_format($estadisticas['totales_planta']['ventas_monto'], 2) }}</strong>
          </div>
          <div class="dash-kpi-icon is-ember" aria-hidden="true">{!! $icoBalance !!}</div>
        </div>
        <div class="dash-kpi-foot">
          <span>{{ $estadisticas['totales_planta']['ventas_unidades'] }} <span data-i18n="dash.kpi_units_sold">unidades vendidas</span></span>
        </div>
      </article>

      <article class="dash-kpi-card">
        <div class="dash-kpi-top">
          <div>
            <span class="dash-kpi-label" data-i18n="m5.plant_liters">Litros acopiados (período)</span>
            <strong class="dash-kpi-value">{{ number_format($estadisticas['totales_planta']['litros_periodo'], 1) }}<small>L</small></strong>
          </div>
          <div class="dash-kpi-icon is-celeste" aria-hidden="true">{!! $icoMicS !!}</div>
        </div>
        <div class="dash-kpi-foot">
          <span>{{ $estadisticas['totales_planta']['moldes_producidos'] }} <span data-i18n="dash.kpi_molds">moldes producidos</span></span>
        </div>
      </article>

      <article class="dash-kpi-card">
        <div class="dash-kpi-top">
          <div>
            <span class="dash-kpi-label" data-i18n="m5.quality_ratio">Calidad aprobada vs ácida</span>
            <strong class="dash-kpi-value">{{ number_format($estadisticas['calidad']['buena_pct'], 0) }}<small>%</small></strong>
          </div>
          <div class="dash-kpi-icon is-verde" aria-hidden="true">{!! $icoOk !!}</div>
        </div>
        <div class="dash-kpi-foot">
          <span>{{ number_format($estadisticas['calidad']['acida_litros'], 1) }} L <span data-i18n="dash.kpi_to_quesillo">a quesillo</span></span>
        </div>
      </article>
    </div>

    <div class="panel-grid-eq" style="margin-bottom: 1.5rem; align-items: start;">
      <div>
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
                <div class="num-tabular" style="font-size: 0.92rem;">
                  <strong>S/ {{ number_format($rp->ventas_monto, 2) }}</strong>
                  <small style="color: var(--color-graphite);">({{ $rp->participacion_pct }}% · {{ $rp->ventas_unidades }} und · {{ number_format($rp->litros_acopiados, 1) }} L)</small>
                </div>
              </div>
              <div class="stats-bar-track" role="img" aria-label="{{ $rp->nombre }}: S/ {{ number_format($rp->ventas_monto, 2) }}">
                <div class="stats-bar-fill" style="width: {{ $barPct }}%;"></div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="dash-card" style="background: #f8fbfe;">
        <div class="dash-card-head">
          <div>
            <h3 data-i18n="dash.c4_title">Distribución sanitaria y ventas</h3>
            <p class="dash-card-sub" data-i18n="dash.c4_sub">Comparativa visual por calidad de leche</p>
          </div>
        </div>
        <div class="dash-chart-box" style="height: 230px;">
          <canvas id="chart-estadisticas-calidad" aria-label="Gráfica de distribución sanitaria"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="panel-grid-eq">
    <div class="panel-card">
      <h3 data-i18n="m5.top_producers">Productores con mayor entrega en esta planta</h3>
      <div class="tabla-region" tabindex="0" style="margin-top: 0.75rem;">
        <table class="tabla-plana" style="min-width: 420px;">
          <thead>
            <tr>
              <th data-i18n="dash.col_producer">Productor</th>
              <th class="num" data-i18n="dash.col_deliveries">Entregas</th>
              <th class="num" data-i18n="dash.col_liters">Litros</th>
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
        <div style="padding: 1rem; background: var(--verde-fill); border-radius: 8px;">
          <strong data-i18n="m5.good_milk">Leche óptima (Tina principal):</strong>
          <span class="num-tabular">{{ number_format($estadisticas['calidad']['buena_litros'], 1) }} L ({{ $estadisticas['calidad']['buena_pct'] }}%)</span>
        </div>
        <div style="padding: 1rem; background: var(--amarillo-fill); border-radius: 8px;">
          <strong data-i18n="m5.acid_milk">Leche con acidez (Quesillo 4 días):</strong>
          <span class="num-tabular">{{ number_format($estadisticas['calidad']['acida_litros'], 1) }} L ({{ $estadisticas['calidad']['acida_pct'] }}%)</span>
        </div>
        <div style="padding: 1rem; background: var(--color-sky-wash); border-radius: 8px;">
          <strong data-i18n="m5.voice_vs_manual">Uso de dictado por voz vs manual:</strong>
          <span class="num-tabular">{{ $estadisticas['metodos']['voz_count'] }} (<span data-i18n="common.voice">Voz</span> · {{ $estadisticas['metodos']['voz_pct'] }}%) · {{ $estadisticas['metodos']['manual_count'] }} (<span data-i18n="common.manual">Manual</span>)</span>
        </div>
      </div>
    </div>
  </div>
</section>
