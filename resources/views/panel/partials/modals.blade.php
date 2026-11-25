<!-- MODAL GLOBAL DE CONFIRMACIÓN JS -->
<div class="modal-overlay" id="modal-confirmacion-lat" hidden role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="confirm-modal-title" data-i18n="confirm.title">Confirmar cambio</h2>
      <button type="button" class="modal-close" data-close-modal="modal-confirmacion-lat" aria-label="Cerrar">&times;</button>
    </div>
    <div class="modal-body">
      <p id="confirm-modal-message" style="font-size: 1rem; color: var(--color-ink); margin: 0;"></p>
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
      <button type="button" class="modal-close" data-close-modal="modal-editar-datos" aria-label="Cerrar">&times;</button>
    </div>
    <div class="modal-body">
      <p style="margin-bottom: 1rem; color: var(--color-graphite);" data-i18n="edit_modal.sub">Corrija aquí entregas, movimientos o ganaderos si ocurrió un error al registrar.</p>

      @php
        $primeraSubtab = $usuario->tienePermiso('acopio.editar') ? 'subtab-acopios'
          : ($usuario->tienePermiso('finanzas.editar') ? 'subtab-movimientos' : 'subtab-ganaderos');
      @endphp

      <div class="subtabs-edit" role="tablist" aria-label="Categorías de corrección">
        @if($usuario->tienePermiso('acopio.editar'))
          <button type="button" role="tab" id="tab-btn-subtab-acopios" aria-controls="subtab-acopios" aria-selected="{{ $primeraSubtab === 'subtab-acopios' ? 'true' : 'false' }}" class="subtab-btn {{ $primeraSubtab === 'subtab-acopios' ? 'activo' : '' }}" data-subtab="subtab-acopios">Entregas de hoy ({{ $acopiosHoy->count() }})</button>
        @endif
        @if($usuario->tienePermiso('finanzas.editar'))
          <button type="button" role="tab" id="tab-btn-subtab-movimientos" aria-controls="subtab-movimientos" aria-selected="{{ $primeraSubtab === 'subtab-movimientos' ? 'true' : 'false' }}" class="subtab-btn {{ $primeraSubtab === 'subtab-movimientos' ? 'activo' : '' }}" data-subtab="subtab-movimientos">Movimientos y ventas ({{ $movimientos->count() }})</button>
        @endif
        @if($usuario->tienePermiso('acopio.proveedores'))
          <button type="button" role="tab" id="tab-btn-subtab-ganaderos" aria-controls="subtab-ganaderos" aria-selected="{{ $primeraSubtab === 'subtab-ganaderos' ? 'true' : 'false' }}" class="subtab-btn {{ $primeraSubtab === 'subtab-ganaderos' ? 'activo' : '' }}" data-subtab="subtab-ganaderos">Ganaderos ({{ $proveedores->count() }})</button>
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
                  <td>{{ $a->calidad }}</td>
                  <td style="display: flex; gap: 0.35rem;">
                    <button type="button" class="btn-micro js-edit-acopio"
                      data-id="{{ $a->id }}"
                      data-proveedor="{{ $a->proveedor_id }}"
                      data-litros="{{ $a->litros }}"
                      data-calidad="{{ $a->calidad }}"
                      data-metodo="{{ $a->metodo }}"
                      data-hora="{{ substr($a->hora, 0, 5) }}" data-i18n="common.edit">Editar</button>
                    <form method="POST" action="{{ route('panel.acopio.delete', $a->id) }}" class="js-confirm-form" data-confirm-kind="delete" data-detalle="acopio de {{ $a->proveedor->nombre }}">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-micro btn-micro-danger" data-i18n="common.erase">Borrar</button>
                    </form>
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
                  <td>{{ $tiposMovLabels[$m->tipo] ?? $m->tipo }}</td>
                  <td>{{ $m->concepto }}</td>
                  <td class="num">S/ {{ number_format($m->monto, 2) }}</td>
                  <td>
                    <button type="button" class="btn-micro js-edit-mov"
                      data-id="{{ $m->id }}"
                      data-tipo="{{ $m->tipo }}"
                      data-proveedor="{{ $m->proveedor_id ?? '' }}"
                      data-concepto="{{ $m->concepto }}"
                      data-unidades="{{ $m->cantidad_unidades ?? 0 }}"
                      data-monto="{{ $m->monto }}" data-i18n="common.edit">Editar</button>
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
            <input type="text" id="np-prv-cod" name="codigo" class="input-lat" required placeholder="PRV-06" style="min-height: 40px;">
          </div>
          <div>
            <label class="form-label" for="np-prv-nom" style="font-size: 0.78rem;" data-i18n="modal.prov_name">Nombre del ganadero</label>
            <input type="text" id="np-prv-nom" name="nombre" class="input-lat" required placeholder="Sra. Elena Quispe" style="min-height: 40px;">
          </div>
          <div>
            <label class="form-label" for="np-prv-sec" style="font-size: 0.78rem;" data-i18n="modal.prov_sector">Sector</label>
            <input type="text" id="np-prv-sec" name="sector" class="input-lat" placeholder="Markjo" style="min-height: 40px;">
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
                    <button type="button" class="btn-micro js-edit-proveedor"
                      data-id="{{ $pr->id }}"
                      data-codigo="{{ $pr->codigo }}"
                      data-nombre="{{ $pr->nombre }}"
                      data-sector="{{ $pr->sector }}" data-i18n="common.edit">Editar</button>
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
      <button type="button" class="modal-close" data-close-modal="modal-edit-proveedor" aria-label="Cerrar">&times;</button>
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
      <h2 id="title-edit-acopio">Corregir entrega de leche</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-acopio" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" id="form-edit-acopio" action="">
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
        <div class="grid-form-2">
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-litros" data-i18n="m3.lbl_liters">Litros (L)</label>
            <input type="number" step="0.1" min="0.5" max="5000" name="litros" id="edit-ac-litros" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-hora" data-i18n="m1.col_time">Hora</label>
            <input type="time" name="hora" id="edit-ac-hora" class="input-lat">
          </div>
        </div>
        <div class="grid-form-2">
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-calidad" data-i18n="m1.col_quality">Calidad</label>
            <select name="calidad" id="edit-ac-calidad" class="select-lat">
              <option value="buena">Aprobada (Tina principal)</option>
              <option value="acida">Ácida (Quesillo 4 días)</option>
              <option value="pendiente">Pendiente</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-ac-metodo" data-i18n="m1.col_method">Método</label>
            <select name="metodo" id="edit-ac-metodo" class="select-lat">
              <option value="manual">Manual</option>
              <option value="voz">Voz</option>
            </select>
          </div>
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
      <h2 id="title-edit-mov">Corregir movimiento / venta</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-mov" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" id="form-edit-mov" action="">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="form-grupo">
          <label class="form-label" for="edit-mov-tipo" data-i18n="m4.lbl_type">Tipo</label>
          <select name="tipo" id="edit-mov-tipo" class="select-lat">
            <option value="venta_producto">Venta de producto lácteo</option>
            <option value="adelanto_efectivo">Adelanto en efectivo</option>
            <option value="adelanto_queso">Adelanto en moldes de queso</option>
            <option value="ayuda_del_dia">Ayuda del día (sin DNI)</option>
            <option value="gasto_operativo">Gasto operativo</option>
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="edit-mov-prov" data-i18n="m4.lbl_prov">Productor (si aplica)</label>
          <select name="proveedor_id" id="edit-mov-prov" class="select-lat">
            <option value="">Ninguno / planta</option>
            @foreach($proveedores as $prov)
              <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="edit-mov-concepto" data-i18n="m4.lbl_concept">Concepto</label>
          <input type="text" name="concepto" id="edit-mov-concepto" class="input-lat" required maxlength="160">
        </div>
        <div class="grid-form-2">
          <div class="form-grupo">
            <label class="form-label" for="edit-mov-unidades" data-i18n="m4.lbl_units">Unidades / Moldes</label>
            <input type="number" min="0" name="cantidad_unidades" id="edit-mov-unidades" class="input-lat">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="edit-mov-monto" data-i18n="m4.lbl_amount">Monto (S/)</label>
            <input type="number" step="0.5" min="0.5" name="monto" id="edit-mov-monto" class="input-lat" required>
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
      <h2 id="title-nueva-tina">Agregar nueva tina</h2>
      <button type="button" class="modal-close" data-close-modal="modal-nueva-tina" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" action="{{ route('panel.tinas.store') }}">
      @csrf
      <div class="modal-body">
        <div class="grid-form-2-code">
          <div class="form-grupo">
            <label class="form-label" for="nt-codigo">Código</label>
            <input type="text" name="codigo" id="nt-codigo" class="input-lat" required placeholder="TINA-04">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nt-nombre">Nombre de la tina</label>
            <input type="text" name="nombre" id="nt-nombre" class="input-lat" required placeholder="Tina Queso Fresco">
          </div>
        </div>
        <div class="grid-form-2">
          <div class="form-grupo">
            <label class="form-label" for="nt-cap">Capacidad (L)</label>
            <input type="number" step="10" min="20" max="10000" name="capacidad_litros" id="nt-cap" class="input-lat" value="300" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nt-tipo">Tipo de uso</label>
            <select name="tipo" id="nt-tipo" class="select-lat">
              <option value="principal_queso">Queso principal</option>
              <option value="quesillo">Quesillo 4 días</option>
              <option value="yogur">Yogur</option>
              <option value="multiproposito">Multipropósito</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-nueva-tina" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" class="btn btn-ember">Crear tina</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modal-edit-tina" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-tina">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-edit-tina" data-i18n="m3.edit_tina">Editar tina</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-tina" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" id="form-edit-tina" action="">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="grid-form-2-code">
          <div class="form-grupo">
            <label class="form-label" for="et-codigo">Código</label>
            <input type="text" name="codigo" id="et-codigo" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="et-nombre">Nombre</label>
            <input type="text" name="nombre" id="et-nombre" class="input-lat" required>
          </div>
        </div>
        <div class="grid-form-2">
          <div class="form-grupo">
            <label class="form-label" for="et-cap">Capacidad (L)</label>
            <input type="number" step="10" min="20" max="10000" name="capacidad_litros" id="et-cap" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="et-tipo">Tipo</label>
            <select name="tipo" id="et-tipo" class="select-lat">
              <option value="principal_queso">Queso principal</option>
              <option value="quesillo">Quesillo 4 días</option>
              <option value="yogur">Yogur</option>
              <option value="multiproposito">Multipropósito</option>
            </select>
          </div>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="et-activa">Estado operativo</label>
          <select name="activa" id="et-activa" class="select-lat">
            <option value="1">Activa</option>
            <option value="0">Inactiva / Mantenimiento</option>
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
@endif

