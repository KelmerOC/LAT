@php
  $productosVentaLabels = [
    'queso_andino'    => 'Queso andino',
    'quesillo_fresco' => 'Quesillo fresco',
    'yogur_natural'   => 'Yogur natural',
    'mantequilla'     => 'Mantequilla',
    'otro'            => 'Otro producto',
  ];
  $canalesVentaLabels = [
    'feria_local'     => 'Feria local',
    'tienda_bodega'   => 'Tienda / bodega',
    'cliente_directo' => 'Cliente directo',
    'planta_interno'  => 'Planta / interno',
    'otro'            => 'Otro canal',
  ];
  $tiposSueldoLabels = [
    'sueldo_personal'   => 'Sueldo de personal',
    'ayuda_del_dia'     => 'Ayuda del día (sin DNI)',
    'adelanto_efectivo' => 'Adelanto en efectivo',
    'adelanto_queso'    => 'Adelanto en queso',
    'gasto_operativo'   => 'Gasto operativo / insumos',
  ];
  $estadosPagoLabels = [
    'pendiente' => 'Pendiente',
    'parcial'   => 'Parcial',
    'pagado'    => 'Pagado',
  ];

  $filtrosLiqParams = array_filter([
    'solo'             => 'liquidacion',
    'liq_desde'        => $filtrosLiquidacion['desde'] ?? $desde,
    'liq_hasta'        => $filtrosLiquidacion['hasta'] ?? $hasta,
    'liq_proveedor_id' => $filtrosLiquidacion['proveedor_id'] ?? '',
    'liq_sector'       => $filtrosLiquidacion['sector'] ?? '',
    'liq_estado'       => $filtrosLiquidacion['estado_pago'] ?? '',
  ]);
  $filtrosVentasParams = array_filter([
    'v_desde'        => $filtrosVentas['desde'] ?? '',
    'v_hasta'        => $filtrosVentas['hasta'] ?? '',
    'v_producto'     => $filtrosVentas['producto'] ?? '',
    'v_canal'        => $filtrosVentas['canal'] ?? '',
    'v_proveedor_id' => $filtrosVentas['proveedor_id'] ?? '',
  ]);
  $filtrosSueldosParams = array_filter([
    's_desde'        => $filtrosSueldos['desde'] ?? '',
    's_hasta'        => $filtrosSueldos['hasta'] ?? '',
    's_tipo'         => $filtrosSueldos['tipo'] ?? '',
    's_proveedor_id' => $filtrosSueldos['proveedor_id'] ?? '',
  ]);

  $hoyIso = \Carbon\Carbon::today()->toDateString();
  $sumLiqLitros = (float) $liquidaciones->sum('litros');
  $sumLiqBruto = (float) $liquidaciones->sum('bruto');
  $sumLiqAdel = (float) $liquidaciones->sum('adelantos');
  $sumLiqNeto = (float) $liquidaciones->sum('neto');

  $sumVentasCant = (int) $ventasMovimientos->sum('cantidad_unidades');
  $sumVentasTotal = (float) $ventasMovimientos->sum('monto');

  $sumSueldosCant = (int) $sueldosMovimientos->sum('cantidad_unidades');
  $sumSueldosTotal = (float) $sueldosMovimientos->sum('monto');
@endphp

