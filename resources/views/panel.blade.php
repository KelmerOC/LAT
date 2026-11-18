@extends('layouts.app')

@section('titulo', 'LAT · Mi Panel Operativo')
@section('sin_chrome_publico', '1')
@section('body_class', 'dash-body')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/lat-dashboard.css') }}">
@endpush

@php
  $icoDash = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>';
  $icoMic = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M19 10v1a7 7 0 0 1-14 0v-1M12 18v4"/></svg>';
  $icoMicS = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M19 10v1a7 7 0 0 1-14 0v-1M12 18v4"/></svg>';
  $icoTec = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 10h.01M10 10h.01M14 10h.01M18 10h.01M7 14h10"/></svg>';
  $icoOk = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
  $icoAlerta = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 20h20L12 3Z"/><path d="M12 10v4M12 17h.01"/></svg>';
  $icoDescarga = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>';
  $icoEditar = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
  $icoTina = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/></svg>';
  $icoBalance = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M9 14h6M9 18h6M9 10h2"/></svg>';
  $icoStats = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 16v-5M12 16V8M17 16v-8"/></svg>';
  $icoAdmin = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>';

  $tiposTinaLabels = [
    'principal_queso' => 'Queso principal',
    'quesillo'        => 'Quesillo 4 días',
    'yogur'           => 'Yogur',
    'multiproposito'  => 'Multipropósito',
  ];
  $tiposMovLabels = [
    'adelanto_efectivo' => 'Adelanto efectivo',
    'adelanto_queso'    => 'Adelanto queso',
    'ayuda_del_dia'     => 'Ayuda del día (sin DNI)',
    'gasto_operativo'   => 'Gasto operativo',
    'venta_producto'    => 'Venta de producto',
  ];

  $mapaIdSeccion = [
    'dashboard'    => 'sec-dashboard',
    'acopio'       => 'sec-acopio',
    'calidad'      => 'sec-calidad',
    'tinas'        => 'sec-tina',
    'finanzas'     => 'sec-quincena',
    'estadisticas' => 'sec-estadisticas',
    'admin'        => 'sec-admin',
  ];
  $domSeccionInicial = $mapaIdSeccion[$seccionInicial] ?? 'sec-sin-permisos';
@endphp

@section('contenido')
<div class="dash-shell">
  @include('panel.partials.sidebar')

  <div class="dash-main">
    @include('panel.partials.topbar')

    <div class="dash-content">
      @if(session('exito') || session('info') || $errors->any())
        <div style="margin-bottom: 1.1rem;">
          @if(session('exito'))
            <div class="alerta alerta-verde" role="status">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
              <span>{{ session('exito') }}</span>
            </div>
          @endif
          @if(session('info'))
            <div class="alerta alerta-celeste" role="status">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
              <span>{{ session('info') }}</span>
            </div>
          @endif
          @if($errors->any())
            <div class="alerta alerta-roja" role="alert">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 20h20L12 3Z"/><path d="M12 10v4M12 17h.01"/></svg>
              <div>
                <strong>Revise los siguientes campos:</strong>
                <ul>
                  @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                  @endforeach
                </ul>
              </div>
            </div>
          @endif
        </div>
      @endif

      @if($usuario->puedeVerSeccion('dashboard') && $dashboardData)
        @include('panel.sections.dashboard')
      @endif

      @if($usuario->puedeVerSeccion('acopio'))
        @include('panel.sections.acopio')
      @endif

      @if($usuario->puedeVerSeccion('calidad'))
        @include('panel.sections.calidad')
      @endif

      @if($usuario->puedeVerSeccion('tinas'))
        @include('panel.sections.tinas')
      @endif

      @if($usuario->puedeVerSeccion('finanzas'))
        @include('panel.sections.finanzas')
      @endif

      @if($usuario->puedeVerSeccion('estadisticas') && $estadisticas)
        @include('panel.sections.estadisticas')
      @endif

      @if($usuario->puedeVerSeccion('admin'))
        @include('panel.sections.admin')
      @endif

      @if(empty($seccionesPermitidas))
        <section id="sec-sin-permisos" class="dash-card" style="max-width: 560px; margin: 2rem auto; text-align: center; padding: 2.5rem;">
          <h2>Sin secciones asignadas</h2>
          <p style="margin-top: 0.75rem;">Tu cuenta (<strong>{{ $usuario->usuario }}</strong>) está activa pero aún no tiene secciones ni controles habilitados. Solicita al administrador de tu planta que asigne tus permisos.</p>
        </section>
      @endif
    </div>
  </div>
