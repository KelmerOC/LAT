<section id="sec-quincena" class="tab-panel" aria-label="Liquidación quincenal y ventas" data-i18n-aria="m4.section_aria" hidden>
  <div class="panel-card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
      <div>
        <h2><span data-i18n="m4.title">Liquidación quincenal de proveedores</span> ({{ $desde }} <span data-i18n="panel.connector_to">al</span> {{ $hasta }})</h2>
        <p style="margin: 0;"><span data-i18n="m4.sub">Cálculo neto transparente: bruto menos adelantos en efectivo o queso.</span> <strong data-i18n="m4.sub_comm">Comisión: S/ 0.00.</strong></p>
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

    @if($usuario->tienePermiso('finanzas.ver'))
      <div class="tabla-region" role="region" aria-label="Liquidación quincenal de proveedores" data-i18n-aria="m4.title" tabindex="0" style="margin-bottom: 1.5rem;">
        <table class="tabla-plana" style="min-width: 720px;">
          <thead>
            <tr>
              <th scope="col" data-i18n="m4.col_code">Código</th>
              <th scope="col" data-i18n="m4.col_farmer">Ganadero</th>
              <th scope="col" data-i18n="m4.col_sector">Sector</th>
              <th scope="col" class="num" data-i18n="m1.col_liters">Litros</th>
              <th scope="col" class="num" data-i18n="m4.col_gross">Bruto</th>
              <th scope="col" class="num" data-i18n="m4.col_advances">Adelantos</th>
              <th scope="col" class="num" data-i18n="m4.col_comm">Comisión</th>
              <th scope="col" class="num" style="background: var(--color-sky-wash);" data-i18n="m4.col_net">Neto a pagar</th>
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
              <tr><td colspan="8" style="text-align: center; padding: 2rem; color: var(--color-graphite);" data-i18n="m4.empty_liq">No hay liquidaciones en este período.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    @endif

    @if($usuario->tienePermiso('finanzas.registrar'))
      <div class="panel-card panel-card-celeste on-fog" style="padding: 1.5rem; margin-bottom: 1.25rem;">
        <h3 style="font-size: 1.1rem;" data-i18n="m4.mov_title">Registrar movimiento, venta o ayuda del día</h3>
        <p class="legal" data-i18n="m4.mov_legal">Ley N.° 29733: los pagos en mano por faenas de lavado o volteo no requieren DNI ni datos personales sensibles. Las ventas alimentan las estadísticas de plantas.</p>

        <form method="POST" action="{{ route('panel.movimiento') }}" class="movimiento-form">
          @csrf
          <div class="form-grupo">
            <label for="mov-tipo" class="form-label" data-i18n="m4.lbl_type">Tipo</label>
            <select name="tipo" id="mov-tipo" class="select-lat">
              <option value="venta_producto" data-i18n="m4.mov_type_venta_producto">Venta de producto lácteo (queso / yogur)</option>
              <option value="adelanto_efectivo" data-i18n="m4.mov_type_adelanto_efectivo">Adelanto en efectivo</option>
              <option value="adelanto_queso" data-i18n="m4.mov_type_adelanto_queso">Adelanto en moldes de queso</option>
              <option value="ayuda_del_dia" data-i18n="m4.mov_type_ayuda_del_dia">Pago de ayuda del día (sin DNI)</option>
              <option value="gasto_operativo" data-i18n="m4.mov_type_gasto_operativo">Gasto operativo / insumos</option>
            </select>
          </div>
          <div class="form-grupo">
            <label for="mov-proveedor" class="form-label" data-i18n="m4.lbl_prov">Productor (si aplica)</label>
            <select name="proveedor_id" id="mov-proveedor" class="select-lat">
              <option value="" data-i18n="m4.opt_none_plant">Ninguno / venta o faena externa</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="mov-concepto" class="form-label" data-i18n="m4.lbl_concept">Concepto</label>
            <input type="text" name="concepto" id="mov-concepto" class="input-lat" required maxlength="160" placeholder="Ej: Venta 25 moldes feria Cusco" data-i18n-placeholder="m4.ph_concept">
          </div>
          <div class="form-grupo">
            <label for="mov-unidades" class="form-label" data-i18n="m4.lbl_units">Moldes / Und.</label>
            <input type="number" min="0" name="cantidad_unidades" id="mov-unidades" class="input-lat" value="0">
          </div>
          <div class="form-grupo">
            <label for="mov-monto" class="form-label" data-i18n="m4.lbl_amount">Monto (S/)</label>
            <input type="number" step="0.5" min="0.5" inputmode="decimal" name="monto" id="mov-monto" class="input-lat" required placeholder="0.00">
          </div>
          <button type="submit" class="btn btn-ocean" data-i18n="m4.mov_btn">Asentar movimiento</button>
        </form>
      </div>
    @endif

    @if($usuario->tieneAlgunPermiso(['finanzas.enviar_excel', 'finanzas.registrar']) && $contadoresPlanta->isNotEmpty())
      <div class="panel-card on-fog" style="padding: 1.5rem;">
        <h3 style="font-size: 1.1rem;">Enviar Excel ordenado al Contador</h3>
        <p class="legal">Genera un libro `.xlsx` ordenado por productor, entregas, ventas y liquidación, y lo entrega directamente en la bandeja del contador.</p>

        <form method="POST" action="{{ route('panel.reportes_excel.send') }}" class="movimiento-form">
          @csrf
          <div class="form-grupo">
            <label for="rep-destinatario" class="form-label">Contador destinatario</label>
            <select name="destinatario_id" id="rep-destinatario" class="select-lat" required>
              @foreach($contadoresPlanta as $cont)
                <option value="{{ $cont->id }}">{{ $cont->name }} ({{ $cont->etiquetaCargo() }})</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="rep-tipo" class="form-label">Tipo de reporte</label>
            <select name="tipo_reporte" id="rep-tipo" class="select-lat">
              <option value="ventas_productor">Ventas y liquidación por productor</option>
              <option value="balance_quincenal">Balance quincenal completo</option>
              <option value="acopios_filtrados">Detalle ordenado de acopios</option>
            </select>
          </div>
          <div class="form-grupo">
            <label for="rep-prov" class="form-label">Productor (opcional)</label>
            <select name="proveedor_id" id="rep-prov" class="select-lat">
              <option value="">Todos los productores</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->codigo }} · {{ $prov->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="rep-desde" class="form-label">Desde</label>
            <input type="date" name="fecha_desde" id="rep-desde" class="input-lat" value="{{ $desde }}" required>
          </div>
          <div class="form-grupo">
            <label for="rep-hasta" class="form-label">Hasta</label>
            <input type="date" name="fecha_hasta" id="rep-hasta" class="input-lat" value="{{ $hasta }}" required>
          </div>
          <div class="form-grupo">
            <label for="rep-notas" class="form-label">Nota para el contador</label>
            <input type="text" name="notas" id="rep-notas" class="input-lat" maxlength="255" placeholder="Ej: Cierre quincenal ventas feria">
          </div>
          <button type="submit" class="btn btn-ember">Enviar Excel al contador</button>
        </form>
      </div>
    @endif
  </div>

  @if($usuario->tieneAlgunPermiso(['finanzas.ver', 'finanzas.exportar', 'finanzas.enviar_excel']))
    <div class="panel-tabla-wrap" style="margin-bottom: 1.5rem;">
      <div class="panel-tabla-cab">
        <h3>Bandeja de reportes Excel (Productor / Ventas ↔ Contador)</h3>
        <span class="chip chip-celeste">{{ $reportesExcel->count() }} archivo(s)</span>
      </div>
      <div class="tabla-region" role="region" aria-label="Bandeja de reportes Excel" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th>Fecha envío</th>
              <th>Remitente → Destinatario</th>
              <th>Detalle y período</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reportesExcel as $rep)
              <tr>
                <td class="num-tabular">{{ $rep->created_at?->format('d/m/Y H:i') }}</td>
                <td>
                  <strong>{{ $rep->remitente?->name ?? 'Usuario' }}</strong> → <strong>{{ $rep->destinatario?->name ?? 'Contador' }}</strong>
                  <small>{{ $rep->nombre_archivo }}</small>
                </td>
                <td>
                  <span>{{ str_replace('_', ' ', ucfirst($rep->tipo_reporte)) }} ({{ $rep->fecha_desde?->format('d/m') }} - {{ $rep->fecha_hasta?->format('d/m/Y') }})</span>
                  @if($rep->proveedor || $rep->notas)
                    <small>{{ $rep->proveedor ? 'Productor: '.$rep->proveedor->nombre.' · ' : '' }}{{ $rep->notas }}</small>
                  @endif
                </td>
                <td>
                  <span class="chip {{ $rep->estado === 'revisado' ? 'chip-verde' : 'chip-amarillo' }}">
                    {{ $rep->estado === 'revisado' ? 'Revisado' : 'Enviado' }}
                  </span>
                </td>
                <td>
                  <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                    <a href="{{ route('panel.reportes_excel.download', $rep->id) }}" class="btn-micro">{!! $icoDescarga !!} Descargar .xlsx</a>
                    @if($rep->estado !== 'revisado' && ($usuario->esAdmin() || $rep->destinatario_id === $usuario->id))
                      <form method="POST" action="{{ route('panel.reportes_excel.review', $rep->id) }}">
                        @csrf
                        <button type="submit" class="btn-micro">Marcar revisado</button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" style="text-align: center; padding: 1.5rem; color: var(--color-graphite);">No hay reportes Excel enviados todavía en esta planta.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif

  @if($usuario->tienePermiso('finanzas.ver'))
    <div class="panel-tabla-wrap">
      <div class="panel-tabla-cab">
        <h3 data-i18n="m4.recent_title">Movimientos y ventas recientes</h3>
      </div>
      <div class="tabla-region" role="region" aria-label="Movimientos y ventas recientes" data-i18n-aria="m4.mov_section_aria" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th data-i18n="m4.col_date">Fecha</th>
              <th data-i18n="m4.col_type">Tipo</th>
              <th data-i18n="m4.col_ref">Referencia</th>
              <th data-i18n="m4.col_concept">Concepto</th>
              <th class="num" data-i18n="m4.col_units">Und.</th>
              <th class="num" data-i18n="m4.col_amount">Monto</th>
              @if($usuario->tienePermiso('finanzas.editar'))
                <th data-i18n="m1.col_actions">Acciones</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @forelse($movimientos as $mov)
              <tr>
                <td class="num-tabular">{{ $mov->fecha?->format('d/m/Y') }}</td>
                <td><span class="chip {{ $mov->tipo === 'venta_producto' ? 'chip-verde' : 'chip-fog' }}" data-i18n="m4.mov_type_{{ $mov->tipo }}">{{ $tiposMovLabels[$mov->tipo] ?? $mov->tipo }}</span></td>
                <td>@if($mov->proveedor){{ $mov->proveedor->nombre }}@else<span data-i18n="panel.plant_external">Planta / externo</span>@endif</td>
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
                        {!! $icoEditar !!} <span data-i18n="common.edit">Editar</span>
                      </button>
                      <form method="POST" action="{{ route('panel.movimiento.delete', $mov->id) }}"
                        class="js-confirm-form"
                        data-confirm-kind="delete"
                        data-detalle-tipo="movimiento"
                        data-detalle-nombre="{{ $mov->concepto }}"
                        data-detalle="{{ $mov->concepto }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-micro btn-micro-danger" data-i18n="common.delete">Eliminar</button>
                      </form>
                    </div>
                  </td>
                @endif
              </tr>
            @empty
              <tr><td colspan="{{ $usuario->tienePermiso('finanzas.editar') ? 7 : 6 }}" style="text-align: center; padding: 1.5rem;" data-i18n="m4.empty_mov">No hay movimientos registrados.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif
</section>
