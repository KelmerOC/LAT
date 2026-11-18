<section id="sec-calidad" class="tab-panel" aria-label="Control de calidad sanitaria" hidden>
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
            @if($usuario->tienePermiso('calidad.evaluar'))
              <th scope="col" data-i18n="m2.col_triage">Triage inmediato</th>
            @endif
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
              @if($usuario->tienePermiso('calidad.evaluar'))
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
              @endif
            </tr>
          @empty
            <tr><td colspan="{{ $usuario->tienePermiso('calidad.evaluar') ? 5 : 4 }}" style="text-align: center; padding: 2rem; color: var(--color-graphite);">No hay entregas para clasificar hoy.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>
