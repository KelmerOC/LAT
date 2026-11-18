<section id="sec-acopio" class="tab-panel" aria-label="Acopio por voz y tacto" hidden>
  <div class="{{ $usuario->tienePermiso('acopio.crear') ? 'panel-grid-2' : '' }}">
    @if($usuario->tienePermiso('acopio.crear'))
      <div class="panel-card on-fog">
        <h2 data-i18n="m1.title">Registro diario de recepción</h2>
        <p data-i18n="m1.sub">Dicte por voz con confirmación hablada o ingrese la cantidad de forma manual.</p>

        <button type="button" id="btn-voz" class="btn btn-ember btn-grande btn-bloque btn-voz" aria-pressed="false">
          {!! $icoMic !!}
          <span id="txt-btn-voz" data-i18n="m1.voice_btn">Dictar acopio por voz</span>
        </button>
        <div id="transcripcion-voz" class="transcripcion" role="status" aria-live="polite" data-i18n="m1.voice_sample">Ejemplo: “Don Eloy Condori, 35 litros”</div>

        <form method="POST" action="{{ route('panel.acopio') }}" id="form-acopio">
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
    @endif

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
              @if($usuario->tienePermiso('acopio.editar'))
                <th scope="col" data-i18n="m1.col_actions">Acciones</th>
              @endif
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
                    <button type="button" class="btn-micro js-edit-acopio"
                      data-id="{{ $acopio->id }}"
                      data-proveedor="{{ $acopio->proveedor_id }}"
                      data-litros="{{ $acopio->litros }}"
                      data-calidad="{{ $acopio->calidad }}"
                      data-metodo="{{ $acopio->metodo }}"
                      data-hora="{{ substr($acopio->hora, 0, 5) }}">
                      {!! $icoEditar !!} <span data-i18n="common.edit">Editar</span>
                    </button>
                  </td>
                @endif
              </tr>
            @empty
              <tr><td colspan="{{ $usuario->tienePermiso('acopio.editar') ? 6 : 5 }}" style="text-align: center; padding: 2rem; color: var(--color-graphite);" data-i18n="m1.empty">No hay entregas registradas hoy.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
