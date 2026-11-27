<!-- MODAL GLOBAL DE CONFIRMACIÓN JS -->
<div class="modal-overlay modal-overlay-confirm" id="modal-confirmacion-lat" hidden role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="confirm-modal-title" data-i18n="confirm.title">Confirmar cambio</h2>
      <button type="button" class="modal-close" data-close-modal="modal-confirmacion-lat" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <div class="modal-body">
      <p id="confirm-modal-badge" class="confirm-status-notice" hidden></p>
      <p id="confirm-modal-message" style="font-size: 1rem; color: var(--color-ink); margin: 0;"></p>
      <div id="confirm-modal-reason-wrap" class="form-grupo" style="margin-top: 0.85rem; margin-bottom: 0;" hidden>
        <label for="confirm-modal-reason" class="form-label" data-i18n="confirm.discard_reason_label">Motivo del descarte</label>
        <input type="text" id="confirm-modal-reason" class="input-lat" maxlength="255" placeholder="Ej: Acidez excesiva / contaminación física" data-i18n-placeholder="confirm.discard_reason_ph">
      </div>
    </div>
    <div class="modal-foot">
      <button type="button" class="btn btn-outline" data-close-modal="modal-confirmacion-lat" data-i18n="confirm.cancel">Cancelar</button>
      <button type="button" class="btn btn-ember" id="btn-confirmar-accion-js" data-i18n="confirm.accept">Sí, confirmar cambio</button>
    </div>
  </div>
</div>

<!-- MODAL: EDITAR DATOS (CORRECCIÓN RÁPIDA SEGÚN PERMISOS) -->
@if($usuario->tieneAlgunPermiso(['acopio.editar', 'finanzas.editar', 'acopio.proveedores']))
<div class="modal-overlay" id="modal-editar-datos" hidden role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
  <div class="modal-swiss">
    <div class="modal-head">
      <h2 id="edit-modal-title" data-i18n="edit_modal.title">Editar datos registrados (Corrección rápida)</h2>
      <button type="button" class="modal-close" data-close-modal="modal-editar-datos" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <div class="modal-body">
      <p style="margin-bottom: 1rem; color: var(--color-graphite);" data-i18n="edit_modal.sub">Corrija aquí entregas, movimientos o ganaderos si ocurrió un error al registrar.</p>

      @php
        $primeraSubtab = $usuario->tienePermiso('acopio.editar') ? 'subtab-acopios'
          : ($usuario->tienePermiso('finanzas.editar') ? 'subtab-movimientos' : 'subtab-ganaderos');
      @endphp

      <div class="subtabs-edit" role="tablist" aria-label="Categorías de corrección" data-i18n-aria="modal.subtabs_aria">
        @if($usuario->tienePermiso('acopio.editar'))
          <button type="button" role="tab" id="tab-btn-subtab-acopios" aria-controls="subtab-acopios" aria-selected="{{ $primeraSubtab === 'subtab-acopios' ? 'true' : 'false' }}" class="subtab-btn {{ $primeraSubtab === 'subtab-acopios' ? 'activo' : '' }}" data-subtab="subtab-acopios"><span data-i18n="modal.tab_acopios">Entregas de hoy</span> <span>({{ $acopiosHoy->count() }})</span></button>
        @endif
        @if($usuario->tienePermiso('finanzas.editar'))
          <button type="button" role="tab" id="tab-btn-subtab-movimientos" aria-controls="subtab-movimientos" aria-selected="{{ $primeraSubtab === 'subtab-movimientos' ? 'true' : 'false' }}" class="subtab-btn {{ $primeraSubtab === 'subtab-movimientos' ? 'activo' : '' }}" data-subtab="subtab-movimientos"><span data-i18n="modal.tab_movs">Movimientos y ventas</span> <span>({{ $movimientos->count() }})</span></button>
        @endif
        @if($usuario->tienePermiso('acopio.proveedores'))
          <button type="button" role="tab" id="tab-btn-subtab-ganaderos" aria-controls="subtab-ganaderos" aria-selected="{{ $primeraSubtab === 'subtab-ganaderos' ? 'true' : 'false' }}" class="subtab-btn {{ $primeraSubtab === 'subtab-ganaderos' ? 'activo' : '' }}" data-subtab="subtab-ganaderos"><span data-i18n="modal.tab_farmers">Ganaderos</span> <span>({{ $proveedores->count() }})</span></button>
        @endif
      </div>

      @if($usuario->tienePermiso('acopio.editar'))
      <div id="subtab-acopios" role="tabpanel" aria-labelledby="tab-btn-subtab-acopios" class="subtab-panel" @if($primeraSubtab !== 'subtab-acopios') hidden @endif>
        <div class="tabla-region">
          <table class="tabla-plana" style="min-width: 480px;">
            <thead><tr><th data-i18n="m1.col_time">Hora</th><th data-i18n="m1.col_producer">Productor</th><th class="num" data-i18n="m1.col_liters">Litros</th><th data-i18n="m1.col_quality">Calidad</th><th data-i18n="m1.col_actions">Corregir</th></tr></thead>
            <tbody>
              @forelse($acopiosHoy as $a)
                <tr>
                  <td class="num-tabular">{{ substr($a->hora, 0, 5) }}</td>
                  <td>{{ $a->proveedor->nombre }}</td>
                  <td class="num"><strong>{{ number_format($a->litros, 1) }} L</strong></td>
                  <td><span data-i18n="calidad.state_{{ $a->calidad }}">{{ $a->calidad }}</span></td>
                  <td style="display: flex; gap: 0.35rem;">
                    @if($a->lote_id !== null)
                      <span class="chip chip-celeste" data-i18n="m1.locked_in_batch">En lote (protegida)</span>
                    @else
                      <button type="button" class="btn-micro js-edit-acopio"
                        data-id="{{ $a->id }}"
                        data-proveedor="{{ $a->proveedor_id }}"
                        data-litros="{{ $a->litros }}"
                        data-calidad="{{ $a->calidad }}"
                        data-metodo="{{ $a->metodo }}"
                        data-fecha="{{ $a->fecha?->format('Y-m-d') }}"
                        data-hora="{{ substr($a->hora, 0, 5) }}"
                        data-frase="{{ $a->frase_dictada ?? '' }}" data-i18n="common.edit">Editar</button>
                      <form method="POST" action="{{ route('panel.acopio.delete', $a->id) }}" class="js-confirm-form" data-confirm-kind="delete" data-detalle-tipo="acopio" data-detalle-nombre="{{ $a->proveedor->nombre ?? '' }}" data-litros="{{ number_format($a->litros, 1) }}" data-detalle="acopio de {{ $a->proveedor->nombre }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-micro btn-micro-danger" data-i18n="common.erase">Borrar</button>
                      </form>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" style="text-align: center;" data-i18n="m1.empty">No hay acopios hoy.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      @endif

      @if($usuario->tienePermiso('finanzas.editar'))
      <div id="subtab-movimientos" role="tabpanel" aria-labelledby="tab-btn-subtab-movimientos" class="subtab-panel" @if($primeraSubtab !== 'subtab-movimientos') hidden @endif>
        <div class="tabla-region">
          <table class="tabla-plana" style="min-width: 480px;">
            <thead><tr><th data-i18n="m4.col_type">Tipo</th><th data-i18n="m4.col_concept">Concepto</th><th class="num" data-i18n="m4.col_amount">Monto</th><th data-i18n="m1.col_actions">Corregir</th></tr></thead>
            <tbody>
              @forelse($movimientos as $m)
                <tr>
                  <td data-i18n="m4.mov_type_{{ $m->tipo }}">{{ $tiposMovLabels[$m->tipo] ?? $m->tipo }}</td>
                  <td>{{ $m->concepto }}</td>
                  <td class="num">S/ {{ number_format($m->monto, 2) }}</td>
                  <td>
                    <button type="button" class="btn-micro js-edit-mov"
                      data-id="{{ $m->id }}"
                      data-tipo="{{ $m->tipo }}"
                      data-fecha="{{ $m->fecha?->format('Y-m-d') ?? '' }}"
                      data-producto="{{ $m->producto_categoria ?? '' }}"
                      data-canal="{{ $m->canal_referencia ?? '' }}"
                      data-proveedor="{{ $m->proveedor_id ?? '' }}"
                      data-concepto="{{ $m->concepto }}"
                      data-unidades="{{ $m->cantidad_unidades ?? 0 }}"
                      data-precio-unitario="{{ $m->precio_unitario !== null ? number_format($m->precio_unitario, 2, '.', '') : '' }}"
                      data-monto="{{ number_format($m->monto, 2, '.', '') }}" data-i18n="common.edit">Editar</button>
                  </td>
                </tr>
              @empty
                <tr><td colspan="4" style="text-align: center;" data-i18n="m4.empty_mov">Sin movimientos.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      @endif

      @if($usuario->tienePermiso('acopio.proveedores'))
      <div id="subtab-ganaderos" role="tabpanel" aria-labelledby="tab-btn-subtab-ganaderos" class="subtab-panel" @if($primeraSubtab !== 'subtab-ganaderos') hidden @endif>
        <form method="POST" action="{{ route('panel.proveedor.store') }}" class="grid-form-ganadero" style="margin-bottom: 1rem;">
          @csrf
          <div>
            <label class="form-label" for="np-prv-cod" style="font-size: 0.78rem;" data-i18n="modal.prov_code">Código</label>
            <input type="text" id="np-prv-cod" name="codigo" class="input-lat" required placeholder="PRV-06" data-i18n-placeholder="modal.ph_prov_code" style="min-height: 40px;">
          </div>
          <div>
            <label class="form-label" for="np-prv-nom" style="font-size: 0.78rem;" data-i18n="modal.prov_name">Nombre del ganadero</label>
            <input type="text" id="np-prv-nom" name="nombre" class="input-lat" required placeholder="Sra. Elena Quispe" data-i18n-placeholder="modal.ph_prov_name" style="min-height: 40px;">
          </div>
          <div>
            <label class="form-label" for="np-prv-sec" style="font-size: 0.78rem;" data-i18n="modal.prov_sector">Sector</label>
            <input type="text" id="np-prv-sec" name="sector" class="input-lat" placeholder="Markjo" data-i18n-placeholder="modal.ph_prov_sector" style="min-height: 40px;">
          </div>
          <button type="submit" class="btn btn-ocean btn-compacto" data-i18n="modal.add_short">+ Agregar</button>
        </form>
        <div class="tabla-region">
          <table class="tabla-plana" style="min-width: 440px;">
            <thead><tr><th data-i18n="m4.col_code">Código</th><th data-i18n="m6.col_name">Nombre</th><th data-i18n="m4.col_sector">Sector</th><th data-i18n="m1.col_actions">Acciones</th></tr></thead>
            <tbody>
              @foreach($proveedores as $pr)
                <tr>
                  <td>{{ $pr->codigo }}</td>
                  <td><strong>{{ $pr->nombre }}</strong></td>
                  <td>{{ $pr->sector }}</td>
                  <td>
                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                      <button type="button" class="btn-micro js-edit-proveedor"
                        data-id="{{ $pr->id }}"
                        data-codigo="{{ $pr->codigo }}"
                        data-nombre="{{ $pr->nombre }}"
                        data-sector="{{ $pr->sector }}" data-i18n="common.edit">Editar</button>
                      <form method="POST" action="{{ route('panel.proveedor.delete', $pr->id) }}"
                        class="js-confirm-form"
                        data-confirm-kind="delete"
                        data-detalle="ganadero {{ $pr->nombre }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-micro btn-micro-danger" data-i18n="common.delete">Eliminar</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