<section id="sec-quincena" class="tab-panel" aria-label="Liquidación quincenal, ventas y pagos" data-i18n-aria="m4.section_aria" hidden>
  {{-- CARD 1: LIQUIDACIÓN QUINCENAL DE PROVEEDORES (HOJA EXCEL INTERACTIVA) --}}
  <div class="panel-card" id="card-liquidacion-excel" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
      <div>
        <h2>
          <span data-i18n="m4.title">Liquidación quincenal de proveedores</span>
          (<span class="num-tabular">{{ $filtrosLiquidacion['desde'] ?? $desde }}</span>
          <span data-i18n="panel.connector_to">al</span>
          <span class="num-tabular">{{ $filtrosLiquidacion['hasta'] ?? $hasta }}</span>)
        </h2>
        <p style="margin: 0;">
          <span data-i18n="m4.sub">Cálculo neto transparente: bruto menos adelantos en efectivo o queso.</span>
          <strong data-i18n="m4.sub_comm">Comisión: S/ 0.00.</strong>
        </p>
      </div>
      <div style="display: flex; flex-wrap: wrap; gap: 0.65rem;">
        @if($usuario->tienePermiso('finanzas.editar'))
          <button type="button" id="btn-toggle-edit-liq" class="btn btn-outline" aria-pressed="false">
            {!! $icoEditar !!} <span data-i18n="m4.btn_edit_liq">Editar tabla</span>
          </button>
        @endif
        @if($usuario->tienePermiso('finanzas.exportar'))
          <a id="btn-download-liq-xlsx" href="{{ route('panel.exportar', $filtrosLiqParams) }}" class="btn btn-ember">
            {!! $icoDescarga !!} <span data-i18n="m4.btn_download_liq_xlsx">Descargar Liquidación (.xlsx)</span>
          </a>
        @endif
      </div>
    </div>

    <form method="GET" action="{{ route('panel') }}#sec-quincena" class="excel-filter-bar" style="margin-bottom: 1rem;">
      <div class="form-grupo">
        <label for="f-liq-desde" class="form-label" data-i18n="m4.filter_from">Desde</label>
        <input type="date" id="f-liq-desde" name="liq_desde" class="input-lat" value="{{ $filtrosLiquidacion['desde'] ?? $desde }}">
      </div>
      <div class="form-grupo">
        <label for="f-liq-hasta" class="form-label" data-i18n="m4.filter_to">Hasta</label>
        <input type="date" id="f-liq-hasta" name="liq_hasta" class="input-lat" value="{{ $filtrosLiquidacion['hasta'] ?? $hasta }}">
      </div>
      <div class="form-grupo">
        <label for="f-liq-prov" class="form-label" data-i18n="m4.col_farmer">Ganadero</label>
        <select id="f-liq-prov" name="liq_proveedor_id" class="select-lat">
          <option value="" data-i18n="m4.filter_all_farmers">Todos los ganaderos</option>
          @foreach($proveedores as $prov)
            <option value="{{ $prov->id }}" @selected((string) ($filtrosLiquidacion['proveedor_id'] ?? '') === (string) $prov->id)>{{ $prov->codigo }} · {{ $prov->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label for="f-liq-sector" class="form-label" data-i18n="m4.col_sector">Sector</label>
        <select id="f-liq-sector" name="liq_sector" class="select-lat">
          <option value="" data-i18n="m4.filter_all_sectors">Todos los sectores</option>
          @foreach($sectoresLiquidacion as $sec)
            <option value="{{ $sec }}" @selected(($filtrosLiquidacion['sector'] ?? '') === $sec)>{{ $sec }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label for="f-liq-estado" class="form-label" data-i18n="m4.col_status">Estado</label>
        <select id="f-liq-estado" name="liq_estado" class="select-lat">
          <option value="" data-i18n="m4.filter_all_status">Todos</option>
          @foreach(\App\Models\Liquidacion::ESTADOS_PAGO as $estOp)
            <option value="{{ $estOp }}" @selected(($filtrosLiquidacion['estado_pago'] ?? '') === $estOp) data-i18n="m4.pay_status_{{ $estOp }}">{{ $estadosPagoLabels[$estOp] ?? ucfirst($estOp) }}</option>
          @endforeach
        </select>
      </div>
      <div class="excel-filter-actions">
        <button type="submit" class="btn btn-ocean btn-compacto" data-i18n="common.filter">Filtrar</button>
        <a href="{{ route('panel') }}#sec-quincena" class="btn btn-outline btn-compacto" data-i18n="common.clear">Limpiar</a>
      </div>
    </form>

    @if($usuario->tienePermiso('finanzas.ver'))
      <div id="liq-excel-wrap" class="excel-sheet-wrap">
        <div class="excel-formula-bar">
          <span>fx</span>
          <code id="liq-formula-bar">=MAX(0, F2 - G2 - H2)</code>
        </div>
        <form id="form-liq-grid" method="POST" action="{{ route('panel.finanzas.liquidacion_grid') }}" class="js-confirm-form" data-confirm-kind="save_liq_grid">
          @csrf
          @method('PUT')
          <input type="hidden" name="fecha_desde" value="{{ $filtrosLiquidacion['desde'] ?? $desde }}">
          <input type="hidden" name="fecha_hasta" value="{{ $filtrosLiquidacion['hasta'] ?? $hasta }}">

          <div class="tabla-region" role="region" aria-label="Liquidación quincenal de proveedores" data-i18n-aria="m4.title" tabindex="0">
            <table id="tabla-excel-liquidacion" class="tabla-plana excel-sheet-table" style="min-width: 920px;">
              <thead>
                <tr>
                  <th scope="col" data-i18n="m4.xcol_a">A · Código</th>
                  <th scope="col" data-i18n="m4.xcol_b">B · Ganadero</th>
                  <th scope="col" data-i18n="m4.xcol_c">C · Sector</th>
                  <th scope="col" class="num" data-i18n="m4.xcol_d">D · Litros</th>
                  <th scope="col" class="num" data-i18n="m4.xcol_e">E · Precio/L (S/)</th>
                  <th scope="col" class="num" data-i18n="m4.xcol_f">F · Bruto (=D×E)</th>
                  <th scope="col" class="num" data-i18n="m4.xcol_g">G · Adelantos (S/)</th>
                  <th scope="col" class="num" data-i18n="m4.xcol_h">H · Comisión (S/)</th>
                  <th scope="col" class="num" style="background: var(--color-sky-wash);" data-i18n="m4.xcol_i">I · Neto (=MAX(0,F-G-H))</th>
                  <th scope="col" data-i18n="m4.xcol_j">J · Estado</th>
                </tr>
              </thead>
              <tbody>
                @forelse($liquidaciones as $idx => $liq)
                  <tr class="js-liq-row" data-row-idx="{{ $idx + 2 }}">
                    <td class="num-tabular">
                      {{ $liq->codigo }}
                      <input type="hidden" name="filas[{{ $idx }}][proveedor_id]" value="{{ $liq->proveedor_id }}">
                    </td>
                    <td><strong>{{ $liq->nombre }}</strong></td>
                    <td>{{ $liq->sector }}</td>
                    <td class="num num-tabular js-liq-litros" data-litros="{{ number_format($liq->litros, 2, '.', '') }}">{{ number_format($liq->litros, 1) }} L</td>
                    <td class="num">
                      <label class="sr-only" for="liq-precio-{{ $idx }}">Precio/L {{ $liq->nombre }}</label>
                      <input type="number" step="0.05" min="0.5" max="20" id="liq-precio-{{ $idx }}" name="filas[{{ $idx }}][precio_litro]" value="{{ number_format($liq->precio_litro, 2, '.', '') }}" class="excel-cell-input js-liq-precio" list="lista-precios-litro" disabled>
                    </td>
                    <td class="num num-tabular js-liq-bruto">S/ {{ number_format($liq->bruto, 2) }}</td>
                    <td class="num">
                      <label class="sr-only" for="liq-adel-{{ $idx }}">Adelantos {{ $liq->nombre }}</label>
                      <input type="number" step="0.5" min="0" max="100000" id="liq-adel-{{ $idx }}" name="filas[{{ $idx }}][adelantos]" value="{{ number_format($liq->adelantos, 2, '.', '') }}" class="excel-cell-input js-liq-adelantos" disabled>
                    </td>
                    <td class="num num-tabular" style="color: var(--verde-text);">S/ 0.00</td>
                    <td class="num num-tabular js-liq-neto" style="font-weight: 700; background: #f7fbfe;">S/ {{ number_format($liq->neto, 2) }}</td>
                    <td>
                      <label class="sr-only" for="liq-est-{{ $idx }}">Estado {{ $liq->nombre }}</label>
                      <select id="liq-est-{{ $idx }}" name="filas[{{ $idx }}][estado_pago]" class="excel-cell-select js-liq-estado" disabled>
                        @foreach(\App\Models\Liquidacion::ESTADOS_PAGO as $estPago)
                          <option value="{{ $estPago }}" @selected(($liq->estado_pago ?? 'pendiente') === $estPago) data-i18n="m4.pay_status_{{ $estPago }}">{{ $estadosPagoLabels[$estPago] ?? ucfirst($estPago) }}</option>
                        @endforeach
                      </select>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="10" style="text-align: center; padding: 2rem; color: var(--color-graphite);" data-i18n="m4.empty_liq">No hay liquidaciones en este período.</td>
                  </tr>
                @endforelse
              </tbody>
              <tfoot>
                <tr class="excel-totals-row">
                  <td colspan="3"><strong data-i18n="m4.totals">Totales del período</strong></td>
                  <td class="num num-tabular" id="liq-sum-litros">{{ number_format($sumLiqLitros, 1) }} L</td>
                  <td class="num">—</td>
                  <td class="num num-tabular" id="liq-sum-bruto">S/ {{ number_format($sumLiqBruto, 2) }}</td>
                  <td class="num num-tabular" id="liq-sum-adelantos">S/ {{ number_format($sumLiqAdel, 2) }}</td>
                  <td class="num num-tabular" style="color: var(--verde-text);">S/ 0.00</td>
                  <td class="num num-tabular" id="liq-sum-neto" style="background: #f7fbfe;"><strong>S/ {{ number_format($sumLiqNeto, 2) }}</strong></td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <datalist id="lista-precios-litro">
            <option value="1.50">
            <option value="1.60">
            <option value="1.65">
            <option value="1.70">
            <option value="1.80">
            <option value="2.00">
          </datalist>

          @if($usuario->tienePermiso('finanzas.editar'))
            <div id="liq-edit-actions" class="excel-edit-actions" hidden>
              <span class="chip chip-amarillo" data-i18n="m4.edit_mode_active">Modo edición activo: edite precios, adelantos o estado y guarde</span>
              <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" id="btn-cancel-edit-liq" class="btn btn-outline btn-compacto" data-i18n="common.cancel">Cancelar</button>
                <button type="submit" class="btn btn-ember btn-compacto" data-i18n="m4.btn_save_liq_grid">Guardar cambios</button>
              </div>
            </div>
          @endif
        </form>
      </div>
    @endif
  </div>

  {{-- CARD 2: TABLA DE VENTAS DE PRODUCTOS (HOJA ESTILO EXCEL INDEPENDIENTE) --}}
  <div class="panel-card" id="card-ventas-excel" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
      <div>
        <h2 data-i18n="m4.sales_title">Tabla de Ventas de Productos</h2>
        <p style="margin: 0;" data-i18n="m4.sales_sub">Registro separado de ventas de queso, quesillo, yogur y mantequilla que alimentan las estadísticas de planta.</p>
      </div>
      @if($usuario->tienePermiso('finanzas.exportar'))
        <a id="btn-download-ventas-xlsx" href="{{ route('panel.exportar', array_merge(['solo' => 'ventas'], $filtrosVentasParams)) }}" class="btn btn-ember">
          {!! $icoDescarga !!} <span data-i18n="m4.btn_download_ventas_xlsx">Descargar Ventas (.xlsx)</span>
        </a>
      @endif
    </div>

    <form method="GET" action="{{ route('panel') }}#sec-quincena" class="excel-filter-bar" style="margin-bottom: 1rem;">
      <div class="form-grupo">
        <label for="f-v-desde" class="form-label" data-i18n="m4.filter_from">Desde</label>
        <input type="date" id="f-v-desde" name="v_desde" class="input-lat" value="{{ $filtrosVentas['desde'] ?? '' }}">
      </div>
      <div class="form-grupo">
        <label for="f-v-hasta" class="form-label" data-i18n="m4.filter_to">Hasta</label>
        <input type="date" id="f-v-hasta" name="v_hasta" class="input-lat" value="{{ $filtrosVentas['hasta'] ?? '' }}">
      </div>
      <div class="form-grupo">
        <label for="f-v-producto" class="form-label" data-i18n="m4.col_product">Producto</label>
        <select id="f-v-producto" name="v_producto" class="select-lat">
          <option value="" data-i18n="m4.filter_all_products">Todos</option>
          @foreach(\App\Models\Liquidacion::PRODUCTOS_VENTA as $prodKey)
            <option value="{{ $prodKey }}" @selected(($filtrosVentas['producto'] ?? '') === $prodKey) data-i18n="m4.prod_{{ $prodKey }}">{{ $productosVentaLabels[$prodKey] ?? $prodKey }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label for="f-v-canal" class="form-label" data-i18n="m4.col_channel">Canal / Ref.</label>
        <select id="f-v-canal" name="v_canal" class="select-lat">
          <option value="" data-i18n="m4.filter_all_channels">Todos</option>
          @foreach(\App\Models\Liquidacion::CANALES_VENTA as $canalKey)
            <option value="{{ $canalKey }}" @selected(($filtrosVentas['canal'] ?? '') === $canalKey) data-i18n="m4.canal_{{ $canalKey }}">{{ $canalesVentaLabels[$canalKey] ?? $canalKey }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label for="f-v-prov" class="form-label" data-i18n="m4.lbl_prov">Productor</label>
        <select id="f-v-prov" name="v_proveedor_id" class="select-lat">
          <option value="" data-i18n="m4.opt_none_plant">Todos / planta</option>
          @foreach($proveedores as $prov)
            <option value="{{ $prov->id }}" @selected((string) ($filtrosVentas['proveedor_id'] ?? '') === (string) $prov->id)>{{ $prov->codigo }} · {{ $prov->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="excel-filter-actions">
        <button type="submit" class="btn btn-ocean btn-compacto" data-i18n="common.filter">Filtrar</button>
        <a href="{{ route('panel') }}#sec-quincena" class="btn btn-outline btn-compacto" data-i18n="common.clear">Limpiar</a>
      </div>
    </form>

    <div class="excel-sheet-wrap">
      <div class="excel-formula-bar">
        <span>fx</span>
        <code id="ventas-formula-bar">=E2*F2 · Total = Cant × P.Unit</code>
      </div>

      @if($usuario->tienePermiso('finanzas.registrar'))
        <form id="form-nueva-venta" method="POST" action="{{ route('panel.movimiento') }}" class="excel-inline-create-form">
          @csrf
          <input type="hidden" name="tipo" value="venta_producto">
          <div class="form-grupo">
            <label for="nv-fecha" class="form-label" data-i18n="m4.col_date">Fecha</label>
            <input type="date" id="nv-fecha" name="fecha" class="input-lat" value="{{ $hoyIso }}" max="{{ $hoyIso }}" required>
          </div>
          <div class="form-grupo">
            <label for="nv-producto" class="form-label" data-i18n="m4.col_product">Producto</label>
            <select id="nv-producto" name="producto_categoria" class="select-lat" required>
              @foreach(\App\Models\Liquidacion::PRODUCTOS_VENTA as $prodKey)
                <option value="{{ $prodKey }}" data-i18n="m4.prod_{{ $prodKey }}">{{ $productosVentaLabels[$prodKey] ?? $prodKey }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="nv-canal" class="form-label" data-i18n="m4.col_channel">Canal / Ref.</label>
            <select id="nv-canal" name="canal_referencia" class="select-lat" required>
              @foreach(\App\Models\Liquidacion::CANALES_VENTA as $canalKey)
                <option value="{{ $canalKey }}" data-i18n="m4.canal_{{ $canalKey }}">{{ $canalesVentaLabels[$canalKey] ?? $canalKey }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="nv-prov" class="form-label" data-i18n="m4.lbl_prov">Productor (opcional)</label>
            <select id="nv-prov" name="proveedor_id" class="select-lat">
              <option value="" data-i18n="m4.opt_none_plant">Planta / venta directa</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->codigo }} · {{ $prov->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="nv-concepto" class="form-label" data-i18n="m4.col_concept">Concepto</label>
            <input type="text" id="nv-concepto" name="concepto" class="input-lat" required maxlength="160" placeholder="Ej: Venta 20 moldes feria Cusco" data-i18n-placeholder="m4.ph_concept">
          </div>
          <div class="form-grupo">
            <label for="nv-cant" class="form-label" data-i18n="m4.col_qty">Cant.</label>
            <input type="number" id="nv-cant" name="cantidad_unidades" class="input-lat js-venta-cant" min="0" max="10000" step="1" value="1">
          </div>
          <div class="form-grupo">
            <label for="nv-punit" class="form-label" data-i18n="m4.col_unit_price">P. Unit. (S/)</label>
            <input type="number" id="nv-punit" name="precio_unitario" class="input-lat js-venta-punit" min="0" max="100000" step="0.1" placeholder="18.00">
          </div>
          <div class="form-grupo">
            <label for="nv-total" class="form-label" data-i18n="m4.col_total_sale">Total (S/)</label>
            <input type="number" id="nv-total" name="monto" class="input-lat js-venta-total" min="0.5" max="1000000" step="0.1" required placeholder="0.00">
          </div>
          <div class="excel-inline-submit">
            <button type="submit" class="btn btn-ocean btn-compacto" data-i18n="m4.btn_add_sale">+ Registrar venta</button>
          </div>
        </form>
      @endif

      @if($usuario->tienePermiso('finanzas.ver'))
        <div class="tabla-region" role="region" aria-label="Tabla de Ventas de Productos" data-i18n-aria="m4.sales_title" tabindex="0">
          <table id="tabla-excel-ventas" class="tabla-plana excel-sheet-table" style="min-width: 860px;">
            <thead>
              <tr>
                <th scope="col" data-i18n="m4.col_date">Fecha</th>
                <th scope="col" data-i18n="m4.col_product">Producto</th>
                <th scope="col" data-i18n="m4.col_channel">Canal / Ref.</th>
                <th scope="col" data-i18n="m4.col_concept">Concepto</th>
                <th scope="col" class="num" data-i18n="m4.col_qty">Cant.</th>
                <th scope="col" class="num" data-i18n="m4.col_unit_price">P. Unit. (S/)</th>
                <th scope="col" class="num" style="background: var(--color-sky-wash);" data-i18n="m4.col_total_formula">Total (=Cant×P.Unit)</th>
                @if($usuario->tienePermiso('finanzas.editar'))
                  <th scope="col" data-i18n="m1.col_actions">Acciones</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse($ventasMovimientos as $vm)
                @php
                  $pUnitV = $vm->precio_unitario !== null
                    ? (float) $vm->precio_unitario
                    : (($vm->cantidad_unidades ?? 0) > 0 ? round((float) $vm->monto / $vm->cantidad_unidades, 2) : 0.0);
                @endphp
                <tr>
                  <td class="num-tabular">{{ $vm->fecha?->format('d/m/Y') }}</td>
                  <td>
                    <span class="chip chip-verde">
                      {{ $productosVentaLabels[$vm->producto_categoria] ?? ($vm->producto_categoria ?: 'Producto') }}
                    </span>
                  </td>
                  <td>
                    <span>{{ $canalesVentaLabels[$vm->canal_referencia] ?? ($vm->canal_referencia ?: 'Planta') }}</span>
                    @if($vm->proveedor)
                      <small>{{ $vm->proveedor->nombre }}</small>
                    @endif
                  </td>
                  <td>{{ $vm->concepto }}</td>
                  <td class="num num-tabular">{{ $vm->cantidad_unidades ?? 0 }}</td>
                  <td class="num num-tabular">S/ {{ number_format($pUnitV, 2) }}</td>
                  <td class="num num-tabular" style="background: #f7fbfe;"><strong>S/ {{ number_format($vm->monto, 2) }}</strong></td>
                  @if($usuario->tienePermiso('finanzas.editar'))
                    <td>
                      <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                        <button type="button" class="btn-micro js-edit-mov"
                          data-id="{{ $vm->id }}"
                          data-tipo="{{ $vm->tipo }}"
                          data-fecha="{{ $vm->fecha?->format('Y-m-d') ?? '' }}"
                          data-producto="{{ $vm->producto_categoria ?? '' }}"
                          data-canal="{{ $vm->canal_referencia ?? '' }}"
                          data-proveedor="{{ $vm->proveedor_id ?? '' }}"
                          data-concepto="{{ $vm->concepto }}"
                          data-unidades="{{ $vm->cantidad_unidades ?? 0 }}"
                          data-precio-unitario="{{ $vm->precio_unitario !== null ? number_format($vm->precio_unitario, 2, '.', '') : '' }}"
                          data-monto="{{ number_format($vm->monto, 2, '.', '') }}">
                          {!! $icoEditar !!} <span data-i18n="common.edit">Editar</span>
                        </button>
                        <form method="POST" action="{{ route('panel.movimiento.delete', $vm->id) }}"
                          class="js-confirm-form"
                          data-confirm-kind="delete"
                          data-detalle-tipo="movimiento"
                          data-detalle-nombre="{{ $vm->concepto }}"
                          data-detalle="{{ $vm->concepto }}">
                          @csrf @method('DELETE')
                          <button type="submit" class="btn-micro btn-micro-danger" data-i18n="common.delete">Eliminar</button>
                        </form>
                      </div>
                    </td>
                  @endif
                </tr>
              @empty
                <tr>
                  <td colspan="{{ $usuario->tienePermiso('finanzas.editar') ? 8 : 7 }}" style="text-align: center; padding: 1.5rem; color: var(--color-graphite);" data-i18n="m4.empty_sales">No hay ventas registradas con estos filtros.</td>
                </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr class="excel-totals-row">
                <td colspan="4"><strong data-i18n="m4.totals_sales">Total ventas filtradas</strong></td>
                <td class="num num-tabular" id="ventas-sum-cant">{{ $sumVentasCant }}</td>
                <td class="num">—</td>
                <td class="num num-tabular" id="ventas-sum-total" style="background: #f7fbfe;"><strong>S/ {{ number_format($sumVentasTotal, 2) }}</strong></td>
                @if($usuario->tienePermiso('finanzas.editar'))
                  <td></td>
                @endif
              </tr>
            </tfoot>
          </table>
        </div>
      @endif
    </div>
  </div>

  {{-- CARD 3: TABLA DE SUELDOS, JORNALES Y PAGOS OPERATIVOS (LEY N.° 29733) --}}
  <div class="panel-card" id="card-sueldos-excel" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
      <div>
        <h2 data-i18n="m4.payroll_title">Tabla de Sueldos, Jornales y Pagos Operativos</h2>
        <p style="margin: 0;" data-i18n="m4.mov_legal">Ley N.° 29733: los pagos en mano por sueldos, jornales o faenas de lavado/volteo no requieren DNI ni datos personales sensibles.</p>
      </div>
      @if($usuario->tienePermiso('finanzas.exportar'))
        <a id="btn-download-sueldos-xlsx" href="{{ route('panel.exportar', array_merge(['solo' => 'sueldos'], $filtrosSueldosParams)) }}" class="btn btn-ember">
          {!! $icoDescarga !!} <span data-i18n="m4.btn_download_sueldos_xlsx">Descargar Sueldos y Pagos (.xlsx)</span>
        </a>
      @endif
    </div>

    <form method="GET" action="{{ route('panel') }}#sec-quincena" class="excel-filter-bar" style="margin-bottom: 1rem;">
      <div class="form-grupo">
        <label for="f-s-desde" class="form-label" data-i18n="m4.filter_from">Desde</label>
        <input type="date" id="f-s-desde" name="s_desde" class="input-lat" value="{{ $filtrosSueldos['desde'] ?? '' }}">
      </div>
      <div class="form-grupo">
        <label for="f-s-hasta" class="form-label" data-i18n="m4.filter_to">Hasta</label>
        <input type="date" id="f-s-hasta" name="s_hasta" class="input-lat" value="{{ $filtrosSueldos['hasta'] ?? '' }}">
      </div>
      <div class="form-grupo">
        <label for="f-s-tipo" class="form-label" data-i18n="m4.col_category">Categoría</label>
        <select id="f-s-tipo" name="s_tipo" class="select-lat">
          <option value="" data-i18n="m4.filter_all_categories">Todas</option>
          @foreach(\App\Models\Liquidacion::TIPOS_SUELDOS_PAGOS as $tipoS)
            <option value="{{ $tipoS }}" @selected(($filtrosSueldos['tipo'] ?? '') === $tipoS) data-i18n="m4.mov_type_{{ $tipoS }}">{{ $tiposSueldoLabels[$tipoS] ?? $tipoS }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-grupo">
        <label for="f-s-prov" class="form-label" data-i18n="m4.col_beneficiary">Beneficiario</label>
        <select id="f-s-prov" name="s_proveedor_id" class="select-lat">
          <option value="" data-i18n="m4.filter_all_beneficiaries">Todos / personal sin DNI</option>
          @foreach($proveedores as $prov)
            <option value="{{ $prov->id }}" @selected((string) ($filtrosSueldos['proveedor_id'] ?? '') === (string) $prov->id)>{{ $prov->codigo }} · {{ $prov->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="excel-filter-actions">
        <button type="submit" class="btn btn-ocean btn-compacto" data-i18n="common.filter">Filtrar</button>
        <a href="{{ route('panel') }}#sec-quincena" class="btn btn-outline btn-compacto" data-i18n="common.clear">Limpiar</a>
      </div>
    </form>

    <div class="excel-sheet-wrap">
      <div class="excel-formula-bar">
        <span>fx</span>
        <code id="sueldos-formula-bar">=E2*F2 · Total = Días/Cant × Tarifa</code>
      </div>

      @if($usuario->tienePermiso('finanzas.registrar'))
        <form id="form-nuevo-sueldo" method="POST" action="{{ route('panel.movimiento') }}" class="excel-inline-create-form">
          @csrf
          <div class="form-grupo">
            <label for="ns-fecha" class="form-label" data-i18n="m4.col_date">Fecha</label>
            <input type="date" id="ns-fecha" name="fecha" class="input-lat" value="{{ $hoyIso }}" max="{{ $hoyIso }}" required>
          </div>
          <div class="form-grupo">
            <label for="ns-tipo" class="form-label" data-i18n="m4.col_category">Categoría</label>
            <select id="ns-tipo" name="tipo" class="select-lat" required>
              @foreach(\App\Models\Liquidacion::TIPOS_SUELDOS_PAGOS as $tipoS)
                <option value="{{ $tipoS }}" data-i18n="m4.mov_type_{{ $tipoS }}">{{ $tiposSueldoLabels[$tipoS] ?? $tipoS }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="ns-prov" class="form-label" data-i18n="m4.col_beneficiary">Beneficiario (opcional)</label>
            <select id="ns-prov" name="proveedor_id" class="select-lat">
              <option value="" data-i18n="m4.opt_none_plant">Personal / faena sin DNI</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->codigo }} · {{ $prov->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="ns-concepto" class="form-label" data-i18n="m4.col_concept_task">Concepto / Faena</label>
            <input type="text" id="ns-concepto" name="concepto" class="input-lat" required maxlength="160" placeholder="Ej: Sueldo maestro quesero quincena" data-i18n-placeholder="m4.ph_payroll_concept">
          </div>
          <div class="form-grupo">
            <label for="ns-cant" class="form-label" data-i18n="m4.col_days_qty">Días/Cant.</label>
            <input type="number" id="ns-cant" name="cantidad_unidades" class="input-lat js-sueldo-cant" min="0" max="10000" step="1" value="1">
          </div>
          <div class="form-grupo">
            <label for="ns-tarifa" class="form-label" data-i18n="m4.col_rate">Tarifa (S/)</label>
            <input type="number" id="ns-tarifa" name="precio_unitario" class="input-lat js-sueldo-tarifa" min="0" max="100000" step="0.5" placeholder="60.00">
          </div>
          <div class="form-grupo">
            <label for="ns-total" class="form-label" data-i18n="m4.col_total_pay">Total (S/)</label>
            <input type="number" id="ns-total" name="monto" class="input-lat js-sueldo-total" min="0.5" max="1000000" step="0.5" required placeholder="0.00">
          </div>
          <div class="excel-inline-submit">
            <button type="submit" class="btn btn-ocean btn-compacto" data-i18n="m4.btn_add_pay">+ Registrar pago</button>
          </div>
        </form>
      @endif

      @if($usuario->tienePermiso('finanzas.ver'))
        <div class="tabla-region" role="region" aria-label="Tabla de Sueldos, Jornales y Pagos Operativos" data-i18n-aria="m4.payroll_title" tabindex="0">
          <table id="tabla-excel-sueldos" class="tabla-plana excel-sheet-table" style="min-width: 860px;">
            <thead>
              <tr>
                <th scope="col" data-i18n="m4.col_date">Fecha</th>
                <th scope="col" data-i18n="m4.col_category">Categoría</th>
                <th scope="col" data-i18n="m4.col_beneficiary">Beneficiario</th>
                <th scope="col" data-i18n="m4.col_concept_task">Concepto / Faena</th>
                <th scope="col" class="num" data-i18n="m4.col_days_qty">Días/Cant.</th>
                <th scope="col" class="num" data-i18n="m4.col_rate">Tarifa (S/)</th>
                <th scope="col" class="num" style="background: var(--color-sky-wash);" data-i18n="m4.col_total_pay_formula">Total (=Días×Tarifa)</th>
                @if($usuario->tienePermiso('finanzas.editar'))
                  <th scope="col" data-i18n="m1.col_actions">Acciones</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse($sueldosMovimientos as $sm)
                @php
                  $tarifaS = $sm->precio_unitario !== null
                    ? (float) $sm->precio_unitario
                    : (($sm->cantidad_unidades ?? 0) > 0 ? round((float) $sm->monto / $sm->cantidad_unidades, 2) : 0.0);
                @endphp
                <tr>
                  <td class="num-tabular">{{ $sm->fecha?->format('d/m/Y') }}</td>
                  <td>
                    <span class="chip chip-fog" data-i18n="m4.mov_type_{{ $sm->tipo }}">{{ $tiposSueldoLabels[$sm->tipo] ?? ($tiposMovLabels[$sm->tipo] ?? $sm->tipo) }}</span>
                  </td>
                  <td>
                    @if($sm->proveedor)
                      {{ $sm->proveedor->nombre }}
                    @else
                      <span data-i18n="panel.plant_external">Planta / sin DNI</span>
                    @endif
                  </td>
                  <td>{{ $sm->concepto }}</td>
                  <td class="num num-tabular">{{ $sm->cantidad_unidades ?? 0 }}</td>
                  <td class="num num-tabular">S/ {{ number_format($tarifaS, 2) }}</td>
                  <td class="num num-tabular" style="background: #f7fbfe;"><strong>S/ {{ number_format($sm->monto, 2) }}</strong></td>
                  @if($usuario->tienePermiso('finanzas.editar'))
                    <td>
                      <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                        <button type="button" class="btn-micro js-edit-mov"
                          data-id="{{ $sm->id }}"
                          data-tipo="{{ $sm->tipo }}"
                          data-fecha="{{ $sm->fecha?->format('Y-m-d') ?? '' }}"
                          data-producto="{{ $sm->producto_categoria ?? '' }}"
                          data-canal="{{ $sm->canal_referencia ?? '' }}"
                          data-proveedor="{{ $sm->proveedor_id ?? '' }}"
                          data-concepto="{{ $sm->concepto }}"
                          data-unidades="{{ $sm->cantidad_unidades ?? 0 }}"
                          data-precio-unitario="{{ $sm->precio_unitario !== null ? number_format($sm->precio_unitario, 2, '.', '') : '' }}"
                          data-monto="{{ number_format($sm->monto, 2, '.', '') }}">
                          {!! $icoEditar !!} <span data-i18n="common.edit">Editar</span>
                        </button>
                        <form method="POST" action="{{ route('panel.movimiento.delete', $sm->id) }}"
                          class="js-confirm-form"
                          data-confirm-kind="delete"
                          data-detalle-tipo="movimiento"
                          data-detalle-nombre="{{ $sm->concepto }}"
                          data-detalle="{{ $sm->concepto }}">
                          @csrf @method('DELETE')
                          <button type="submit" class="btn-micro btn-micro-danger" data-i18n="common.delete">Eliminar</button>
                        </form>
                      </div>
                    </td>
                  @endif
                </tr>
              @empty
                <tr>
                  <td colspan="{{ $usuario->tienePermiso('finanzas.editar') ? 8 : 7 }}" style="text-align: center; padding: 1.5rem; color: var(--color-graphite);" data-i18n="m4.empty_payroll">No hay sueldos ni pagos operativos registrados con estos filtros.</td>
                </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr class="excel-totals-row">
                <td colspan="4"><strong data-i18n="m4.totals_payroll">Total sueldos y pagos filtrados</strong></td>
                <td class="num num-tabular" id="sueldos-sum-cant">{{ $sumSueldosCant }}</td>
                <td class="num">—</td>
                <td class="num num-tabular" id="sueldos-sum-total" style="background: #f7fbfe;"><strong>S/ {{ number_format($sumSueldosTotal, 2) }}</strong></td>
                @if($usuario->tienePermiso('finanzas.editar'))
                  <td></td>
                @endif
              </tr>
            </tfoot>
          </table>
        </div>
      @endif
    </div>
  </div>

  {{-- CARD 4: ENVIAR EXCEL ORDENADO AL CONTADOR + NOTIFICACIÓN VERDE + BANDEJA --}}
  <div class="panel-card" id="card-contable-bandeja">
    @if($usuario->tieneAlgunPermiso(['finanzas.enviar_excel', 'finanzas.registrar']) && $contadoresPlanta->isNotEmpty())
      <div class="panel-card on-fog" style="padding: 1.25rem; margin-bottom: 1.25rem;">
        <h3 style="font-size: 1.1rem;" data-i18n="m4.send_excel_title">Enviar Excel ordenado al Contador</h3>
        <p class="legal" data-i18n="m4.send_excel_sub">Genera un libro `.xlsx` ordenado según el tipo elegido y lo entrega directamente en la bandeja del contador con aviso verde en vivo.</p>

        <form id="form-enviar-excel-contador" method="POST" action="{{ route('panel.reportes_excel.send') }}" class="movimiento-form js-confirm-form" data-confirm-kind="send_excel">
          @csrf
          <div class="form-grupo">
            <label for="rep-destinatario" class="form-label" data-i18n="m4.lbl_accountant">Contador destinatario</label>
            <select name="destinatario_id" id="rep-destinatario" class="select-lat" required>
              @foreach($contadoresPlanta as $cont)
                <option value="{{ $cont->id }}">{{ $cont->name }} ({{ $cont->etiquetaCargo() }})</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="rep-tipo" class="form-label" data-i18n="m4.lbl_report_type">Tipo de reporte</label>
            <select name="tipo_reporte" id="rep-tipo" class="select-lat">
              <option value="liquidacion_proveedores" data-i18n="m4.rep_liq_prov">Liquidación quincenal de proveedores</option>
              <option value="ventas_productos" data-i18n="m4.rep_sales_prod">Tabla de ventas de productos</option>
              <option value="sueldos_pagos" data-i18n="m4.rep_payroll">Sueldos, jornales y pagos operativos</option>
              <option value="balance_quincenal" data-i18n="m4.rep_balance">Balance quincenal completo</option>
            </select>
          </div>
          <div class="form-grupo">
            <label for="rep-prov" class="form-label" data-i18n="m4.lbl_prov_opt">Productor (opcional)</label>
            <select name="proveedor_id" id="rep-prov" class="select-lat">
              <option value="" data-i18n="m4.filter_all_farmers">Todos los productores</option>
              @foreach($proveedores as $prov)
                <option value="{{ $prov->id }}">{{ $prov->codigo }} · {{ $prov->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="rep-desde" class="form-label" data-i18n="m4.filter_from">Desde</label>
            <input type="date" name="fecha_desde" id="rep-desde" class="input-lat" value="{{ $desde }}" required>
          </div>
          <div class="form-grupo">
            <label for="rep-hasta" class="form-label" data-i18n="m4.filter_to">Hasta</label>
            <input type="date" name="fecha_hasta" id="rep-hasta" class="input-lat" value="{{ $hasta }}" required>
          </div>
          <div class="form-grupo">
            <label for="rep-notas" class="form-label" data-i18n="m4.lbl_accountant_note">Nota para el contador</label>
            <input type="text" name="notas" id="rep-notas" class="input-lat" maxlength="255" placeholder="Ej: Cierre quincenal ventas feria">
          </div>
          <button type="submit" class="btn btn-ember" data-i18n="m4.btn_send_excel">Enviar Excel al contador</button>
        </form>
      </div>
    @endif

    @if($destinatariosContables->isNotEmpty())
      <div class="panel-card panel-card-verde on-fog" style="padding: 1.25rem; margin-bottom: 1.25rem;">
        <h3 style="font-size: 1.05rem;" data-i18n="m4.notif_accounting_title">Aviso contable directo (Notificación verde)</h3>
        <p class="legal" data-i18n="m4.notif_accounting_sub">Envía un aviso resaltado en verde entre Contador, Encargado de Planta y Administración.</p>

        <form id="form-notif-contable" method="POST" action="{{ route('panel.notificaciones.contable') }}" class="movimiento-form js-confirm-form" data-confirm-kind="send_accounting_notif">
          @csrf
          <div class="form-grupo">
            <label for="nc-destinatario" class="form-label" data-i18n="m4.lbl_recipient">Destinatario</label>
            <select name="destinatario_id" id="nc-destinatario" class="select-lat" required>
              @foreach($destinatariosContables as $dest)
                <option value="{{ $dest->id }}">{{ $dest->name }} ({{ $dest->etiquetaCargo() }})</option>
              @endforeach
            </select>
          </div>
          <div class="form-grupo">
            <label for="nc-asunto" class="form-label" data-i18n="m4.lbl_subject">Asunto</label>
            <input type="text" name="asunto" id="nc-asunto" class="input-lat" required maxlength="120" placeholder="Ej: Conformidad de cierre quincenal" data-i18n-placeholder="m4.ph_subject">
          </div>
          <div class="form-grupo movimiento-form-wide">
            <label for="nc-mensaje" class="form-label" data-i18n="m4.lbl_message">Mensaje</label>
            <input type="text" name="mensaje" id="nc-mensaje" class="input-lat" required maxlength="300" placeholder="Ej: Revisar hoja de ventas antes del cierre de caja" data-i18n-placeholder="m4.ph_message">
          </div>
          <button type="submit" class="btn btn-verde" data-i18n="m4.btn_send_accounting_notif">Enviar notificación verde</button>
        </form>
      </div>
    @endif

    @if($usuario->tieneAlgunPermiso(['finanzas.ver', 'finanzas.exportar', 'finanzas.enviar_excel']))
      <div class="panel-tabla-wrap">
        <div class="panel-tabla-cab">
          <h3 data-i18n="m4.tray_title">Bandeja de reportes Excel (Productor / Ventas ↔ Contador)</h3>
          <span class="chip chip-celeste">{{ $reportesExcel->count() }} archivo(s)</span>
        </div>
        <div class="tabla-region" role="region" aria-label="Bandeja de reportes Excel" tabindex="0">
          <table class="tabla-plana">
            <thead>
              <tr>
                <th scope="col" data-i18n="m4.tray_col_date">Fecha envío</th>
                <th scope="col" data-i18n="m4.tray_col_route">Remitente → Destinatario</th>
                <th scope="col" data-i18n="m4.tray_col_detail">Detalle y período</th>
                <th scope="col" data-i18n="m4.col_status">Estado</th>
                <th scope="col" data-i18n="m1.col_actions">Acciones</th>
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
                      <a href="{{ route('panel.reportes_excel.download', $rep->id) }}" class="btn-micro">{!! $icoDescarga !!} <span data-i18n="m4.btn_download_xlsx">Descargar .xlsx</span></a>
                      @if($rep->estado !== 'revisado' && ($usuario->esAdmin() || $rep->destinatario_id === $usuario->id))
                        <form method="POST" action="{{ route('panel.reportes_excel.review', $rep->id) }}" class="js-confirm-form" data-confirm-kind="review_excel" data-archivo="{{ $rep->nombre_archivo }}">
                          @csrf
                          <button type="submit" class="btn-micro" data-i18n="m4.btn_mark_reviewed">Marcar revisado</button>
                        </form>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" style="text-align: center; padding: 1.5rem; color: var(--color-graphite);" data-i18n="m4.tray_empty">No hay reportes Excel enviados todavía en esta planta.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    @endif
  </div>
</section>