</div>

@include('panel.partials.modals')
@endsection

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
  // 1. Navegación lateral de secciones + Drawer móvil + Persistencia por usuario
  (function () {
    const navBtns = Array.from(document.querySelectorAll('#dash-nav-list .dash-nav-btn'));
    const panels = Array.from(document.querySelectorAll('.tab-panel'));
    const topbarTitle = document.getElementById('topbar-section-title');
    const sidebar = document.getElementById('dash-sidebar');
    const backdrop = document.getElementById('dash-sidebar-backdrop');
    const btnOpen = document.getElementById('btn-sidebar-open');
    const btnClose = document.getElementById('btn-sidebar-close');
    const storageKey = 'lat_active_section_u{{ $usuario->id }}';
    const defaultSection = @json($domSeccionInicial);

    function toggleSidebar(open) {
      if (!sidebar) return;
      sidebar.classList.toggle('abierto', open);
      if (backdrop) backdrop.classList.toggle('visible', open);
      if (btnOpen) btnOpen.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function activarSeccion(sectionId) {
      let target = document.getElementById(sectionId);
      if (!target && panels.length > 0) {
        target = document.getElementById(defaultSection) || panels[0];
        sectionId = target ? target.id : '';
      }
      if (!target) return;

      panels.forEach(p => { p.hidden = p.id !== sectionId; });

      navBtns.forEach(btn => {
        const activa = btn.dataset.section === sectionId;
        btn.classList.toggle('activo', activa);
        if (activa) {
          btn.setAttribute('aria-current', 'page');
          if (topbarTitle) topbarTitle.textContent = btn.dataset.titulo || 'Mi Panel Operativo';
        } else {
          btn.removeAttribute('aria-current');
        }
      });

      toggleSidebar(false);
      try { sessionStorage.setItem(storageKey, sectionId); } catch (_) {}
      window.dispatchEvent(new Event('resize'));
    }

    navBtns.forEach(btn => {
      btn.addEventListener('click', () => activarSeccion(btn.dataset.section));
    });

    document.querySelectorAll('.js-jump-section').forEach(btn => {
      btn.addEventListener('click', () => activarSeccion(btn.dataset.section));
    });

    if (btnOpen) btnOpen.addEventListener('click', () => toggleSidebar(true));
    if (btnClose) btnClose.addEventListener('click', () => toggleSidebar(false));
    if (backdrop) backdrop.addEventListener('click', () => toggleSidebar(false));

    let inicial = defaultSection;
    try {
      const saved = sessionStorage.getItem(storageKey);
      if (saved && document.getElementById(saved)) {
        inicial = saved;
      }
    } catch (_) {}
    activarSeccion(inicial);
  })();

  // 2. Gráficas interactivas Chart.js (Dashboard y Estadísticas)
  (function () {
    if (typeof Chart === 'undefined') return;
    Chart.defaults.font.family = "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
    Chart.defaults.color = '#4b6276';

    @if($dashboardData)
      const dashSeries = @json($dashboardData['series']);

      const ctxAcopio = document.getElementById('chart-acopio-diario');
      if (ctxAcopio) {
        new Chart(ctxAcopio, {
          type: 'bar',
          data: {
            labels: dashSeries.labels,
            datasets: [
              {
                label: 'Óptima (L)',
                data: dashSeries.acopio_buena,
                backgroundColor: '#0284c7',
                borderRadius: 4,
              },
              {
                label: 'Ácida · Quesillo (L)',
                data: dashSeries.acopio_acida,
                backgroundColor: '#c2410c',
                borderRadius: 4,
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } } },
            scales: {
              x: { stacked: true, grid: { display: false } },
              y: { stacked: true, beginAtZero: true, grid: { color: '#eef2f6' } }
            }
          }
        });
      }

      const ctxVentas = document.getElementById('chart-ventas-gastos');
      if (ctxVentas) {
        new Chart(ctxVentas, {
          type: 'line',
          data: {
            labels: dashSeries.labels,
            datasets: [
              {
                label: 'Ventas (S/)',
                data: dashSeries.ventas,
                borderColor: '#15803d',
                backgroundColor: 'rgba(21, 128, 61, 0.12)',
                fill: true,
                tension: 0.32,
                borderWidth: 2.5,
                pointRadius: 3.5,
              },
              {
                label: 'Gastos (S/)',
                data: dashSeries.gastos,
                borderColor: '#0a2f4d',
                backgroundColor: 'transparent',
                borderDash: [5, 4],
                tension: 0.25,
                borderWidth: 2,
                pointRadius: 3,
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } } },
            scales: {
              x: { grid: { display: false } },
              y: { beginAtZero: true, grid: { color: '#eef2f6' } }
            }
          }
        });
      }

      const ctxMoldes = document.getElementById('chart-moldes-diarios');
      if (ctxMoldes) {
        new Chart(ctxMoldes, {
          type: 'line',
          data: {
            labels: dashSeries.labels,
            datasets: [{
              label: 'Moldes producidos',
              data: dashSeries.moldes,
              borderColor: '#c2410c',
              backgroundColor: 'rgba(194, 65, 12, 0.12)',
              fill: true,
              tension: 0.3,
              borderWidth: 2.5,
              pointRadius: 4,
              pointBackgroundColor: '#0a2f4d',
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              x: { grid: { display: false } },
              y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef2f6' } }
            }
          }
        });
      }
    @endif

    @if($estadisticas)
      const ctxStatsCalidad = document.getElementById('chart-estadisticas-calidad');
      if (ctxStatsCalidad) {
        new Chart(ctxStatsCalidad, {
          type: 'doughnut',
          data: {
            labels: ['Óptima (Tina)', 'Ácida (Quesillo)', 'Pendiente'],
            datasets: [{
              data: [
                {{ (float) $estadisticas['calidad']['buena_litros'] }},
                {{ (float) $estadisticas['calidad']['acida_litros'] }},
                {{ (float) $estadisticas['calidad']['pendiente_litros'] }}
              ],
              backgroundColor: ['#15803d', '#c2410c', '#0284c7'],
              borderWidth: 2,
              borderColor: '#ffffff'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '64%',
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } } }
          }
        });
      }
    @endif
  })();

  // 3. Modales, Confirmación JS y Matriz de Permisos de Usuarios Personalizados
  (function () {
    let pendingForm = null;
    const modalConfirm = document.getElementById('modal-confirmacion-lat');
    const confirmMsgEl = document.getElementById('confirm-modal-message');
    const btnConfirmar = document.getElementById('btn-confirmar-accion-js');

    function abrirModal(id) {
      const el = document.getElementById(id);
      if (!el) return;
      el.hidden = false;
      const focusable = el.querySelector('input:not([type="hidden"]):not([readonly]), select, button:not(.modal-close)');
      if (focusable) focusable.focus();
    }

    function cerrarModal(id) {
      const el = document.getElementById(id);
      if (el) el.hidden = true;
      if (id === 'modal-confirmacion-lat') pendingForm = null;
    }

    document.querySelectorAll('[data-open-modal]').forEach(btn => {
      btn.addEventListener('click', () => abrirModal(btn.dataset.openModal));
    });
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
      btn.addEventListener('click', () => cerrarModal(btn.dataset.closeModal));
    });
    document.querySelectorAll('.modal-overlay').forEach(ov => {
      ov.addEventListener('click', e => {
        if (e.target === ov) cerrarModal(ov.id);
      });
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay:not([hidden])').forEach(ov => cerrarModal(ov.id));
      }
    });

    // Interceptor JS para Calidad Sanitaria, Cierre de Lote y Eliminaciones
    document.addEventListener('submit', function (e) {
      const form = e.target;
      if (!form.classList || !form.classList.contains('js-confirm-form')) return;
      if (form.dataset.confirmed === 'true') return;

      e.preventDefault();
      pendingForm = form;

      const kind = form.dataset.confirmKind;
      let msg = '¿Está seguro de que desea aplicar este cambio?';
      if (window.LAT_I18N) {
        if (kind === 'calidad') {
          msg = window.LAT_I18N.t('confirm.quality_msg', {
            nombre: form.dataset.nombre || '',
            litros: form.dataset.litros || '0',
            estado: form.dataset.nuevoEstado || ''
          });
        } else if (kind === 'finish_batch') {
          msg = window.LAT_I18N.t('confirm.finish_batch_msg', {
            codigo: form.dataset.codigo || ''
          });
        } else if (kind === 'delete') {
          msg = window.LAT_I18N.t('confirm.delete_msg', {
            detalle: form.dataset.detalle || ''
          });
        }
      }

      if (confirmMsgEl) confirmMsgEl.textContent = msg;
      abrirModal('modal-confirmacion-lat');
    });

    if (btnConfirmar) {
      btnConfirmar.addEventListener('click', function () {
        if (!pendingForm) return;
        const target = pendingForm;
        target.dataset.confirmed = 'true';
        cerrarModal('modal-confirmacion-lat');
        target.submit();
      });
    }

    // Sub-tabs del modal Editar Datos
    document.querySelectorAll('.subtab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.subtab-btn').forEach(b => b.classList.toggle('activo', b === btn));
        document.querySelectorAll('.subtab-panel').forEach(p => { p.hidden = p.id !== btn.dataset.subtab; });
      });
    });

    // Matriz de permisos para Usuarios Personalizados
    const plantillasCargo = @json($plantillasCargo);

    function actualizarContadorPermisos(prefix) {
      const checks = Array.from(document.querySelectorAll(`.js-perm-checkbox[data-prefix="${prefix}"]`));
      const marcados = checks.filter(c => c.checked);
      const secciones = new Set(marcados.map(c => c.dataset.sec));
      const counterEl = document.getElementById(prefix + '-perm-counter');
      if (counterEl) {
        counterEl.textContent = `${secciones.size} secciones · ${marcados.length} controles activos`;
      }
    }

    function aplicarPermisosEnMatriz(prefix, listaPermisos) {
      const setPerms = new Set(listaPermisos || []);
      document.querySelectorAll(`.js-perm-checkbox[data-prefix="${prefix}"]`).forEach(cb => {
        if (!cb.disabled) {
          cb.checked = setPerms.has(cb.value);
        }
      });
      actualizarContadorPermisos(prefix);
    }

    document.querySelectorAll('.js-cargo-template').forEach(sel => {
      sel.addEventListener('change', () => {
        const prefix = sel.dataset.prefix;
        const cargo = sel.value;
        if (cargo !== 'personalizado' && plantillasCargo[cargo]) {
          aplicarPermisosEnMatriz(prefix, plantillasCargo[cargo]);
          const rangoEl = document.getElementById(prefix + '-rango');
          if (rangoEl) {
            rangoEl.value = cargo === 'administracion' ? 'administrador' : (cargo === 'encargado_planta' ? 'supervisor' : 'operador');
          }
        } else {
          actualizarContadorPermisos(prefix);
        }
      });
    });

    document.querySelectorAll('.js-perm-checkbox').forEach(cb => {
      cb.addEventListener('change', () => {
        const prefix = cb.dataset.prefix;
        const cargoEl = document.getElementById(prefix + '-cargo');
        if (cargoEl && cargoEl.value !== 'personalizado') {
          cargoEl.value = 'personalizado';
        }
        actualizarContadorPermisos(prefix);
      });
    });

    document.querySelectorAll('.perm-toggle-all').forEach(btn => {
      btn.addEventListener('click', () => {
        const prefix = btn.dataset.prefix;
        const sec = btn.dataset.targetSec;
        const cbs = Array.from(document.querySelectorAll(`.js-perm-checkbox[data-prefix="${prefix}"][data-sec="${sec}"]:not(:disabled)`));
        const todosMarcados = cbs.length > 0 && cbs.every(c => c.checked);
        cbs.forEach(c => { c.checked = !todosMarcados; });
        const cargoEl = document.getElementById(prefix + '-cargo');
        if (cargoEl && cargoEl.value !== 'personalizado') {
          cargoEl.value = 'personalizado';
        }
        actualizarContadorPermisos(prefix);
      });
    });

    const btnNuevoUsuario = document.getElementById('btn-abrir-nuevo-usuario');
    if (btnNuevoUsuario) {
      btnNuevoUsuario.addEventListener('click', () => {
        aplicarPermisosEnMatriz('nu', []);
      });
    }

    // Disparadores para editar registros individuales (solo si tiene el control asignado)
    @if($usuario->tienePermiso('acopio.editar'))
    const baseAcopioUrl = @json(url('/panel/acopio'));
    document.querySelectorAll('.js-edit-acopio').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-acopio').action = baseAcopioUrl + '/' + btn.dataset.id;
        document.getElementById('edit-ac-prov').value = btn.dataset.proveedor;
        document.getElementById('edit-ac-litros').value = btn.dataset.litros;
        document.getElementById('edit-ac-calidad').value = btn.dataset.calidad;
        document.getElementById('edit-ac-metodo').value = btn.dataset.metodo;
        document.getElementById('edit-ac-hora').value = btn.dataset.hora;
        cerrarModal('modal-editar-datos');
        abrirModal('modal-edit-acopio');
      });
    });
    @endif

    @if($usuario->tienePermiso('finanzas.editar'))
    const baseMovUrl = @json(url('/panel/movimiento'));
    document.querySelectorAll('.js-edit-mov').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-mov').action = baseMovUrl + '/' + btn.dataset.id;
        document.getElementById('edit-mov-tipo').value = btn.dataset.tipo;
        document.getElementById('edit-mov-prov').value = btn.dataset.proveedor;
        document.getElementById('edit-mov-concepto').value = btn.dataset.concepto;
        document.getElementById('edit-mov-unidades').value = btn.dataset.unidades;
        document.getElementById('edit-mov-monto').value = btn.dataset.monto;
        cerrarModal('modal-editar-datos');
        abrirModal('modal-edit-mov');
      });
    });
    @endif

    @if($usuario->tienePermiso('tinas.gestionar'))
    const baseTinaUrl = @json(url('/panel/tinas'));
    document.querySelectorAll('.js-edit-tina').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-tina').action = baseTinaUrl + '/' + btn.dataset.id;
        document.getElementById('et-codigo').value = btn.dataset.codigo;
        document.getElementById('et-nombre').value = btn.dataset.nombre;
        document.getElementById('et-cap').value = btn.dataset.capacidad;
        document.getElementById('et-tipo').value = btn.dataset.tipo;
        document.getElementById('et-activa').value = btn.dataset.activa;
        abrirModal('modal-edit-tina');
      });
    });
    @endif

    @if($usuario->tienePermiso('admin.plantas'))
    const basePlantaUrl = @json(url('/panel/admin/plantas'));
    document.querySelectorAll('.js-edit-planta').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-planta').action = basePlantaUrl + '/' + btn.dataset.id;
        document.getElementById('ep-cod').value = btn.dataset.codigo;
        document.getElementById('ep-nom').value = btn.dataset.nombre;
        document.getElementById('ep-ubi').value = btn.dataset.ubicacion;
        document.getElementById('ep-pre').value = btn.dataset.precio;
        document.getElementById('ep-act').value = btn.dataset.activa;
        abrirModal('modal-edit-planta');
      });
    });
    @endif

    @if($usuario->tienePermiso('admin.usuarios'))
    const baseUserUrl = @json(url('/panel/admin/usuarios'));
    document.querySelectorAll('.js-edit-usuario').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-usuario').action = baseUserUrl + '/' + btn.dataset.id;
        document.getElementById('eu-name').value = btn.dataset.name;
        document.getElementById('eu-user').value = btn.dataset.usuario;
        document.getElementById('eu-email').value = btn.dataset.email;
        document.getElementById('eu-cargo').value = btn.dataset.cargo;
        const rangoEl = document.getElementById('eu-rango');
        if (rangoEl) rangoEl.value = btn.dataset.rango || 'operador';
        const plantaEl = document.getElementById('eu-planta');
        if (plantaEl && plantaEl.tagName === 'SELECT') plantaEl.value = btn.dataset.planta;
        document.getElementById('eu-activo').value = btn.dataset.activo;
        document.getElementById('eu-pass').value = '';
        let perms = [];
        try { perms = JSON.parse(btn.dataset.permisos || '[]'); } catch (_) {}
        aplicarPermisosEnMatriz('eu', perms);
        abrirModal('modal-edit-usuario');
      });
    });
    @endif
  })();

  // 4. Botonera táctil de una mano
  document.querySelectorAll('.botonera button').forEach(b => {
    b.addEventListener('click', () => {
      const input = document.getElementById('input-litros');
      if (input) {
        input.value = ((parseFloat(input.value) || 0) + parseFloat(b.dataset.litros)).toFixed(1);
      }
    });
  });

  // 5. Reconocimiento de voz (Web Speech API) con confirmación hablada
  (function () {
    const btnVoz = document.getElementById('btn-voz');
    const txtBtn = document.getElementById('txt-btn-voz');
    const transcripcion = document.getElementById('transcripcion-voz');
    if (!btnVoz) return;

    function reposo() {
      txtBtn.textContent = window.LAT_I18N ? window.LAT_I18N.t('m1.voice_btn') : 'Dictar acopio por voz';
      btnVoz.setAttribute('aria-pressed', 'false');
    }

    btnVoz.addEventListener('click', () => {
      const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SR) {
        transcripcion.textContent = 'Este navegador no admite dictado por voz. Use la botonera táctil o escriba los litros.';
        return;
      }
      const rec = new SR();
      rec.lang = 'es-PE';
      rec.interimResults = false;

      txtBtn.textContent = 'Escuchando… hable ahora';
      btnVoz.setAttribute('aria-pressed', 'true');

      rec.onresult = e => {
        const frase = e.results[0][0].transcript;
        transcripcion.textContent = 'Escuchado: “' + frase + '”';
        document.getElementById('input-frase').value = frase;
        document.getElementById('input-metodo').value = 'voz';
        const m = frase.match(/(\d+([\.,]\d+)?)/);
        if (m) document.getElementById('input-litros').value = parseFloat(m[1].replace(',', '.'));
        if ('speechSynthesis' in window) {
          const u = new SpeechSynthesisUtterance('Acopio capturado correctamente');
          u.lang = 'es-PE';
          window.speechSynthesis.speak(u);
        }
        reposo();
      };
      rec.onerror = () => { transcripcion.textContent = 'No se pudo escuchar. Intente de nuevo o use la botonera táctil.'; reposo(); };
      rec.onend = reposo;
      rec.start();
    });
  })();
</script>
@endpush
