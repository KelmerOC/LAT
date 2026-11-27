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
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
          <button type="button" class="btn btn-outline" id="btn-recetas-base" data-open-modal="modal-recetas-base" data-i18n="m3.base_recipes_btn">Recetas base</button>
          <button type="button" class="btn btn-ember" id="btn-agregar-tina" data-open-modal="modal-nueva-tina" data-i18n="m3.add_tina">
            + Agregar nueva tina
          </button>
        </div>
      @endif
    </div>
  </div>

  @php
    $resumenLeche = $resumenLecheTinas ?? [];
    $resumenTotal = (float) ($resumenLeche['total_litros'] ?? 0);
    $resumenOptLitros = (float) ($resumenLeche['optima']['litros'] ?? 0);
    $resumenOptEntregas = (int) ($resumenLeche['optima']['entregas'] ?? 0);
    $resumenAcidLitros = (float) ($resumenLeche['acida']['litros'] ?? 0);
    $resumenAcidEntregas = (int) ($resumenLeche['acida']['entregas'] ?? 0);
    $resumenPendLitros = (float) ($resumenLeche['pendiente']['litros'] ?? 0);
    $resumenPendEntregas = (int) ($resumenLeche['pendiente']['entregas'] ?? 0);
  @endphp
  <div id="aviso-calidad-tinas" class="panel-card tina-calidad-aviso" role="region" aria-labelledby="tina-calidad-aviso-title" style="margin-bottom: 1.5rem;">
    <h3 id="tina-calidad-aviso-title" data-i18n="m3.quality_notice_title">Leche recibida desde Control Sanitario (hoy)</h3>
    <p class="tina-calidad-aviso-sub" data-i18n="m3.quality_notice_sub">Según la calidad sanitaria clasificada en recepción, cada volumen tiene un destino de tina recomendado:</p>

    @if($resumenTotal > 0)
      <div class="tina-calidad-grid">
        <div class="tina-calidad-item is-optima">
          <span class="chip chip-verde" data-i18n="m3.quality_optima_label">Leche óptima (aprobada)</span>
          <div class="tina-calidad-valor">
            {{ number_format($resumenOptLitros, 1) }} L
            <small>({{ $resumenOptEntregas }} <span data-i18n="m3.deliveries_count">entregas</span>)</small>
          </div>
          <p class="tina-calidad-uso" data-i18n="m3.quality_optima_use">Uso asignado: Tina principal (Queso maduro / Yogur)</p>
        </div>

        <div class="tina-calidad-item is-acida">
          <span class="chip chip-amarillo" data-i18n="m3.quality_acida_label">Leche con acidez (74°)</span>
          <div class="tina-calidad-valor">
            {{ number_format($resumenAcidLitros, 1) }} L
            <small>({{ $resumenAcidEntregas }} <span data-i18n="m3.deliveries_count">entregas</span>)</small>
          </div>
          <p class="tina-calidad-uso" data-i18n="m3.quality_acida_use">Uso asignado: Tina de Quesillo fresco (4 días) — cero desperdicio</p>
        </div>

        @if($resumenPendLitros > 0)
          <div class="tina-calidad-item is-pendiente">
            <span class="chip chip-fog" data-i18n="m3.quality_pending_label">Pendiente de prueba sanitaria</span>
            <div class="tina-calidad-valor">
              {{ number_format($resumenPendLitros, 1) }} L
              <small>({{ $resumenPendEntregas }} <span data-i18n="m3.deliveries_count">entregas</span>)</small>
            </div>
            <p class="tina-calidad-uso" data-i18n="m3.quality_pending_use">Clasifique esta leche en 2. Calidad sanitaria antes de cargarla.</p>
          </div>
        @endif
      </div>
    @else
      <p class="tina-calidad-empty" data-i18n="m3.quality_empty">Aún no hay leche recibida ni clasificada hoy en Control Sanitario.</p>
    @endif
    <div id="tinas-calidad-alertas-list" class="tinas-calidad-alertas" hidden></div>
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
            @php $esPresetTipo = method_exists($tina, 'esTipoPreestablecido') ? $tina->esTipoPreestablecido() : isset($tiposTinaLabels[$tina->tipo]); @endphp
            <span class="chip chip-celeste">{{ $tina->codigo }} · @if($esPresetTipo)<span data-i18n="tina.type_{{ $tina->tipo }}">{{ $tiposTinaLabels[$tina->tipo] ?? $tina->tipo }}</span>@else<span>{{ $tina->tipo }}</span>@endif</span>
            <h3 style="margin-top: 0.45rem; font-size: 1.15rem;">{{ $tina->nombre }}</h3>
            <p style="font-size: 0.85rem; color: var(--color-graphite); margin: 0;">
              <span data-i18n="m3.capacity">Capacidad:</span> <strong>{{ number_format($tina->capacidad_litros, 0) }} L</strong> ·
              <span data-i18n="m3.state">Estado:</span> <strong data-i18n="{{ $tina->activa ? 'm3.active' : 'm3.inactive' }}">{{ $tina->activa ? 'Activa' : 'Inactiva' }}</strong>
            </p>
          </div>
          @if($usuario->tienePermiso('tinas.gestionar'))
            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
              <button type="button" class="btn-micro js-edit-tina"
                data-id="{{ $tina->id }}"
                data-codigo="{{ $tina->codigo }}"
                data-nombre="{{ $tina->nombre }}"
                data-capacidad="{{ $tina->capacidad_litros }}"
                data-tipo="{{ $tina->tipo }}"
                data-activa="{{ $tina->activa ? '1' : '0' }}">
                {!! $icoEditar !!} <span data-i18n="m3.edit_tina">Editar tina</span>
              </button>
              @if(! $lote)
                <form method="POST" action="{{ route('panel.tinas.delete', $tina->id) }}"
                  class="js-confirm-form"
                  data-confirm-kind="delete"
                  data-detalle-tipo="tina"
                  data-detalle-nombre="{{ $tina->codigo }} · {{ $tina->nombre }}"
                  data-detalle="tina {{ $tina->codigo }} · {{ $tina->nombre }}">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn-micro btn-micro-danger" data-i18n="common.delete">Eliminar</button>
                </form>
              @else
                <button type="button" class="btn-micro btn-micro-danger" disabled aria-disabled="true" title="Finalice el lote activo para eliminar esta tina" data-i18n-title="m3.delete_blocked_active" data-i18n="common.delete">Eliminar</button>
              @endif
            </div>
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
            @if(! empty($lote->receta_nombre))
              <small class="tina-receta-tag">{{ $lote->receta_nombre }}</small>
            @endif
            <div style="font-size: 2.3rem; font-weight: 300; font-variant-numeric: tabular-nums; margin: 0.4rem 0;">
              {{ number_format($lote->temperatura_actual, 1) }} °C
              <small style="font-size: 0.95rem; color: #bae6fd;">({{ number_format($lote->litros, 1) }} L)</small>
            </div>
            <div class="barra-termica" style="margin: 0.5rem 0 0;">
              <div style="width: {{ min(100, max(0, $tTerm['porcentaje'])) }}%;"></div>
            </div>
            @php $finIso = method_exists($lote, 'finEstimadoEn') ? $lote->finEstimadoEn()?->toIso8601String() : null; @endphp
            <div class="tina-timer-box" data-fin-iso="{{ $finIso }}" data-lote-id="{{ $lote->id }}" data-tina-codigo="{{ $tina->codigo }}">
              <span data-i18n="m3.timer_label">Cronómetro:</span>
              <strong class="js-tina-countdown num-tabular">--:--</strong>
              <small>({{ (int) ($lote->duracion_minutos ?? 45) }} min)</small>
            </div>
          </div>

          <div class="tina-receta-mini">
            <div><span data-i18n="m3.rennet">Cuajo líquido</span><strong>{{ number_format((float) $lote->cuajo_ml, 2) }} ml</strong></div>
            <div><span data-i18n="m3.salt">Sal común</span><strong>{{ number_format((float) $lote->sal_kg, 2) }} kg</strong></div>
            <div><span data-i18n="m3.culture">Cultivo</span><strong>{{ number_format((float) $lote->cultivo_g, 2) }} g</strong></div>
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
              <div>
                <label class="form-label" style="font-size: 0.78rem;" for="dur-{{ $lote->id }}" data-i18n="m3.lbl_timer_min">Tiempo (min)</label>
                <input type="number" step="1" min="1" max="1440" id="dur-{{ $lote->id }}" name="duracion_minutos" class="input-lat" value="{{ (int) ($lote->duracion_minutos ?? 45) }}" style="min-height: 42px; padding: 0.45rem 0.65rem;">
              </div>
              <button type="submit" class="btn btn-ocean btn-compacto" data-i18n="m3.update_sensor">Actualizar</button>
              <details class="tina-receta-edit-details">
                <summary data-i18n="m3.edit_recipe_toggle">Ajustar proporciones de receta</summary>
                <div class="grid-form-3" style="margin-top: 0.65rem;">
                  <div>
                    <label class="form-label" style="font-size: 0.78rem;" for="cuajo-{{ $lote->id }}" data-i18n="m3.rennet_ml">Cuajo (ml)</label>
                    <input type="number" step="0.01" min="0" max="10000" id="cuajo-{{ $lote->id }}" name="cuajo_ml" class="input-lat" value="{{ number_format((float) $lote->cuajo_ml, 2, '.', '') }}" style="min-height: 40px; padding: 0.4rem 0.6rem;">
                  </div>
                  <div>
                    <label class="form-label" style="font-size: 0.78rem;" for="sal-{{ $lote->id }}" data-i18n="m3.salt_kg">Sal (kg)</label>
                    <input type="number" step="0.01" min="0" max="10000" id="sal-{{ $lote->id }}" name="sal_kg" class="input-lat" value="{{ number_format((float) $lote->sal_kg, 2, '.', '') }}" style="min-height: 40px; padding: 0.4rem 0.6rem;">
                  </div>
                  <div>
                    <label class="form-label" style="font-size: 0.78rem;" for="cult-{{ $lote->id }}" data-i18n="m3.culture_g">Cultivo (g)</label>
                    <input type="number" step="0.01" min="0" max="10000" id="cult-{{ $lote->id }}" name="cultivo_g" class="input-lat" value="{{ number_format((float) $lote->cultivo_g, 2, '.', '') }}" style="min-height: 40px; padding: 0.4rem 0.6rem;">
                  </div>
                </div>
              </details>
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
          @php
            $puedeOperarTina = $tina->activa && $usuario->tienePermiso('tinas.operar');
            $litrosOpt = (float) ($resumenLecheTinas['optima']['litros'] ?? 0);
            $litrosAcid = (float) ($resumenLecheTinas['acida']['litros'] ?? 0);
            if ($tina->tipo === 'quesillo') {
                $litrosSegunCalidad = $litrosAcid;
                $hintKey = 'm3.vat_milk_hint_acida';
                $hintFallback = 'Leche con acidez disponible para quesillo:';
            } elseif ($tina->tipo === 'multiproposito') {
                $litrosSegunCalidad = $litrosOpt + $litrosAcid;
                $hintKey = 'm3.vat_milk_hint_multi';
                $hintFallback = 'Leche clasificada disponible:';
            } else {
                $litrosSegunCalidad = $litrosOpt;
                $hintKey = 'm3.vat_milk_hint_optima';
                $hintFallback = 'Leche óptima disponible para queso/yogur:';
            }
            $litrosSugeridos = $litrosSegunCalidad >= 5
                ? min($tina->capacidad_litros, $litrosSegunCalidad)
                : min($tina->capacidad_litros, max(50, $litrosHoy ?: 100));
          @endphp
          <div class="tina-idle-box">
            <p style="margin: 0; font-weight: 600; color: var(--color-graphite);" data-i18n="m3.no_batch">Tina libre (sin lote en proceso)</p>
            @if($puedeOperarTina)
              <small class="tina-idle-milk-hint"><span data-i18n="{{ $hintKey }}">{{ $hintFallback }}</span> <strong>{{ number_format($litrosSegunCalidad, 1) }} L</strong></small>
            @endif
          </div>
          @if($puedeOperarTina)
            <button type="button" class="btn btn-ember btn-bloque js-open-iniciar-tina"
              data-tina-id="{{ $tina->id }}"
              data-tina-codigo="{{ $tina->codigo }}"
              data-tina-nombre="{{ $tina->nombre }}"
              data-tina-capacidad="{{ $tina->capacidad_litros }}"
              data-tina-tipo="{{ $tina->tipo }}"
              data-action-url="{{ route('panel.tinas.lote', $tina->id) }}"
              data-i18n="m3.start_batch">
              Iniciar lote en tina
            </button>
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