@endif

<!-- MODAL: EDITAR GANADERO -->
@if($usuario->tienePermiso('acopio.proveedores'))
<div class="modal-overlay" id="modal-edit-proveedor" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-proveedor">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-edit-proveedor" data-i18n="modal.edit_prov_title">Editar ganadero</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-proveedor" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <form method="POST" id="form-edit-proveedor" action="">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="grid-form-2-code">
          <div class="form-grupo">
            <label class="form-label" for="ep-prv-cod" data-i18n="modal.prov_code">Código</label>
            <input type="text" name="codigo" id="ep-prv-cod" class="input-lat" required maxlength="20">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="ep-prv-sec" data-i18n="modal.prov_sector">Sector</label>
            <input type="text" name="sector" id="ep-prv-sec" class="input-lat" maxlength="120">
          </div>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="ep-prv-nom" data-i18n="modal.prov_name">Nombre del ganadero</label>
          <input type="text" name="nombre" id="ep-prv-nom" class="input-lat" required maxlength="120">
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-edit-proveedor" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" class="btn btn-ember" data-i18n="common.save">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
@endif

<!-- MODAL: EDITAR ACOPIO ESPECÍFICO -->
@if($usuario->tienePermiso('acopio.editar'))
<div class="modal-overlay" id="modal-edit-acopio" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-acopio">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-edit-acopio" data-i18n="modal.edit_acopio_title">Corregir entrega de leche</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-acopio" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <form method="POST" id="form-edit-acopio" action="" class="js-confirm-form" data-confirm-kind="edit_acopio">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="form-grupo">
          <label class="form-label" for="edit-ac-prov" data-i18n="m1.col_producer">Productor</label>
          <select name="proveedor_id" id="edit-ac-prov" class="select-lat" required>
            @foreach($proveedores as $prov)
              <option value="{{ $prov->id }}">{{ $prov->codigo }} - {{ $prov->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="grid-form-3">
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-litros" data-i18n="m3.lbl_liters">Litros (L)</label>
            <input type="number" step="0.1" min="1" max="5000" name="litros" id="edit-ac-litros" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-fecha">Fecha</label>
            <input type="date" name="fecha" id="edit-ac-fecha" class="input-lat" max="{{ Carbon\Carbon::today()->toDateString() }}">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-hora" data-i18n="m1.col_time">Hora</label>
            <input type="time" name="hora" id="edit-ac-hora" class="input-lat">
          </div>
        </div>
        <div class="grid-form-2">
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-calidad" data-i18n="m1.col_quality">Calidad</label>
            <select name="calidad" id="edit-ac-calidad" class="select-lat" @disabled(! $usuario->tienePermiso('calidad.evaluar'))>
              <option value="buena" data-i18n="calidad.state_buena">Aprobada (Tina principal)</option>
              <option value="acida" data-i18n="calidad.state_acida">Ácida (Quesillo 4 días)</option>
              <option value="pendiente" data-i18n="calidad.state_pendiente">Pendiente</option>
              <option value="descartada" data-i18n="m2.status_discarded">Descartada</option>
            </select>
            @if(! $usuario->tienePermiso('calidad.evaluar'))
              <input type="hidden" name="calidad" id="edit-ac-calidad-hidden" value="">
            @endif
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-metodo" data-i18n="m1.col_method">Método</label>
            <select name="metodo" id="edit-ac-metodo" class="select-lat">
              <option value="manual" data-i18n="common.manual">Manual</option>
              <option value="voz" data-i18n="common.voice">Voz</option>
            </select>
          </div>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="edit-ac-frase" data-i18n="modal.lbl_voice_phrase">Frase dictada (opcional)</label>
          <input type="text" name="frase_dictada" id="edit-ac-frase" class="input-lat" maxlength="255" placeholder="Ej: Don Eloy Condori, 35 litros">
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-edit-acopio" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" class="btn btn-ember" data-i18n="common.save">Guardar corrección</button>
      </div>
    </form>
  </div>
</div>
@endif

<!-- MODAL: EDITAR MOVIMIENTO FINANCIERO -->
@if($usuario->tienePermiso('finanzas.editar'))
<div class="modal-overlay" id="modal-edit-mov" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-mov">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-edit-mov" data-i18n="modal.edit_mov_title">Corregir movimiento / venta</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-mov" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <form method="POST" id="form-edit-mov" action="">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="grid-form-2">
          <div class="form-grupo">
            <label class="form-label" for="edit-mov-tipo" data-i18n="m4.lbl_type">Tipo</label>
            <select name="tipo" id="edit-mov-tipo" class="select-lat">
              <option value="venta_producto" data-i18n="m4.mov_type_venta_producto">Venta de producto lácteo</option>
              <option value="sueldo_personal" data-i18n="m4.mov_type_sueldo_personal">Sueldo de personal</option>
              <option value="adelanto_efectivo" data-i18n="m4.mov_type_adelanto_efectivo">Adelanto en efectivo</option>
              <option value="adelanto_queso" data-i18n="m4.mov_type_adelanto_queso">Adelanto en moldes de queso</option>
              <option value="ayuda_del_dia" data-i18n="m4.mov_type_ayuda_del_dia">Ayuda del día (sin DNI)</option>
              <option value="gasto_operativo" data-i18n="m4.mov_type_gasto_operativo">Gasto operativo</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-mov-fecha" data-i18n="m4.col_date">Fecha</label>
            <input type="date" name="fecha" id="edit-mov-fecha" class="input-lat" max="{{ \Carbon\Carbon::today()->toDateString() }}">
          </div>
        </div>
        <div class="grid-form-2">
          <div class="form-grupo">
            <label class="form-label" for="edit-mov-producto" data-i18n="m4.col_product">Producto (si aplica)</label>
            <select name="producto_categoria" id="edit-mov-producto" class="select-lat">
              <option value="">—</option>
              @foreach(\App\Models\Liquidacion::PRODUCTOS_VENTA as $prodKey)
                <option value="{{ $prodKey }}" data-i18n="m4.prod_{{ $prodKey }}">{{ ucwords(str_replace('_', ' ', $prodKey)) }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-mov-canal" data-i18n="m4.col_channel">Canal / Ref. (si aplica)</label>
            <select name="canal_referencia" id="edit-mov-canal" class="select-lat">
              <option value="">—</option>
              @foreach(\App\Models\Liquidacion::CANALES_VENTA as $canalKey)
                <option value="{{ $canalKey }}" data-i18n="m4.canal_{{ $canalKey }}">{{ ucwords(str_replace('_', ' ', $canalKey)) }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="edit-mov-prov" data-i18n="m4.lbl_prov">Productor / Beneficiario (si aplica)</label>
          <select name="proveedor_id" id="edit-mov-prov" class="select-lat">
            <option value="" data-i18n="m4.opt_none_plant">Ninguno / planta</option>
            @foreach($proveedores as $prov)
              <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="edit-mov-concepto" data-i18n="m4.lbl_concept">Concepto</label>
          <input type="text" name="concepto" id="edit-mov-concepto" class="input-lat" required maxlength="160">
        </div>
        <div class="grid-form-3">
          <div class="form-grupo">
            <label class="form-label" for="edit-mov-unidades" data-i18n="m4.lbl_units">Unidades / Días</label>
            <input type="number" min="0" name="cantidad_unidades" id="edit-mov-unidades" class="input-lat">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-mov-punit" data-i18n="m4.col_unit_price">P. Unit. / Tarifa (S/)</label>
            <input type="number" step="0.01" min="0" name="precio_unitario" id="edit-mov-punit" class="input-lat">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-mov-monto" data-i18n="m4.lbl_amount">Monto (S/)</label>
            <input type="number" step="0.01" min="0.5" name="monto" id="edit-mov-monto" class="input-lat" required>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-edit-mov" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" class="btn btn-ember" data-i18n="common.save">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
@endif

<!-- MODALES DE MÚLTIPLES TINAS -->
@if($usuario->tienePermiso('tinas.gestionar'))
<div class="modal-overlay" id="modal-nueva-tina" hidden role="dialog" aria-modal="true" aria-labelledby="title-nueva-tina">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-nueva-tina" data-i18n="modal.new_tina_title">Agregar nueva tina</h2>
      <button type="button" class="modal-close" data-close-modal="modal-nueva-tina" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <form method="POST" action="{{ route('panel.tinas.store') }}">
      @csrf
      <div class="modal-body">
        <div class="grid-form-2-code">
          @php
            $sufTina = $siguienteSufijoTina ?? '04';
            $prefTina = $prefijoTinaIdioma ?? 'TINA-';
          @endphp
          <div class="form-grupo">
            <label class="form-label" for="nt-codigo-sufijo" data-i18n="modal.tina_code">Código</label>
            <div class="input-prefix-group">
              <span class="input-prefix-addon js-tina-prefix-addon" aria-hidden="true">{{ $prefTina }}</span>
              <input type="text" id="nt-codigo-sufijo" class="input-lat" required maxlength="25" value="{{ $sufTina }}" placeholder="{{ $sufTina }}" autocomplete="off">
            </div>
            <input type="hidden" name="codigo" id="nt-codigo" value="{{ $prefTina }}{{ $sufTina }}">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nt-nombre" data-i18n="modal.tina_name">Nombre de la tina</label>
            <input type="text" name="nombre" id="nt-nombre" class="input-lat" required placeholder="Tina Queso Fresco" data-i18n-placeholder="modal.ph_tina_name">
          </div>
        </div>
        <div class="grid-form-2">
          <div class="form-grupo">
            <label class="form-label" for="nt-cap" data-i18n="modal.tina_cap">Capacidad (L)</label>
            <input type="number" step="10" min="20" max="10000" name="capacidad_litros" id="nt-cap" class="input-lat" value="300" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nt-tipo" data-i18n="modal.tina_type">Tipo de uso</label>
            <input type="text" name="tipo" id="nt-tipo" class="input-lat" list="lista-tipos-tina" maxlength="80" required placeholder="Queso principal o escriba otro…" data-i18n-placeholder="modal.ph_tina_type" autocomplete="off">
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-nueva-tina" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" class="btn btn-ember" data-i18n="modal.create_tina_btn">Crear tina</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modal-edit-tina" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-tina">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-edit-tina" data-i18n="m3.edit_tina">Editar tina</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-tina" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <form method="POST" id="form-edit-tina" action="">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="grid-form-2-code">
          <div class="form-grupo">
            <label class="form-label" for="et-codigo-sufijo" data-i18n="modal.tina_code">Código</label>
            <div class="input-prefix-group">
              <span class="input-prefix-addon js-tina-prefix-addon" aria-hidden="true">{{ $prefijoTinaIdioma ?? 'TINA-' }}</span>
              <input type="text" id="et-codigo-sufijo" class="input-lat" required maxlength="25" autocomplete="off">
            </div>
            <input type="hidden" name="codigo" id="et-codigo" value="">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="et-nombre" data-i18n="modal.tina_name">Nombre</label>
            <input type="text" name="nombre" id="et-nombre" class="input-lat" required>
          </div>
        </div>
        <div class="grid-form-2">
          <div class="form-grupo">
            <label class="form-label" for="et-cap" data-i18n="modal.tina_cap">Capacidad (L)</label>
            <input type="number" step="10" min="20" max="10000" name="capacidad_litros" id="et-cap" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="et-tipo" data-i18n="modal.tina_type">Tipo</label>
            <input type="text" name="tipo" id="et-tipo" class="input-lat" list="lista-tipos-tina" maxlength="80" required placeholder="Queso principal o escriba otro…" data-i18n-placeholder="modal.ph_tina_type" autocomplete="off">
          </div>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="et-activa" data-i18n="modal.tina_status">Estado operativo</label>
          <select name="activa" id="et-activa" class="select-lat">
            <option value="1" data-i18n="m3.active">Activa</option>
            <option value="0" data-i18n="modal.tina_inactive_maint">Inactiva / Mantenimiento</option>
          </select>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-edit-tina" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" class="btn btn-ember" data-i18n="common.save">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
<datalist id="lista-tipos-tina">
  <option value="Queso principal">
  <option value="Quesillo 4 días">
  <option value="Yogur">
  <option value="Multipropósito">
</datalist>
@endif

<!-- MODAL: INICIAR LOTE EN TINA (SELECCIÓN MÚLTIPLE DE LECHE Y RECETA) -->
@if($usuario->tienePermiso('tinas.operar'))
<div class="modal-overlay" id="modal-iniciar-tina" hidden role="dialog" aria-modal="true" aria-labelledby="title-iniciar-tina">
  <div class="modal-swiss modal-lg">
    <div class="modal-head">
      <h2 id="title-iniciar-tina" data-i18n="m3.start_modal_title">Iniciar lote en tina</h2>
      <button type="button" class="modal-close" data-close-modal="modal-iniciar-tina" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <form method="POST" id="form-iniciar-tina" action="" class="js-confirm-form" data-confirm-kind="start_batch">
      @csrf
      <input type="hidden" id="it-litros-total" name="litros" value="0">
      <input type="hidden" id="it-tina-capacidad" value="0">
      <div class="modal-body">
        <div id="it-tina-resumen" class="perm-summary-bar">
          <strong data-i18n="m3.start_modal_vat">Tina seleccionada</strong>
        </div>

        <h3 style="font-size: 1rem; margin-bottom: 0.6rem;" data-i18n="m3.start_sec_milk">1. Leche clasificada disponible (selección múltiple)</h3>
        <div class="it-filtros-bar">
          <div>
            <label class="form-label" for="it-filtro-calidad" data-i18n="m3.filter_quality">Calidad</label>
            <select id="it-filtro-calidad" class="select-lat">
              <option value="todas" data-i18n="m3.filter_all_qualities">Todas las calidades</option>
              <option value="buena" data-i18n="m2.status_good">Óptima</option>
              <option value="acida" data-i18n="m2.status_acid">Con acidez</option>
            </select>
          </div>
          <div>
            <label class="form-label" for="it-filtro-dia" data-i18n="m3.filter_day">Día</label>
            <select id="it-filtro-dia" class="select-lat">
              <option value="todos" data-i18n="m3.filter_all_days">Todos los días</option>
              <option value="hoy" data-i18n="dash.range_hoy">Hoy</option>
              <option value="fecha" data-i18n="m3.filter_by_date">Fecha exacta</option>
            </select>
          </div>
          <div>
            <label class="form-label" for="it-filtro-fecha" data-i18n="m3.filter_intake_date">Fecha de acopio</label>
            <input type="date" id="it-filtro-fecha" class="input-lat">
          </div>
          <div>
            <label class="form-label" for="it-filtro-orden" data-i18n="m3.filter_sort">Ordenar</label>
            <select id="it-filtro-orden" class="select-lat">
              <option value="reciente" data-i18n="m3.sort_recent">Más reciente</option>
              <option value="antiguo" data-i18n="m3.sort_oldest">Más antiguo</option>
              <option value="litros" data-i18n="m3.sort_liters">Mayor volumen (L)</option>
            </select>
          </div>
        </div>

        <div class="it-actions-bar">
          <div style="display: flex; gap: 0.45rem; flex-wrap: wrap;">
            <button type="button" id="it-btn-sel-visibles" class="btn-micro" data-i18n="m3.select_visible">Seleccionar visibles</button>
            <button type="button" id="it-btn-limpiar" class="btn-micro" data-i18n="m3.clear_selection">Limpiar</button>
          </div>
          <div id="it-litros-resumen" class="it-litros-meter num-tabular">0.0 L</div>
        </div>

        <div id="it-hidden-selection-notice" class="alerta alerta-amarillo it-hidden-selection-notice" role="status" aria-live="polite" hidden>
          <span id="it-hidden-selection-text"></span>
          <button type="button" id="it-btn-desmarcar-ocultas" class="btn-micro" data-i18n="m3.uncheck_hidden">Desmarcar fuera de filtro</button>
        </div>

        @php $acopiosDisp = $acopiosDisponiblesTinas ?? collect(); @endphp
        <div id="it-lista-acopios" class="it-acopios-list">
          @foreach($acopiosDisp as $ac)
            <label class="it-acopio-item"
              data-calidad="{{ $ac->calidad }}"
              data-fecha="{{ $ac->fecha?->format('Y-m-d') }}"
              data-eval-ts="{{ ($ac->evaluado_en ?? $ac->created_at)?->timestamp ?? 0 }}"
              data-litros="{{ (float) $ac->litros }}">
              <input type="checkbox" name="acopio_ids[]" value="{{ $ac->id }}" class="js-it-acopio-cb">
              <span class="chip {{ $ac->calidad === 'buena' ? 'chip-verde' : 'chip-amarillo' }}" data-i18n="{{ $ac->calidad === 'buena' ? 'm2.status_good' : 'm3.diverted_quesillo' }}">
                {{ $ac->calidad === 'buena' ? 'Óptima' : 'Desviada a quesillo' }}
              </span>
              <span class="it-acopio-prov"><strong>{{ $ac->proveedor->nombre }}</strong></span>
              <strong class="num-tabular it-acopio-litros">{{ number_format($ac->litros, 1) }} L</strong>
              <span class="it-acopio-fechas num-tabular">
                <small><strong data-i18n="m3.lbl_received">Recibida:</strong> {{ $ac->fecha?->format('d/m/Y') }} {{ substr((string) $ac->hora, 0, 5) }}</small>
                @if($ac->evaluado_en)
                  <small><strong data-i18n="m3.lbl_evaluated">Evaluada:</strong> {{ $ac->evaluado_en->format('d/m/Y H:i') }}</small>
                @endif
              </span>
            </label>
          @endforeach
          <p id="it-acopios-empty" class="it-acopios-empty" data-i18n="m3.no_milk_available" @if($acopiosDisp->isNotEmpty()) hidden @endif>
            No hay leche clasificada disponible con estos filtros.
          </p>
        </div>

        <h3 style="font-size: 1rem; margin: 1.15rem 0 0.6rem;" data-i18n="m3.start_sec_recipe">2. Receta base y cronómetro editables</h3>
        <div class="grid-form-2" style="margin-bottom: 0.75rem;">
          <div class="form-grupo" style="margin: 0;">
            <label class="form-label" for="it-receta-preset" data-i18n="m3.recipe_preset">Receta base</label>
            <select id="it-receta-preset" name="receta_base" class="select-lat">
              <option value="principal_queso" data-i18n="tina.type_principal_queso">Queso principal</option>
              <option value="quesillo" data-i18n="tina.type_quesillo">Quesillo 4 días</option>
              <option value="yogur" data-i18n="tina.type_yogur">Yogur</option>
              <option value="multiproposito" data-i18n="tina.type_multiproposito">Multipropósito</option>
              <option value="personalizada" data-i18n="m3.recipe_custom">Personalizada</option>
            </select>
          </div>
          <div class="form-grupo" style="margin: 0;">
            <label class="form-label" for="it-receta-nombre" data-i18n="m3.recipe_name">Nombre de receta</label>
            <input type="text" id="it-receta-nombre" name="receta_nombre" class="input-lat" maxlength="120" placeholder="Queso andino estándar" data-i18n-placeholder="m3.ph_recipe_name">
          </div>
        </div>

        <div class="grid-form-2" style="margin-bottom: 0.75rem;">
          <div class="form-grupo" style="margin: 0;">
            <label class="form-label" for="it-temp" data-i18n="m3.lbl_temp_init">Temp. inicial (°C)</label>
            <input type="number" id="it-temp" name="temperatura_actual" class="input-lat" step="0.5" min="4" max="95" value="72.0" required>
          </div>
          <div class="form-grupo" style="margin: 0;">
            <label class="form-label" for="it-duracion" data-i18n="m3.lbl_timer_min">Tiempo (min)</label>
            <input type="number" id="it-duracion" name="duracion_minutos" class="input-lat" step="1" min="1" max="1440" value="45" required>
          </div>
        </div>

        <div class="grid-form-3" style="margin-bottom: 0.75rem;">
          <div class="form-grupo" style="margin: 0;">
            <label class="form-label" for="it-cuajo" data-i18n="m3.rennet_ml">Cuajo (ml)</label>
            <input type="number" id="it-cuajo" name="cuajo_ml" class="input-lat" step="0.01" min="0" max="10000" value="0.00" required>
          </div>
          <div class="form-grupo" style="margin: 0;">
            <label class="form-label" for="it-sal" data-i18n="m3.salt_kg">Sal (kg)</label>
            <input type="number" id="it-sal" name="sal_kg" class="input-lat" step="0.01" min="0" max="10000" value="0.00" required>
          </div>
          <div class="form-grupo" style="margin: 0;">
            <label class="form-label" for="it-cultivo" data-i18n="m3.culture_g">Cultivo (g)</label>
            <input type="number" id="it-cultivo" name="cultivo_g" class="input-lat" step="0.01" min="0" max="10000" value="0.00" required>
          </div>
        </div>

        <div style="display: flex; justify-content: flex-end;">
          <button type="button" id="it-btn-recalcular" class="btn btn-outline btn-compacto" data-i18n="m3.recalc_recipe">Recalcular según litros</button>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-iniciar-tina" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" id="btn-submit-iniciar-tina" class="btn btn-ember" data-i18n="m3.start_batch">Iniciar lote en tina</button>
      </div>
    </form>
  </div>
</div>
@endif

<!-- MODAL: CONFIGURAR RECETAS BASE POR TIPO DE TINA -->
@if($usuario->tienePermiso('tinas.gestionar'))
<div class="modal-overlay" id="modal-recetas-base" hidden role="dialog" aria-modal="true" aria-labelledby="title-recetas-base">
  <div class="modal-swiss modal-lg">
    <div class="modal-head">
      <h2 id="title-recetas-base" data-i18n="m3.base_recipes_title">Recetas base de planta (por cada 100 L)</h2>
      <button type="button" class="modal-close" data-close-modal="modal-recetas-base" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <form method="POST" id="form-recetas-base" action="{{ route('panel.tinas.recetas_base') }}">
      @csrf @method('PUT')
      <div class="modal-body">
        <p style="margin-bottom: 1rem; color: var(--color-graphite);" data-i18n="m3.base_recipes_sub">Defina las proporciones estándar por cada 100 litros, temperatura inicial y tiempo base para cada tipo de producción.</p>
        <div class="rb-grid">
          @foreach(($recetasBase ?? []) as $clave => $rec)
            <div class="rb-card">
              <div class="rb-card-head">
                <strong data-i18n="tina.type_{{ $clave }}">{{ $rec['nombre'] ?? ($tiposTinaLabels[$clave] ?? $clave) }}</strong>
                <span class="chip chip-celeste">{{ $clave }}</span>
              </div>
              <div class="grid-form-3" style="margin-bottom: 0.65rem;">
                <div>
                  <label class="form-label" style="font-size: 0.76rem;" for="rb-{{ $clave }}-cuajo" data-i18n="m3.rennet_100l">Cuajo ml / 100 L</label>
                  <input type="number" step="0.01" min="0" max="10000" id="rb-{{ $clave }}-cuajo" name="recetas[{{ $clave }}][cuajo_x_100l]" class="input-lat" value="{{ $rec['cuajo_x_100l'] ?? 0 }}" required style="min-height: 40px; padding: 0.4rem 0.6rem;">
                </div>
                <div>
                  <label class="form-label" style="font-size: 0.76rem;" for="rb-{{ $clave }}-sal" data-i18n="m3.salt_100l">Sal kg / 100 L</label>
                  <input type="number" step="0.01" min="0" max="10000" id="rb-{{ $clave }}-sal" name="recetas[{{ $clave }}][sal_x_100l]" class="input-lat" value="{{ $rec['sal_x_100l'] ?? 0 }}" required style="min-height: 40px; padding: 0.4rem 0.6rem;">
                </div>
                <div>
                  <label class="form-label" style="font-size: 0.76rem;" for="rb-{{ $clave }}-cultivo" data-i18n="m3.culture_100l">Cultivo g / 100 L</label>
                  <input type="number" step="0.01" min="0" max="10000" id="rb-{{ $clave }}-cultivo" name="recetas[{{ $clave }}][cultivo_x_100l]" class="input-lat" value="{{ $rec['cultivo_x_100l'] ?? 0 }}" required style="min-height: 40px; padding: 0.4rem 0.6rem;">
                </div>
              </div>
              <div class="grid-form-2">
                <div>
                  <label class="form-label" style="font-size: 0.76rem;" for="rb-{{ $clave }}-temp" data-i18n="m3.lbl_temp_init">Temp. inicial (°C)</label>
                  <input type="number" step="0.5" min="4" max="95" id="rb-{{ $clave }}-temp" name="recetas[{{ $clave }}][temp_inicial]" class="input-lat" value="{{ $rec['temp_inicial'] ?? 72 }}" required style="min-height: 40px; padding: 0.4rem 0.6rem;">
                </div>
                <div>
                  <label class="form-label" style="font-size: 0.76rem;" for="rb-{{ $clave }}-duracion" data-i18n="m3.lbl_timer_min">Tiempo (min)</label>
                  <input type="number" step="1" min="1" max="1440" id="rb-{{ $clave }}-duracion" name="recetas[{{ $clave }}][duracion_minutos]" class="input-lat" value="{{ $rec['duracion_minutos'] ?? 45 }}" required style="min-height: 40px; padding: 0.4rem 0.6rem;">
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-recetas-base" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" class="btn btn-ember" data-i18n="common.save">Guardar recetas base</button>
      </div>
    </form>
  </div>
</div>
@endif

<!-- MODALES DE PLANTAS -->
@if($usuario->tienePermiso('admin.plantas'))
<div class="modal-overlay" id="modal-nueva-planta" hidden role="dialog" aria-modal="true" aria-labelledby="title-nueva-planta">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-nueva-planta" data-i18n="modal.new_plant_title">Agregar nueva planta</h2>
      <button type="button" class="modal-close" data-close-modal="modal-nueva-planta" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.plantas.store') }}">
      @csrf
      <div class="modal-body">
        <div class="grid-form-2-code">
          @php $sufPlt = $siguienteSufijoPlanta ?? '04'; @endphp
          <div class="form-grupo">
            <label class="form-label" for="np-cod-sufijo" data-i18n="m6.col_code">Código</label>
            <div class="input-prefix-group">
              <span class="input-prefix-addon" aria-hidden="true">PLT-</span>
              <input type="text" id="np-cod-sufijo" class="input-lat" required maxlength="16" value="{{ $sufPlt }}" placeholder="{{ $sufPlt }}" autocomplete="off">
            </div>
            <input type="hidden" name="codigo" id="np-cod" value="PLT-{{ $sufPlt }}">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="np-nom" data-i18n="m6.col_plant_name">Nombre de planta</label>
            <input type="text" name="nombre" id="np-nom" class="input-lat" required placeholder="Quesería Maras" data-i18n-placeholder="modal.ph_plant_name">
          </div>
        </div>
        <div class="grid-form-2-wide">
          <div class="form-grupo">
            <label class="form-label" for="np-ubi" data-i18n="m6.col_location">Ubicación</label>
            <input type="text" name="ubicacion" id="np-ubi" class="input-lat" required value="Cusco Provincia">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="np-pre" data-i18n="m6.col_price">Precio / L (S/)</label>
            <input type="number" step="0.01" min="0.5" max="20" name="precio_litro" id="np-pre" class="input-lat" value="1.65" required>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-nueva-planta" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" class="btn btn-ember" data-i18n="modal.create_plant_btn">Crear planta</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modal-edit-planta" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-planta">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-edit-planta" data-i18n="modal.edit_plant_title">Editar planta láctea</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-planta" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <form method="POST" id="form-edit-planta" action="">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="grid-form-2-code">
          <div class="form-grupo">
            <label class="form-label" for="ep-cod-sufijo" data-i18n="m6.col_code">Código</label>
            <div class="input-prefix-group">
              <span class="input-prefix-addon" aria-hidden="true">PLT-</span>
              <input type="text" id="ep-cod-sufijo" class="input-lat" required maxlength="16" autocomplete="off">
            </div>
            <input type="hidden" name="codigo" id="ep-cod" value="">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="ep-nom" data-i18n="m6.col_name">Nombre</label>
            <input type="text" name="nombre" id="ep-nom" class="input-lat" required>
          </div>
        </div>
        <div class="grid-form-2-wide">
          <div class="form-grupo">
            <label class="form-label" for="ep-ubi" data-i18n="m6.col_location">Ubicación</label>
            <input type="text" name="ubicacion" id="ep-ubi" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="ep-pre" data-i18n="m6.col_price">Precio / L (S/)</label>
            <input type="number" step="0.01" min="0.5" max="20" name="precio_litro" id="ep-pre" class="input-lat" required>
          </div>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="ep-act" data-i18n="m6.col_status">Estado</label>
          <select name="activa" id="ep-act" class="select-lat">
            <option value="1" data-i18n="m3.active">Activa</option>
            <option value="0" data-i18n="m3.inactive">Inactiva</option>
          </select>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-edit-planta" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" class="btn btn-ember" data-i18n="common.save">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
@endif

<!-- MODALES DE USUARIOS PERSONALIZADOS Y MATRIZ DE PERMISOS -->
@if($usuario->tienePermiso('admin.usuarios'))
<div class="modal-overlay" id="modal-nuevo-usuario" hidden role="dialog" aria-modal="true" aria-labelledby="title-nuevo-usuario">
  <div class="modal-swiss modal-lg">
    <div class="modal-head">
      <h2 id="title-nuevo-usuario" data-i18n="modal.new_user_title">Crear usuario personalizado y permisos</h2>
      <button type="button" class="modal-close" data-close-modal="modal-nuevo-usuario" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.usuarios.store') }}" class="js-user-perm-form" data-prefix="nu">
      @csrf
      <input type="hidden" name="permisos_present" value="1">
      <div class="modal-body">
        <div class="grid-form-3">
          <div class="form-grupo">
            <label class="form-label" for="nu-name" data-i18n="modal.user_fullname">Nombre completo</label>
            <input type="text" name="name" id="nu-name" class="input-lat" required placeholder="Ej: Juana Condori" data-i18n-placeholder="modal.ph_user_fullname">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nu-user" data-i18n="modal.user_login">Usuario de acceso</label>
            <input type="text" name="usuario" id="nu-user" class="input-lat" required placeholder="juana.acopio" data-i18n-placeholder="modal.ph_user_login">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nu-pass" data-i18n="modal.user_pass_init">Contraseña inicial</label>
            <input type="password" name="password" id="nu-pass" class="input-lat" required minlength="8" placeholder="Mín. 8 caracteres" data-i18n-placeholder="modal.ph_user_pass_init">
          </div>
        </div>

        <div class="grid-form-4">
          <div class="form-grupo">
            <label class="form-label" for="nu-cargo" data-i18n="modal.user_role_tpl">Plantilla / Cargo</label>
            <select name="cargo" id="nu-cargo" class="select-lat js-cargo-template" data-prefix="nu">
              <option value="personalizado" data-i18n="m6.role_personalizado">Personalizado (Manual)</option>
              <option value="recolector_ruta" data-i18n="m6.role_recolector_ruta">Recolector en ruta</option>
              <option value="control_tina" data-i18n="m6.role_control_tina">Control de tina / calidad</option>
              <option value="encargado_planta" data-i18n="m6.role_encargado_planta">Encargado de planta</option>
              <option value="contador_externo" data-i18n="m6.role_contador_externo">Contador externo</option>
              @if($usuario->esAdmin())
                <option value="administracion" data-i18n="m6.role_administracion">Administración total</option>
              @endif
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nu-rango" data-i18n="modal.user_rank">Rango jerárquico</label>
            <select name="rango" id="nu-rango" class="select-lat">
              <option value="personalizado" data-i18n="m6.rank_personalizado">Acceso Personalizado</option>
              <option value="operador" data-i18n="m6.rank_operador">Operador</option>
              <option value="supervisor" data-i18n="m6.rank_supervisor">Supervisor con Rango</option>
              @if($usuario->esAdmin())
                <option value="administrador" data-i18n="m6.rank_administrador">Administrador Total</option>
              @endif
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nu-planta" data-i18n="modal.user_plant">Planta asignada</label>
            @if($usuario->esAdmin())
              <select name="planta_id" id="nu-planta" class="select-lat">
                @foreach($plantas as $pl)
                  <option value="{{ $pl->id }}" @selected($pl->id === $plantaActiva->id)>{{ $pl->nombre }}</option>
                @endforeach
              </select>
            @else
              <input type="hidden" name="planta_id" value="{{ $plantaActiva->id }}">
              <input type="text" id="nu-planta" class="input-lat" value="{{ $plantaActiva->nombre }}" readonly>
            @endif
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nu-email" data-i18n="modal.email_opt">Correo (opcional)</label>
            <input type="email" name="email" id="nu-email" class="input-lat" placeholder="opcional@planta.pe" data-i18n-placeholder="modal.ph_user_email">
          </div>
        </div>

        <div class="perm-summary-bar">
          <span data-i18n="modal.perm_hint">Permisos por sección y control: marca únicamente lo que este usuario verá al iniciar sesión.</span>
          <span class="chip chip-celeste" id="nu-perm-counter">0</span>
        </div>

        <div class="perm-matrix" id="nu-perm-matrix">
          @foreach($catalogoPermisos as $secKey => $secData)
            <div class="perm-section-card">
              <div class="perm-section-head">
                <strong data-i18n="perm.sec.{{ $secKey }}">{{ $secData['titulo'] }}</strong>
                <button type="button" class="perm-toggle-all" data-target-sec="{{ $secKey }}" data-prefix="nu" data-i18n="modal.mark_all">Marcar todo</button>
              </div>
              <div class="perm-list">
                @foreach($secData['permisos'] as $permKey => $permInfo)
                  @php
                    $puedeAsignar = in_array($permKey, $permisosAsignables, true);
                    $cbId = 'nu-perm-' . str_replace('.', '-', $permKey);
                  @endphp
                  <label class="perm-check {{ $puedeAsignar ? '' : 'is-disabled' }}" for="{{ $cbId }}">
                    <input type="checkbox"
                      id="{{ $cbId }}"
                      name="permisos[]"
                      value="{{ $permKey }}"
                      data-sec="{{ $secKey }}"
                      data-prefix="nu"
                      class="js-perm-checkbox"
                      @disabled(! $puedeAsignar)>
                    <span>
                      <strong data-i18n="perm.{{ $permKey }}.label">{{ $permInfo['label'] }}</strong>
                      <small data-i18n="perm.{{ $permKey }}.desc">{{ $permInfo['desc'] }}</small>
                    </span>
                  </label>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-nuevo-usuario" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" class="btn btn-ember" data-i18n="modal.create_user_btn">Crear usuario con permisos</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modal-edit-usuario" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-usuario">
  <div class="modal-swiss modal-lg">
    <div class="modal-head">
      <h2 id="title-edit-usuario" data-i18n="modal.edit_user_title">Editar usuario y permisos por sección</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-usuario" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <form method="POST" id="form-edit-usuario" action="" class="js-user-perm-form" data-prefix="eu">
      @csrf @method('PUT')
      <input type="hidden" name="permisos_present" value="1">
      <div class="modal-body">
        <div class="grid-form-3">
          <div class="form-grupo">
            <label class="form-label" for="eu-name" data-i18n="modal.user_fullname">Nombre completo</label>
            <input type="text" name="name" id="eu-name" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="eu-user" data-i18n="modal.user_login_short">Usuario</label>
            <input type="text" name="usuario" id="eu-user" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="eu-pass" data-i18n="modal.user_pass_new">Nueva contraseña (opcional)</label>
            <input type="password" name="password" id="eu-pass" class="input-lat" minlength="8" placeholder="Mantener actual" data-i18n-placeholder="modal.ph_user_pass_keep">
          </div>
        </div>

        <div class="grid-form-4">
          <div class="form-grupo">
            <label class="form-label" for="eu-cargo" data-i18n="modal.user_role_tpl">Plantilla / Cargo</label>
            <select name="cargo" id="eu-cargo" class="select-lat js-cargo-template" data-prefix="eu">
              <option value="personalizado" data-i18n="m6.role_personalizado">Personalizado (Manual)</option>
              <option value="recolector_ruta" data-i18n="m6.role_recolector_ruta">Recolector en ruta</option>
              <option value="control_tina" data-i18n="m6.role_control_tina">Control de tina / calidad</option>
              <option value="encargado_planta" data-i18n="m6.role_encargado_planta">Encargado de planta</option>
              <option value="contador_externo" data-i18n="m6.role_contador_externo">Contador externo</option>
              @if($usuario->esAdmin())
                <option value="administracion" data-i18n="m6.role_administracion">Administración total</option>
              @endif
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="eu-rango" data-i18n="modal.user_rank_short">Rango</label>
            <select name="rango" id="eu-rango" class="select-lat">
              <option value="personalizado" data-i18n="m6.rank_personalizado">Acceso Personalizado</option>
              <option value="operador" data-i18n="m6.rank_operador">Operador</option>
              <option value="supervisor" data-i18n="m6.rank_supervisor">Supervisor con Rango</option>
              @if($usuario->esAdmin())
                <option value="administrador" data-i18n="m6.rank_administrador">Administrador Total</option>
              @endif
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="eu-planta" data-i18n="m6.col_plant">Planta</label>
            @if($usuario->esAdmin())
              <select name="planta_id" id="eu-planta" class="select-lat">
                @foreach($plantas as $pl)
                  <option value="{{ $pl->id }}">{{ $pl->nombre }}</option>
                @endforeach
              </select>
            @else
              <input type="hidden" name="planta_id" id="eu-planta-hidden" value="{{ $plantaActiva->id }}">
              <input type="text" id="eu-planta" class="input-lat" value="{{ $plantaActiva->nombre }}" readonly>
            @endif
          </div>
          <div class="form-grupo">
            <label class="form-label" for="eu-activo" data-i18n="m6.col_status">Estado</label>
            <select name="activo" id="eu-activo" class="select-lat">
              <option value="1" data-i18n="m3.active">Activo</option>
              <option value="0" data-i18n="m3.inactive">Inactivo</option>
            </select>
          </div>
        </div>

        <div class="form-grupo">
          <label class="form-label" for="eu-email" data-i18n="modal.email_opt">Correo (opcional)</label>
          <input type="email" name="email" id="eu-email" class="input-lat" value="" placeholder="opcional@planta.pe" data-i18n-placeholder="modal.ph_user_email">
        </div>

        <div class="perm-summary-bar">
          <span data-i18n="modal.perm_adjust">Controles habilitados: ajusta los permisos específicos de esta cuenta.</span>
          <span class="chip chip-celeste" id="eu-perm-counter">0</span>
        </div>

        <div class="perm-matrix" id="eu-perm-matrix">
          @foreach($catalogoPermisos as $secKey => $secData)
            <div class="perm-section-card">
              <div class="perm-section-head">
                <strong data-i18n="perm.sec.{{ $secKey }}">{{ $secData['titulo'] }}</strong>
                <button type="button" class="perm-toggle-all" data-target-sec="{{ $secKey }}" data-prefix="eu" data-i18n="modal.mark_all">Marcar todo</button>
              </div>
              <div class="perm-list">
                @foreach($secData['permisos'] as $permKey => $permInfo)
                  @php
                    $puedeAsignar = in_array($permKey, $permisosAsignables, true);
                    $cbId = 'eu-perm-' . str_replace('.', '-', $permKey);
                  @endphp
                  <label class="perm-check {{ $puedeAsignar ? '' : 'is-disabled' }}" for="{{ $cbId }}">
                    <input type="checkbox"
                      id="{{ $cbId }}"
                      name="permisos[]"
                      value="{{ $permKey }}"
                      data-sec="{{ $secKey }}"
                      data-prefix="eu"
                      class="js-perm-checkbox"
                      @disabled(! $puedeAsignar)>
                    <span>
                      <strong data-i18n="perm.{{ $permKey }}.label">{{ $permInfo['label'] }}</strong>
                      <small data-i18n="perm.{{ $permKey }}.desc">{{ $permInfo['desc'] }}</small>
                    </span>
                  </label>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-edit-usuario" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" class="btn btn-ember" data-i18n="modal.save_user_btn">Guardar cambios y permisos</button>
      </div>
    </form>
  </div>
</div>
@endif

<!-- MODAL: VER INFORME DE ACTIVIDAD DE PLANTA (.TXT) -->
@if($usuario->esAdmin())
<div class="modal-overlay" id="modal-ver-informe-planta" hidden role="dialog" aria-modal="true" aria-labelledby="informe-modal-title">
  <div class="modal-swiss modal-lg">
    <div class="modal-head">
      <h2 id="informe-modal-title" data-i18n="m5.report_modal_title">Informe de actividad de planta</h2>
      <button type="button" class="modal-close" data-close-modal="modal-ver-informe-planta" aria-label="Cerrar" data-i18n-aria="modal.close_aria">&times;</button>
    </div>
    <div class="modal-body">
      <p id="informe-modal-meta" style="margin: 0 0 0.75rem; font-size: 0.88rem; color: var(--color-graphite);"></p>
      <pre id="informe-modal-pre" class="informe-txt-viewer"></pre>
    </div>
    <div class="modal-foot">
      <button type="button" class="btn btn-outline" data-close-modal="modal-ver-informe-planta" data-i18n="common.close">Cerrar</button>
      <a id="informe-modal-download" class="btn btn-ember" href="#" data-i18n="m5.btn_download_txt">Descargar .txt</a>
    </div>
  </div>
</div>
@endif
