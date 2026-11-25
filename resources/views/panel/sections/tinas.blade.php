@php
  $estadosTinaLabels = [
    'libre' => 'Libre',
    'pasteurizando' => 'Pasteurizando',
    'enfriando' => 'Enfriando',
    'lista_para_cuajo' => 'Lista para cuajo',
    'muy_fria' => 'Muy fría',
  ];
@endphp

<section id="sec-tina" class="tab-panel" aria-label="Control de múltiples tinas y receta" data-i18n-aria="tina.section_aria" hidden>
  <div class="panel-card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
      <div>
        <h2 data-i18n="m3.title">Control de múltiples tinas y dosificación</h2>
        <p style="margin: 0;" data-i18n="m3.sub">Agregue, edite y supervise la curva térmica y receta automática de cada tina de la planta.</p>
      </div>
      @if($usuario->tienePermiso('tinas.gestionar'))
        <button type="button" class="btn btn-ember" id="btn-agregar-tina" data-open-modal="modal-nueva-tina" data-i18n="m3.add_tina">
          + Agregar nueva tina
        </button>
      @endif
    </div>
  </div>

  <div class="tinas-grid">
    @forelse($tinasPanel as $item)
      @php
        $tina = $item->tina;
        $lote = $item->lote;
        $tTerm = $item->termica;
        $tRec = $item->receta;
      @endphp
      <article class="tina-card">
        <div class="tina-card-header">
          <div>
            <span class="chip chip-celeste">{{ $tina->codigo }} · <span data-i18n="tina.type_{{ $tina->tipo }}">{{ $tiposTinaLabels[$tina->tipo] ?? $tina->tipo }}</span></span>
            <h3 style="margin-top: 0.45rem; font-size: 1.15rem;">{{ $tina->nombre }}</h3>
            <p style="font-size: 0.85rem; color: var(--color-graphite); margin: 0;">
              <span data-i18n="m3.capacity">Capacidad:</span> <strong>{{ number_format($tina->capacidad_litros, 0) }} L</strong> ·
              <span data-i18n="m3.state">Estado:</span> <strong data-i18n="{{ $tina->activa ? 'm3.active' : 'm3.inactive' }}">{{ $tina->activa ? 'Activa' : 'Inactiva' }}</strong>
            </p>
          </div>
          @if($usuario->tienePermiso('tinas.gestionar'))
            <button type="button" class="btn-micro js-edit-tina"
              data-id="{{ $tina->id }}"
              data-codigo="{{ $tina->codigo }}"
              data-nombre="{{ $tina->nombre }}"
              data-capacidad="{{ $tina->capacidad_litros }}"
              data-tipo="{{ $tina->tipo }}"
              data-activa="{{ $tina->activa ? '1' : '0' }}">
              {!! $icoEditar !!} <span data-i18n="m3.edit_tina">Editar tina</span>
            </button>
          @endif
        </div>

        @if($lote && $tTerm && $tRec)
          <div class="tina-lote-box">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
              <small style="color: #bae6fd; font-weight: 600;"><span data-i18n="m3.batch_label">Lote</span> {{ $lote->codigo }}</small>
              <span class="chip {{ $tTerm['apta'] ? 'chip-verde' : 'chip-amarillo' }}">
                <span data-i18n="tina.state_{{ $tTerm['estado'] }}">{{ $estadosTinaLabels[$tTerm['estado']] ?? str_replace('_', ' ', $tTerm['estado']) }}</span> · {{ $tTerm['porcentaje'] }}%
              </span>
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
            <div><span data-i18n="m3.rennet">Cuajo líquido</span><strong>{{ $tRec['cuajo_ml'] }} ml</strong></div>
            <div><span data-i18n="m3.salt">Sal común</span><strong>{{ $tRec['sal_kg'] }} kg</strong></div>
            <div><span data-i18n="m3.culture">Cultivo</span><strong>{{ $tRec['cultivo_g'] }} g</strong></div>
            <div><span data-i18n="m3.molds_est">Moldes est.</span><strong>{{ $tRec['moldes_estimados'] }} <span data-i18n="panel.unit_und">und</span></strong></div>
          </div>

          @if($usuario->tienePermiso('tinas.operar'))
            <form method="POST" action="{{ route('panel.tina', $lote->id) }}" class="grid-form-tina-sensor">
              @csrf
              <div>
                <label class="form-label" style="font-size: 0.78rem;" for="temp-{{ $lote->id }}" data-i18n="m3.lbl_sensor">Sensor (°C)</label>
                <input type="number" step="0.5" id="temp-{{ $lote->id }}" name="temperatura" class="input-lat" value="{{ $lote->temperatura_actual }}" style="min-height: 42px; padding: 0.45rem 0.65rem;">
              </div>
              <div>
                <label class="form-label" style="font-size: 0.78rem;" for="lit-{{ $lote->id }}" data-i18n="m3.lbl_liters">Litros (L)</label>
                <input type="number" step="0.5" min="5" id="lit-{{ $lote->id }}" name="litros" class="input-lat" value="{{ $lote->litros }}" style="min-height: 42px; padding: 0.45rem 0.65rem;">
              </div>
              <button type="submit" class="btn btn-ocean btn-compacto" data-i18n="m3.update_sensor">Actualizar</button>
            </form>

            <form method="POST" action="{{ route('panel.tina', $lote->id) }}"
              class="js-confirm-form tina-finish-form"
              data-confirm-kind="finish_batch"
              data-codigo="{{ $lote->codigo }}">
              @csrf
              <div style="flex: 1;">
                <label class="form-label" style="font-size: 0.78rem;" for="moldes-{{ $lote->id }}" data-i18n="m3.lbl_molds_obtained">Moldes obtenidos (para cerrar lote)</label>
                <input type="number" min="0" id="moldes-{{ $lote->id }}" name="moldes" class="input-lat" value="{{ $tRec['moldes_estimados'] }}" style="min-height: 42px; padding: 0.45rem 0.65rem;">
              </div>
              <button type="submit" class="btn btn-verde btn-compacto" data-i18n="m3.finish_batch">Finalizar lote</button>
            </form>
          @endif
        @else
          <div class="tina-idle-box">
            <p style="margin: 0; font-weight: 600; color: var(--color-graphite);" data-i18n="m3.no_batch">Tina libre (sin lote en proceso)</p>
          </div>
          @if($tina->activa && $usuario->tienePermiso('tinas.operar'))
            <form method="POST" action="{{ route('panel.tinas.lote', $tina->id) }}" class="grid-form-2" style="align-items: end;">
              @csrf
              <div>
                <label class="form-label" style="font-size: 0.78rem;" for="start-lit-{{ $tina->id }}"><span data-i18n="m3.lbl_liters_load">Litros a cargar</span> (<span data-i18n="panel.max_abbr">máx</span> {{ (int) $tina->capacidad_litros }})</label>
                <input type="number" step="0.5" min="5" max="{{ $tina->capacidad_litros }}" id="start-lit-{{ $tina->id }}" name="litros" class="input-lat" value="{{ min($tina->capacidad_litros, max(50, $litrosHoy ?: 100)) }}" required style="min-height: 42px;">
              </div>
              <div>
                <label class="form-label" style="font-size: 0.78rem;" for="start-temp-{{ $tina->id }}" data-i18n="m3.lbl_temp_init">Temp. inicial (°C)</label>
                <input type="number" step="0.5" min="4" max="95" id="start-temp-{{ $tina->id }}" name="temperatura_actual" class="input-lat" value="72.0" required style="min-height: 42px;">
              </div>
              <button type="submit" class="btn btn-ember btn-bloque" style="grid-column: 1 / -1;" data-i18n="m3.start_batch">Iniciar lote en tina</button>
            </form>
          @endif
        @endif
      </article>
    @empty
      <div class="panel-card">
        <p style="margin: 0; color: var(--color-graphite);" data-i18n="dash.no_vats">Sin tinas registradas.</p>
      </div>
    @endforelse
  </div>
</section>
