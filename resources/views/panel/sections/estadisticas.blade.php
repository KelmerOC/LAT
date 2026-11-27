<section id="sec-estadisticas" class="tab-panel" aria-label="Estadísticas comparativas y ranking" data-i18n-aria="m5.section_aria" hidden>
  @php
    $lider = $estadisticas['planta_lider'];
    $maxVentas = max(1, (float) ($estadisticas['ranking_plantas']->max('ventas_monto') ?? 1));
  @endphp

  <div class="panel-card" style="margin-bottom: 1.5rem;">
    <h2 data-i18n="m5.title">Estadísticas de plantas y ventas</h2>
    <p style="margin-bottom: 1.25rem;" data-i18n="m5.sub">Comparativa de desempeño: qué planta vende más, producción de moldes y ranking de proveedores.</p>

    <div class="dash-kpi-grid" style="margin-bottom: 1.5rem;">
      @if($usuario->tienePermiso('dashboard.ver_red'))
        <article class="dash-kpi-card">
          <div class="dash-kpi-top">
            <div>
              <span class="dash-kpi-label" data-i18n="m5.top_plant">Planta que vende más</span>
              <strong class="dash-kpi-value" style="font-size: 1.2rem;">@if($lider){{ $lider->nombre }}@else<span data-i18n="panel.no_sales">Sin ventas</span>@endif</strong>
            </div>
            <div class="dash-kpi-icon is-ocean" aria-hidden="true">{!! $icoStats !!}</div>
          </div>
          <div class="dash-kpi-foot">
            <span><span data-i18n="dash.col_sales">Ventas:</span> <strong>S/ {{ number_format($lider?->ventas_monto ?? 0, 2) }}</strong> ({{ $lider?->participacion_pct ?? 0 }}%)</span>
          </div>
        </article>
      @endif

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
      @if($usuario->tienePermiso('dashboard.ver_red'))
        <div>
          <h3 data-i18n="m5.who_sells_most">Ranking de plantas: ¿Quién vende más?</h3>
          <div class="stats-ranking-list" role="region" aria-label="Ranking de plantas" data-i18n-aria="m5.rank_aria">
            @foreach($estadisticas['ranking_plantas'] as $idx => $rp)
              @php $barPct = $maxVentas > 0 ? min(100, round(($rp->ventas_monto / $maxVentas) * 100, 1)) : 0; @endphp
              <div class="stats-bar-row {{ $idx === 0 ? 'is-leader' : '' }}">
                <div class="stats-bar-meta">
                  <div>
                    <strong>#{{ $idx + 1 }} · {{ $rp->nombre }}</strong>
                    <span class="chip {{ $idx === 0 ? 'chip-verde' : 'chip-fog' }}">{{ $rp->ubicacion }}</span>
                    @if($idx === 0)
                      <span class="chip chip-amarillo" data-i18n="m5.top_seller">Mayor vendedor</span>
                    @endif
                  </div>
                  <div class="num-tabular" style="font-size: 0.92rem;">
                    <strong>S/ {{ number_format($rp->ventas_monto, 2) }}</strong>
                    <small style="color: var(--color-graphite);">({{ $rp->participacion_pct }}% · {{ $rp->ventas_unidades }} <span data-i18n="panel.unit_und">und</span> · {{ number_format($rp->litros_acopiados, 1) }} L)</small>
                  </div>
                </div>
                <div class="stats-bar-track" role="img" aria-label="{{ $rp->nombre }}: S/ {{ number_format($rp->ventas_monto, 2) }}">
                  <div class="stats-bar-fill" style="width: {{ $barPct }}%;"></div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      <div class="dash-card" style="background: #f8fbfe;">
        <div class="dash-card-head">
          <div>
            <h3 data-i18n="dash.c4_title">Distribución sanitaria y ventas</h3>
            <p class="dash-card-sub" data-i18n="dash.c4_sub">Comparativa visual por calidad de leche</p>
          </div>
        </div>
        <div class="dash-chart-box" style="height: 230px;">
          <canvas id="chart-estadisticas-calidad" aria-label="Gráfica de distribución sanitaria" data-i18n-aria="dash.c4_title"></canvas>
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
              <tr><td colspan="4" style="text-align: center; padding: 1.5rem;" data-i18n="m5.no_deliveries">Sin entregas en el período.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="panel-card">
      <h3 data-i18n="m5.quality_split">Calidad sanitaria y método de captura</h3>
      <div style="display: grid; gap: 0.9rem; margin-top: 1rem;">
        <div class="stats-quality-box is-good">
          <strong data-i18n="m5.good_milk">Leche óptima (Tina principal):</strong>
          <span class="num-tabular">{{ number_format($estadisticas['calidad']['buena_litros'], 1) }} L ({{ $estadisticas['calidad']['buena_pct'] }}%)</span>
        </div>
        <div class="stats-quality-box is-acid">
          <strong data-i18n="m5.acid_milk">Leche con acidez (Quesillo 4 días):</strong>
          <span class="num-tabular">{{ number_format($estadisticas['calidad']['acida_litros'], 1) }} L ({{ $estadisticas['calidad']['acida_pct'] }}%)</span>
        </div>
        <div class="stats-quality-box is-voice">
          <strong data-i18n="m5.voice_vs_manual">Uso de dictado por voz vs manual:</strong>
          <span class="num-tabular">{{ $estadisticas['metodos']['voz_count'] }} (<span data-i18n="common.voice">Voz</span> · {{ $estadisticas['metodos']['voz_pct'] }}%) · {{ $estadisticas['metodos']['manual_count'] }} (<span data-i18n="common.manual">Manual</span>)</span>
        </div>
      </div>
    </div>
  </div>

  @if($usuario->esAdmin())
    <div class="panel-card" id="card-informes-planta" style="margin-top: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
        <div>
          <h3 data-i18n="m5.reports_history_title">Historial de Informes de Administradores de Planta</h3>
          <p style="margin: 0;" data-i18n="m5.reports_history_sub">Informes automáticos cada 24h y reportes manuales enviados por los encargados de planta en archivo de texto trazable.</p>
        </div>
        <span class="chip chip-celeste">{{ ($informesPlanta ?? collect())->count() }} informe(s)</span>
      </div>

      <form method="GET" action="{{ route('panel') }}#sec-estadisticas" class="excel-filter-bar" style="margin-bottom: 1rem;">
        <div class="form-grupo">
          <label for="f-inf-planta" class="form-label" data-i18n="m6.col_plant">Planta</label>
          <select id="f-inf-planta" name="inf_planta_id" class="select-lat">
            <option value="" data-i18n="m5.filter_all_plants">Todas las plantas</option>
            @foreach($plantas as $pl)
              <option value="{{ $pl->id }}" @selected((string) ($filtrosInformes['planta_id'] ?? '') === (string) $pl->id)>{{ $pl->codigo }} · {{ $pl->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-grupo">
          <label for="f-inf-desde" class="form-label" data-i18n="m4.filter_from">Desde</label>
          <input type="date" id="f-inf-desde" name="inf_fecha_desde" class="input-lat" value="{{ $filtrosInformes['fecha_desde'] ?? '' }}">
        </div>
        <div class="form-grupo">
          <label for="f-inf-hasta" class="form-label" data-i18n="m4.filter_to">Hasta</label>
          <input type="date" id="f-inf-hasta" name="inf_fecha_hasta" class="input-lat" value="{{ $filtrosInformes['fecha_hasta'] ?? '' }}">
        </div>
        <div class="form-grupo">
          <label for="f-inf-origen" class="form-label" data-i18n="m5.col_origin">Origen</label>
          <select id="f-inf-origen" name="inf_origen" class="select-lat">
            <option value="" data-i18n="m5.filter_all_origins">Todos</option>
            <option value="automatico_24h" @selected(($filtrosInformes['origen'] ?? '') === 'automatico_24h') data-i18n="m5.origin_auto">Automático 24h</option>
            <option value="manual_admin_planta" @selected(($filtrosInformes['origen'] ?? '') === 'manual_admin_planta') data-i18n="m5.origin_manual">Manual (Encargado)</option>
          </select>
        </div>
        <div class="excel-filter-actions">
          <button type="submit" class="btn btn-ocean btn-compacto" data-i18n="common.filter">Filtrar</button>
          <a href="{{ route('panel') }}#sec-estadisticas" class="btn btn-outline btn-compacto" data-i18n="common.clear">Limpiar</a>
        </div>
      </form>

      <div class="tabla-region" role="region" aria-label="Historial de Informes de Administradores de Planta" data-i18n-aria="m5.reports_history_title" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th scope="col" data-i18n="m5.col_datetime">Fecha / Hora</th>
              <th scope="col" data-i18n="m6.col_plant">Planta</th>
              <th scope="col" data-i18n="m5.col_origin">Origen</th>
              <th scope="col" data-i18n="m5.col_responsible">Responsable</th>
              <th scope="col" class="num" data-i18n="m5.col_actions_count">Acciones</th>
              <th scope="col" data-i18n="m1.col_actions">Opciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($informesPlanta ?? collect()) as $inf)
              @php
                $esAutoInf = $inf->origen === 'automatico_24h';
                $metaInf = ($inf->planta?->codigo ?? 'PLT') . ' · ' . ($inf->planta?->nombre ?? 'Planta') . ' · ' . ($inf->created_at?->format('d/m/Y H:i') ?? '') . ' · ' . ($inf->emisor?->name ?? 'Sistema 24h');
              @endphp
              <tr>
                <td class="num-tabular">{{ $inf->created_at?->format('d/m/Y H:i') }}</td>
                <td>
                  <strong>{{ $inf->planta?->codigo ?? 'PLT' }} · {{ $inf->planta?->nombre ?? 'Planta' }}</strong>
                  <small>{{ $inf->resumen_titulo }}</small>
                </td>
                <td>
                  <span class="chip {{ $esAutoInf ? 'chip-celeste' : 'chip-verde' }}" data-i18n="{{ $esAutoInf ? 'm5.origin_auto' : 'm5.origin_manual' }}">
                    {{ $esAutoInf ? 'Automático 24h' : 'Manual (Encargado)' }}
                  </span>
                </td>
                <td>{{ $inf->emisor?->name ?? 'Sistema automático' }}</td>
                <td class="num num-tabular">{{ (int) ($inf->total_acciones ?? 0) }}</td>
                <td>
                  <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                    <button type="button" class="btn-micro js-ver-informe-planta"
                      data-titulo="{{ $inf->resumen_titulo }}"
                      data-meta="{{ $metaInf }}"
                      data-contenido="{{ $inf->contenido_texto }}"
                      data-download-url="{{ route('panel.estadisticas.informes.download', $inf->id) }}">
                      <span data-i18n="m5.btn_view_txt">Ver informe .txt</span>
                    </button>
                    <a href="{{ route('panel.estadisticas.informes.download', $inf->id) }}" class="btn-micro">
                      {!! $icoDescarga !!} <span data-i18n="m5.btn_download_txt">Descargar .txt</span>
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" style="text-align: center; padding: 1.5rem; color: var(--color-graphite);" data-i18n="m5.reports_empty">No hay informes de actividad registrados con estos filtros.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @elseif($usuario->cargo === 'encargado_planta' || $usuario->tienePermiso('admin.usuarios'))
    <div class="panel-card" id="card-enviar-informe-planta" style="margin-top: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
          <h3 style="margin-bottom: 0.25rem;" data-i18n="m5.send_report_title">Enviar informe manual de actividad a Administración</h3>
          <p style="margin: 0;" data-i18n="m5.send_report_sub">Compila la bitácora y métricas de las últimas 24 horas de {{ $plantaActiva->nombre }} y la envía al Administrador Total.</p>
        </div>
        <form id="form-enviar-informe-planta" method="POST" action="{{ route('panel.informes_planta.enviar') }}" class="js-confirm-form" data-confirm-kind="send_plant_report" data-planta="{{ $plantaActiva->nombre }}">
          @csrf
          <button type="submit" class="btn btn-ember" data-i18n="m5.btn_send_report">Enviar informe de planta</button>
        </form>
      </div>
    </div>
  @endif
</section>
