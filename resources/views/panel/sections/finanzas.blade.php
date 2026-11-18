<section id="sec-quincena" class="tab-panel" aria-label="Liquidación quincenal y ventas" hidden>
  <div class="panel-card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
      <div>
        <h2><span data-i18n="m4.title">Liquidación quincenal de proveedores</span> ({{ $desde }} al {{ $hasta }})</h2>
        <p style="margin: 0;" data-i18n="m4.sub">Cálculo neto transparente: bruto menos adelantos en efectivo o queso. <strong>Comisión: S/ 0.00.</strong></p>
      </div>
      <div style="display: flex; flex-wrap: wrap; gap: 0.65rem;">
        @if($usuario->tieneAlgunPermiso(['acopio.editar', 'finanzas.editar', 'acopio.proveedores']))
          <button type="button" class="btn btn-outline" data-open-modal="modal-editar-datos">
            {!! $icoEditar !!} <span data-i18n="panel.btn_edit_data">Editar datos</span>
          </button>
        @endif
        @if($usuario->tienePermiso('finanzas.exportar'))
          <a href="{{ route('panel.exportar') }}" class="btn btn-ember">
            {!! $icoDescarga !!} <span data-i18n="panel.btn_download_excel">Descargar balance en Excel (.xlsx)</span>
          </a>
        @endif
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

    @if($usuario->tienePermiso('finanzas.registrar'))
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
    @endif
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
            @if($usuario->tienePermiso('finanzas.editar'))
              <th>Acciones</th>
            @endif
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
              @if($usuario->tienePermiso('finanzas.editar'))
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
              @endif
            </tr>
          @empty
            <tr><td colspan="{{ $usuario->tienePermiso('finanzas.editar') ? 7 : 6 }}" style="text-align: center; padding: 1.5rem;">No hay movimientos registrados.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>