<!-- MODALES DE PLANTAS -->
@if($usuario->tienePermiso('admin.plantas'))
<div class="modal-overlay" id="modal-nueva-planta" hidden role="dialog" aria-modal="true" aria-labelledby="title-nueva-planta">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-nueva-planta">Agregar nueva planta</h2>
      <button type="button" class="modal-close" data-close-modal="modal-nueva-planta" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.plantas.store') }}">
      @csrf
      <div class="modal-body">
        <div class="grid-form-2-code">
          <div class="form-grupo">
            <label class="form-label" for="np-cod" data-i18n="m6.col_code">Código</label>
            <input type="text" name="codigo" id="np-cod" class="input-lat" required placeholder="PLT-04">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="np-nom" data-i18n="m6.col_plant_name">Nombre de planta</label>
            <input type="text" name="nombre" id="np-nom" class="input-lat" required placeholder="Quesería Maras">
          </div>
        </div>
        <div class="grid-form-2-wide">
          <div class="form-grupo">
            <label class="form-label" for="np-ubi" data-i18n="m6.col_location">Ubicación</label>
            <input type="text" name="ubicacion" id="np-ubi" class="input-lat" required value="Cusco Provincia">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="np-pre" data-i18n="m6.col_price">Precio / L (S/)</label>
            <input type="number" step="0.05" min="0.5" max="20" name="precio_litro" id="np-pre" class="input-lat" value="1.65" required>
          </div>
        </div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-outline" data-close-modal="modal-nueva-planta" data-i18n="common.cancel">Cancelar</button>
        <button type="submit" class="btn btn-ember">Crear planta</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modal-edit-planta" hidden role="dialog" aria-modal="true" aria-labelledby="title-edit-planta">
  <div class="modal-swiss modal-sm">
    <div class="modal-head">
      <h2 id="title-edit-planta">Editar planta láctea</h2>
      <button type="button" class="modal-close" data-close-modal="modal-edit-planta" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" id="form-edit-planta" action="">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="grid-form-2-code">
          <div class="form-grupo">
            <label class="form-label" for="ep-cod" data-i18n="m6.col_code">Código</label>
            <input type="text" name="codigo" id="ep-cod" class="input-lat" required>
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
            <input type="number" step="0.05" min="0.5" max="20" name="precio_litro" id="ep-pre" class="input-lat" required>
          </div>
        </div>
        <div class="form-grupo">
          <label class="form-label" for="ep-act" data-i18n="m6.col_status">Estado</label>
          <select name="activa" id="ep-act" class="select-lat">
            <option value="1">Activa</option>
            <option value="0">Inactiva</option>
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
      <button type="button" class="modal-close" data-close-modal="modal-nuevo-usuario" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.usuarios.store') }}" class="js-user-perm-form" data-prefix="nu">
      @csrf
      <input type="hidden" name="permisos_present" value="1">
      <div class="modal-body">
        <div class="grid-form-3">
          <div class="form-grupo">
            <label class="form-label" for="nu-name">Nombre completo</label>
            <input type="text" name="name" id="nu-name" class="input-lat" required placeholder="Ej: Juana Condori">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nu-user">Usuario de acceso</label>
            <input type="text" name="usuario" id="nu-user" class="input-lat" required placeholder="juana.acopio">
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nu-pass">Contraseña inicial</label>
            <input type="password" name="password" id="nu-pass" class="input-lat" required minlength="6" placeholder="Mín. 6 caracteres">
          </div>
        </div>

        <div class="grid-form-4">
          <div class="form-grupo">
            <label class="form-label" for="nu-cargo">Plantilla / Cargo</label>
            <select name="cargo" id="nu-cargo" class="select-lat js-cargo-template" data-prefix="nu">
              <option value="personalizado">Personalizado (Manual)</option>
              <option value="recolector_ruta">Recolector en ruta</option>
              <option value="control_tina">Control de tina / calidad</option>
              <option value="encargado_planta">Encargado de planta</option>
              <option value="contador_externo">Contador externo</option>
              @if($usuario->esAdmin())
                <option value="administracion">Administración total</option>
              @endif
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nu-rango">Rango jerárquico</label>
            <select name="rango" id="nu-rango" class="select-lat">
              <option value="personalizado">Acceso Personalizado</option>
              <option value="operador">Operador</option>
              <option value="supervisor">Supervisor con Rango</option>
              @if($usuario->esAdmin())
                <option value="administrador">Administrador Total</option>
              @endif
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="nu-planta">Planta asignada</label>
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
            <input type="email" name="email" id="nu-email" class="input-lat" placeholder="opcional@planta.pe">
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
      <button type="button" class="modal-close" data-close-modal="modal-edit-usuario" aria-label="Cerrar">&times;</button>
    </div>
    <form method="POST" id="form-edit-usuario" action="" class="js-user-perm-form" data-prefix="eu">
      @csrf @method('PUT')
      <input type="hidden" name="permisos_present" value="1">
      <div class="modal-body">
        <div class="grid-form-3">
          <div class="form-grupo">
            <label class="form-label" for="eu-name">Nombre completo</label>
            <input type="text" name="name" id="eu-name" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="eu-user">Usuario</label>
            <input type="text" name="usuario" id="eu-user" class="input-lat" required>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="eu-pass">Nueva contraseña (opcional)</label>
            <input type="password" name="password" id="eu-pass" class="input-lat" minlength="6" placeholder="Mantener actual">
          </div>
        </div>

        <div class="grid-form-4">
          <div class="form-grupo">
            <label class="form-label" for="eu-cargo">Plantilla / Cargo</label>
            <select name="cargo" id="eu-cargo" class="select-lat js-cargo-template" data-prefix="eu">
              <option value="personalizado">Personalizado (Manual)</option>
              <option value="recolector_ruta">Recolector en ruta</option>
              <option value="control_tina">Control de tina / calidad</option>
              <option value="encargado_planta">Encargado de planta</option>
              <option value="contador_externo">Contador externo</option>
              @if($usuario->esAdmin())
                <option value="administracion">Administración total</option>
              @endif
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="eu-rango">Rango</label>
            <select name="rango" id="eu-rango" class="select-lat">
              <option value="personalizado">Acceso Personalizado</option>
              <option value="operador">Operador</option>
              <option value="supervisor">Supervisor con Rango</option>
              @if($usuario->esAdmin())
                <option value="administrador">Administrador Total</option>
              @endif
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label" for="eu-planta">Planta</label>
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
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
        </div>

        <div class="form-grupo">
          <label class="form-label" for="eu-email" data-i18n="modal.email_opt">Correo (opcional)</label>
          <input type="email" name="email" id="eu-email" class="input-lat" value="" placeholder="opcional@planta.pe">
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
