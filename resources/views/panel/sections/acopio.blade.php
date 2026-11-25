<section id="sec-acopio" class="tab-panel" aria-label="Acopio por voz y tacto" data-i18n-aria="acopio.section_aria" hidden>
  <div class="{{ $usuario->tienePermiso('acopio.crear') ? 'panel-grid-2' : '' }}">
    @if($usuario->tienePermiso('acopio.crear'))
      <div class="panel-card on-fog">
        <h2 data-i18n="m1.title">Registro diario de recepción</h2>
        <p data-i18n="m1.sub">Dicte por voz con confirmación hablada o ingrese la cantidad de forma manual (mín. 1.0 L).</p>

        <button type="button" id="btn-voz" class="btn btn-ember btn-grande btn-bloque btn-voz" aria-pressed="false">
          {!! $icoMic !!}
          <span id="txt-btn-voz" data-i18n="m1.voice_btn">Dictar acopio por voz</span>
        </button>
        <div id="transcripcion-voz" class="transcripcion" role="status" aria-live="polite" data-i18n="m1.voice_sample">Ejemplo: “Don Eloy Condori, 35 litros”</div>

        <form method="POST" action="{{ route('panel.acopio') }}" id="form-acopio" data-sync-url="{{ route('panel.acopio.sync') }}">
          @csrf
          <input type="hidden" name="metodo" id="input-metodo" value="manual">
          <input type="hidden" name="frase_dictada" id="input-frase" value="">

          <div class="form-grupo">
            <label for="select-proveedor" class="form-label" data-i18n="m1.producer">Ganadero / productor</label>
            <select name="proveedor_id" id="select-proveedor" class="select-lat" required>
              <option value="" data-i18n="m1.select_producer">Seleccione el ganadero…</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->codigo }} - {{ $prov->nombre }} ({{ $prov->sector }})</option>
              @endforeach
            </select>
          </div>

          <div class="grid-form-2">
            <div class="form-grupo">
              <label for="input-litros" class="form-label" data-i18n="m1.liters">Volumen en litros (L)</label>
              <input type="number" step="0.1" min="1" max="5000" inputmode="decimal" name="litros" id="input-litros" class="input-lat input-litros" required placeholder="0.0">
            </div>
            <div class="form-grupo">
              <label for="input-fecha" class="form-label">Fecha de acopio</label>
              <input type="date" name="fecha" id="input-fecha" class="input-lat" value="{{ old('fecha', Carbon\Carbon::today()->toDateString()) }}" max="{{ Carbon\Carbon::today()->toDateString() }}" required>
            </div>
          </div>

          <div class="form-grupo" role="group" aria-labelledby="botonera-etq">
            <span class="botonera-etq" id="botonera-etq" data-i18n="m1.touch_pad">Botonera táctil rápida (suma litros)</span>
            <div class="botonera" role="group" aria-label="Botonera táctil rápida" data-i18n-aria="acopio.keypad_aria">
              @foreach([10, 15, 20, 25, 30, 35, 40, 50] as $lit)
                <button type="button" data-litros="{{ $lit }}" aria-label="Sumar {{ $lit }} litros" data-i18n-aria="panel.sum_liters_aria">+{{ $lit }}</button>
              @endforeach
            </div>
          </div>

          <button type="submit" class="btn btn-ocean btn-bloque btn-grande" data-i18n="m1.save_btn">Guardar entrega en tina</button>
        </form>
      </div>
    @endif

    @if($usuario->tienePermiso('acopio.ver'))
      <div class="panel-tabla-wrap">
        <div class="panel-tabla-cab">
          @if($usuario->tienePermiso('acopio.crear'))
            <h3>
              @if($hayFiltrosAcopio)
                Acopios filtrados ({{ $acopiosHoy->count() }})
              @else
                <span data-i18n="m1.today_list">Entregas de hoy</span> ({{ Carbon\Carbon::today()->format('d/m/Y') }})
              @endif
            </h3>
          @else
            <h2>
              @if($hayFiltrosAcopio)
                Acopios filtrados ({{ $acopiosHoy->count() }})
              @else
                <span data-i18n="m1.today_list">Entregas de hoy</span> ({{ Carbon\Carbon::today()->format('d/m/Y') }})
              @endif
            </h2>
          @endif
          <span class="chip chip-celeste num-tabular"><span data-i18n="m1.total">Total:</span> {{ number_format($litrosHoy, 1) }} L</span>
        </div>

        <form method="GET" action="{{ route('panel') }}#sec-acopio" class="acopio-filtros-bar" aria-label="Filtrar acopios">
          <div>
            <label for="f-acopio-desde" class="form-label">Desde fecha</label>
            <input type="date" id="f-acopio-desde" name="acopio_fecha_desde" class="input-lat" value="{{ $filtrosAcopio['fecha_desde'] }}">
          </div>
          <div>
            <label for="f-acopio-hasta" class="form-label">Hasta fecha</label>
            <input type="date" id="f-acopio-hasta" name="acopio_fecha_hasta" class="input-lat" value="{{ $filtrosAcopio['fecha_hasta'] }}">
          </div>
          <div>
            <label for="f-acopio-prov" class="form-label">Productor</label>
            <select id="f-acopio-prov" name="acopio_proveedor_id" class="select-lat">
              <option value="">Todos los productores</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}" @selected((string) $filtrosAcopio['proveedor_id'] === (string) $prov->id)>{{ $prov->codigo }} · {{ $prov->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="f-acopio-lmin" class="form-label">Litros mín.</label>
            <input type="number" step="0.5" min="0" id="f-acopio-lmin" name="acopio_litros_min" class="input-lat" value="{{ $filtrosAcopio['litros_min'] }}" placeholder="Ej: 10">
          </div>
          <div>
            <label for="f-acopio-lmax" class="form-label">Litros máx.</label>
            <input type="number" step="0.5" min="0" id="f-acopio-lmax" name="acopio_litros_max" class="input-lat" value="{{ $filtrosAcopio['litros_max'] }}" placeholder="Ej: 100">
          </div>
          <div class="acopio-filtros-btns">
            <button type="submit" class="btn btn-ocean btn-compacto">Filtrar</button>
            @if($hayFiltrosAcopio)
              <a href="{{ route('panel') }}#sec-acopio" class="btn btn-outline btn-compacto">Hoy</a>
            @endif
          </div>
        </form>

        <div class="tabla-region" role="region" aria-label="Entregas de leche" data-i18n-aria="acopio.table_aria" tabindex="0">
          <table class="tabla-plana">
            <thead>
              <tr>
                <th scope="col">Fecha</th>
                <th scope="col" data-i18n="m1.col_time">Hora</th>
                <th scope="col" data-i18n="m1.col_producer">Productor</th>
                <th scope="col" class="num" data-i18n="m1.col_liters">Litros</th>
                <th scope="col" data-i18n="m1.col_method">Método</th>
                <th scope="col" data-i18n="m1.col_quality">Calidad</th>
                @if($usuario->tienePermiso('acopio.editar'))
                  <th scope="col" data-i18n="m1.col_actions">Acciones</th>
                @endif
              </tr>
            </thead>
            <tbody id="tbody-acopios-hoy">
              @forelse($acopiosHoy as $acopio)
                <tr>
                  <td class="num-tabular">{{ $acopio->fecha?->format('d/m/Y') }}</td>
                  <td class="num-tabular">{{ substr($acopio->hora, 0, 5) }}</td>
                  <td><strong>{{ $acopio->proveedor->nombre }}</strong><small>{{ $acopio->proveedor->sector }}</small></td>
                  <td class="num" style="font-size: 1.1rem;">{{ number_format($acopio->litros, 1) }}</td>
                  <td>
                    @if($acopio->metodo === 'voz')
                      <span class="chip chip-celeste">{!! $icoMicS !!} <span data-i18n="common.voice">Voz</span></span>
                    @else
                      <span class="chip chip-fog">{!! $icoTec !!} <span data-i18n="common.manual">Manual</span></span>
                    @endif
                  </td>
                  <td>
                    @if($acopio->calidad === 'buena')
                      <span class="chip chip-verde">{!! $icoOk !!} <span data-i18n="common.approved">Aprobada</span></span>
                    @elseif($acopio->calidad === 'acida')
                      <span class="chip chip-amarillo">{!! $icoAlerta !!} <span data-i18n="common.acid_chip">Ácida (quesillo)</span></span>
                    @else
                      <span class="chip chip-amarillo" data-i18n="common.pending">Pendiente</span>
                    @endif
                  </td>
                  @if($usuario->tienePermiso('acopio.editar'))
                    <td>
                      <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                        <button type="button" class="btn-micro js-edit-acopio"
                          data-id="{{ $acopio->id }}"
                          data-proveedor="{{ $acopio->proveedor_id }}"
                          data-litros="{{ $acopio->litros }}"
                          data-calidad="{{ $acopio->calidad }}"
                          data-metodo="{{ $acopio->metodo }}"
                          data-fecha="{{ $acopio->fecha?->format('Y-m-d') }}"
                          data-hora="{{ substr($acopio->hora, 0, 5) }}">
                          {!! $icoEditar !!} <span data-i18n="common.edit">Editar</span>
                        </button>
                        <form method="POST" action="{{ route('panel.acopio.delete', $acopio->id) }}"
                          class="js-confirm-form"
                          data-confirm-kind="delete"
                          data-detalle-tipo="acopio"
                          data-detalle-nombre="{{ $acopio->proveedor->nombre }}"
                          data-detalle="acopio de {{ $acopio->proveedor->nombre }}">
                          @csrf @method('DELETE')
                          <button type="submit" class="btn-micro btn-micro-danger" data-i18n="common.delete">Eliminar</button>
                        </form>
                      </div>
                    </td>
                  @endif
                </tr>
              @empty
                <tr class="is-empty-acopios"><td colspan="{{ $usuario->tienePermiso('acopio.editar') ? 7 : 6 }}" style="text-align: center; padding: 2rem; color: var(--color-graphite);" data-i18n="m1.empty">No hay entregas registradas para este criterio.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    @endif
  </div>
</section>
