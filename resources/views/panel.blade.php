@extends('layouts.app')

@section('titulo', 'LAT · Mi Panel Operativo')
@section('title_i18n', 'title.panel')
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
                <strong data-i18n="common.review_errors">Revise los siguientes campos:</strong>
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
          <h2 data-i18n="panel.no_sections_title">Sin secciones asignadas</h2>
          <p style="margin-top: 0.75rem;"><span data-i18n="panel.no_sections_desc">Tu cuenta está activa pero aún no tiene secciones ni controles habilitados. Solicita al administrador de tu planta que asigne tus permisos.</span> (<strong>{{ $usuario->usuario }}</strong>)</p>
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
  // Registro compartido de gráficas Chart.js para redimensionar al cambiar de sección
  window.__latCharts = {
    dashboard: [],
    estadisticas: []
  };

  // 1. Navegación lateral de secciones + Drawer móvil + Persistencia por usuario
  (function () {
    const navBtns = Array.from(document.querySelectorAll('#dash-nav-list .dash-nav-btn'));
    const panels = Array.from(document.querySelectorAll('.tab-panel'));
    const topbarTitle = document.getElementById('topbar-section-title');
    const rangePills = document.getElementById('dash-range-pills');
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

      if (rangePills) {
        rangePills.hidden = sectionId !== 'sec-dashboard';
      }

      navBtns.forEach(btn => {
        const activa = btn.dataset.section === sectionId;
        btn.classList.toggle('activo', activa);
        if (activa) {
          btn.setAttribute('aria-current', 'page');
          if (topbarTitle) {
            const titKey = btn.dataset.tituloKey;
            topbarTitle.textContent = (titKey && window.LAT_I18N) ? window.LAT_I18N.t(titKey) : (btn.dataset.titulo || 'Mi Panel Operativo');
          }
        } else {
          btn.removeAttribute('aria-current');
        }
      });

      toggleSidebar(false);
      try { sessionStorage.setItem(storageKey, sectionId); } catch (_) {}

      if (sectionId === 'sec-dashboard' || sectionId === 'sec-estadisticas') {
        requestAnimationFrame(() => {
          const group = sectionId === 'sec-dashboard' ? window.__latCharts.dashboard : window.__latCharts.estadisticas;
          group.forEach(ch => {
            if (ch && typeof ch.resize === 'function') ch.resize();
          });
          window.dispatchEvent(new Event('resize'));
        });
      }
    }

    if (window.LAT_I18N && window.LAT_I18N.onChange) {
      window.LAT_I18N.onChange(() => {
        const activeBtn = navBtns.find(b => b.classList.contains('activo'));
        if (activeBtn && topbarTitle) {
          const titKey = activeBtn.dataset.tituloKey;
          topbarTitle.textContent = titKey ? window.LAT_I18N.t(titKey) : (activeBtn.dataset.titulo || '');
        }
      });
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

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && sidebar && sidebar.classList.contains('abierto')) {
        toggleSidebar(false);
        if (btnOpen) btnOpen.focus();
      }
    });

    let inicial = defaultSection;
    try {
      const saved = sessionStorage.getItem(storageKey);
      if (saved && document.getElementById(saved)) {
        inicial = saved;
      }
    } catch (_) {}
    activarSeccion(inicial);
  })();

  // 2. Gráficas interactivas Chart.js (Dashboard y Estadísticas) con soporte trilingüe
  (function () {
    if (typeof Chart === 'undefined') return;
    Chart.defaults.font.family = "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
    Chart.defaults.color = '#4b6276';
    const tr = (key, fallback) => (window.LAT_I18N ? window.LAT_I18N.t(key) : fallback);

    let chartAcopio = null;
    let chartVentas = null;
    let chartMoldes = null;
    let chartStatsCalidad = null;

    @if($dashboardData)
      const dashSeries = @json($dashboardData['series']);

      const ctxAcopio = document.getElementById('chart-acopio-diario');
      if (ctxAcopio) {
        chartAcopio = new Chart(ctxAcopio, {
          type: 'bar',
          data: {
            labels: dashSeries.labels,
            datasets: [
              {
                label: tr('dash.c1_ds_good', 'Óptima (L)'),
                data: dashSeries.acopio_buena,
                backgroundColor: '#0284c7',
                borderRadius: 4,
              },
              {
                label: tr('dash.c1_ds_acid', 'Ácida · Quesillo (L)'),
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
        chartVentas = new Chart(ctxVentas, {
          type: 'line',
          data: {
            labels: dashSeries.labels,
            datasets: [
              {
                label: tr('dash.c2_ds_sales', 'Ventas (S/)'),
                data: dashSeries.ventas,
                borderColor: '#15803d',
                backgroundColor: 'rgba(21, 128, 61, 0.12)',
                fill: true,
                tension: 0.32,
                borderWidth: 2.5,
                pointRadius: 3.5,
              },
              {
                label: tr('dash.c2_ds_exp', 'Gastos (S/)'),
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
        chartMoldes = new Chart(ctxMoldes, {
          type: 'line',
          data: {
            labels: dashSeries.labels,
            datasets: [{
              label: tr('dash.c3_ds_molds', 'Moldes producidos'),
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
        chartStatsCalidad = new Chart(ctxStatsCalidad, {
          type: 'doughnut',
          data: {
            labels: [
              tr('dash.c4_l1', 'Óptima (Tina)'),
              tr('dash.c4_l2', 'Ácida (Quesillo)'),
              tr('dash.c4_l3', 'Pendiente')
            ],
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

    window.__latCharts.dashboard = [chartAcopio, chartVentas, chartMoldes].filter(Boolean);
    window.__latCharts.estadisticas = [chartStatsCalidad].filter(Boolean);

    if (window.LAT_I18N && window.LAT_I18N.onChange) {
      window.LAT_I18N.onChange(() => {
        if (chartAcopio) {
          chartAcopio.data.datasets[0].label = tr('dash.c1_ds_good', 'Óptima (L)');
          chartAcopio.data.datasets[1].label = tr('dash.c1_ds_acid', 'Ácida · Quesillo (L)');
          chartAcopio.update();
        }
        if (chartVentas) {
          chartVentas.data.datasets[0].label = tr('dash.c2_ds_sales', 'Ventas (S/)');
          chartVentas.data.datasets[1].label = tr('dash.c2_ds_exp', 'Gastos (S/)');
          chartVentas.update();
        }
        if (chartMoldes) {
          chartMoldes.data.datasets[0].label = tr('dash.c3_ds_molds', 'Moldes producidos');
          chartMoldes.update();
        }
        if (chartStatsCalidad) {
          chartStatsCalidad.data.labels = [
            tr('dash.c4_l1', 'Óptima (Tina)'),
            tr('dash.c4_l2', 'Ácida (Quesillo)'),
            tr('dash.c4_l3', 'Pendiente')
          ];
          chartStatsCalidad.update();
        }
      });
    }
  })();

  // 3. Modales, Confirmación JS y Matriz de Permisos de Usuarios Personalizados
  (function () {
    let pendingForm = null;
    const modalTriggers = new Map();
    const modalConfirm = document.getElementById('modal-confirmacion-lat');
    const confirmMsgEl = document.getElementById('confirm-modal-message');
    const btnConfirmar = document.getElementById('btn-confirmar-accion-js');

    function abrirModal(id, triggerEl) {
      const el = document.getElementById(id);
      if (!el) return;
      modalTriggers.set(id, triggerEl || document.activeElement);
      el.hidden = false;
      const focusable = el.querySelector('input:not([type="hidden"]):not([readonly]), select, button:not(.modal-close)');
      if (focusable) focusable.focus();
    }

    function cerrarModal(id) {
      const el = document.getElementById(id);
      if (el) el.hidden = true;
      if (id === 'modal-confirmacion-lat') pendingForm = null;
      const trigger = modalTriggers.get(id);
      modalTriggers.delete(id);
      if (trigger && typeof trigger.focus === 'function' && trigger.offsetParent !== null) {
        trigger.focus();
      }
    }

    document.querySelectorAll('[data-open-modal]').forEach(btn => {
      btn.addEventListener('click', () => abrirModal(btn.dataset.openModal, btn));
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
      let msg = window.LAT_I18N ? window.LAT_I18N.t('confirm.default_msg') : '¿Está seguro de que desea aplicar este cambio?';
      if (window.LAT_I18N) {
        if (kind === 'calidad') {
          const estadoKey = form.dataset.estadoKey || (e.submitter && e.submitter.dataset.estadoKey) || 'buena';
          const estadoLabel = window.LAT_I18N.t(estadoKey === 'acida' ? 'calidad.state_acida' : 'calidad.state_buena');
          msg = window.LAT_I18N.t('confirm.quality_msg', {
            nombre: form.dataset.nombre || '',
            litros: form.dataset.litros || '0',
            estado: estadoLabel
          });
        } else if (kind === 'finish_batch') {
          msg = window.LAT_I18N.t('confirm.finish_batch_msg', {
            codigo: form.dataset.codigo || ''
          });
        } else if (kind === 'delete') {
          const detalleTipo = form.dataset.detalleTipo || (e.submitter && e.submitter.dataset.detalleTipo) || '';
          const detalleNombre = form.dataset.detalleNombre || (e.submitter && e.submitter.dataset.detalleNombre) || '';
          const detalle = detalleTipo
            ? window.LAT_I18N.t(detalleTipo === 'acopio' ? 'confirm.detail_acopio' : 'confirm.detail_mov', { nombre: detalleNombre })
            : (form.dataset.detalle || detalleNombre);
          msg = window.LAT_I18N.t('confirm.delete_msg', { detalle });
        }
      }

      if (confirmMsgEl) confirmMsgEl.textContent = msg;
      abrirModal('modal-confirmacion-lat', form.querySelector('button[type="submit"]'));
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
        document.querySelectorAll('.subtab-btn').forEach(b => {
          const activa = b === btn;
          b.classList.toggle('activo', activa);
          b.setAttribute('aria-selected', activa ? 'true' : 'false');
        });
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
        counterEl.textContent = window.LAT_I18N
          ? window.LAT_I18N.t('perm.counter', { secs: secciones.size, ctrls: marcados.length })
          : `${secciones.size} secciones · ${marcados.length} controles activos`;
      }
    }

    if (window.LAT_I18N && window.LAT_I18N.onChange) {
      window.LAT_I18N.onChange(() => {
        actualizarContadorPermisos('nu');
        actualizarContadorPermisos('eu');
      });
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
        const formNuevo = document.querySelector('#modal-nuevo-usuario form');
        if (formNuevo) formNuevo.reset();
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
        const fallbackTrigger = btn.closest('#modal-editar-datos') ? document.getElementById('btn-abrir-editar-datos') : btn;
        cerrarModal('modal-editar-datos');
        abrirModal('modal-edit-acopio', fallbackTrigger || btn);
      });
    });
    @endif

    @if($usuario->tienePermiso('acopio.proveedores'))
    const baseProvUrl = @json(url('/panel/proveedor'));
    document.querySelectorAll('.js-edit-proveedor').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-proveedor').action = baseProvUrl + '/' + btn.dataset.id;
        document.getElementById('ep-prv-cod').value = btn.dataset.codigo || '';
        document.getElementById('ep-prv-nom').value = btn.dataset.nombre || '';
        document.getElementById('ep-prv-sec').value = btn.dataset.sector || '';
        cerrarModal('modal-editar-datos');
        abrirModal('modal-edit-proveedor', document.getElementById('btn-abrir-editar-datos') || btn);
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
        const fallbackTrigger = btn.closest('#modal-editar-datos') ? document.getElementById('btn-abrir-editar-datos') : btn;
        cerrarModal('modal-editar-datos');
        abrirModal('modal-edit-mov', fallbackTrigger || btn);
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
        abrirModal('modal-edit-tina', btn);
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
        abrirModal('modal-edit-planta', btn);
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
        abrirModal('modal-edit-usuario', btn);
      });
    });
    @endif
  })();

  // 4. Botonera táctil de una mano e ingreso manual
  (function () {
    const inputLitros = document.getElementById('input-litros');
    const inputMetodo = document.getElementById('input-metodo');
    if (inputLitros && inputMetodo) {
      inputLitros.addEventListener('input', () => {
        inputMetodo.value = 'manual';
      });
    }
    document.querySelectorAll('.botonera button').forEach(b => {
      b.addEventListener('click', () => {
        if (inputLitros) {
          inputLitros.value = ((parseFloat(inputLitros.value) || 0) + parseFloat(b.dataset.litros)).toFixed(1);
        }
        if (inputMetodo) {
          inputMetodo.value = 'manual';
        }
      });
    });
  })();

  // 5. Reconocimiento de voz (Web Speech API) con confirmación hablada y emparejamiento de productor
  (function () {
    const btnVoz = document.getElementById('btn-voz');
    const txtBtn = document.getElementById('txt-btn-voz');
    const transcripcion = document.getElementById('transcripcion-voz');
    const selectProv = document.getElementById('select-proveedor');
    if (!btnVoz) return;

    let escuchando = false;

    function normalizarTexto(txt) {
      return (txt || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/['’ʻʼ`´]/g, '')
        .replace(/[^a-z0-9\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
    }

    function extraerLitrosDeTexto(frase) {
      const m = (frase || '').match(/(\d+(?:[\.,]\d+)?)/);
      if (m) {
        return parseFloat(m[1].replace(',', '.'));
      }

      const tokens = normalizarTexto(frase).split(' ').filter(Boolean);
      if (!tokens.length) return null;

      // Numerales Quechua Cusqueño (1..9, chunka=10, pachak=100, con sufijos -yuq/-niyuq)
      const quUnits = {
        huk: 1, uk: 1, uj: 1,
        iskay: 2,
        kimsa: 3, kinsa: 3,
        tawa: 4,
        pichqa: 5, pisqa: 5, pishqa: 5,
        suqta: 6, soqta: 6,
        qanchis: 7, qanchish: 7,
        pusaq: 8,
        isqun: 9, esqon: 9
      };

      let quTotal = 0;
      let quCurr = 0;
      let quFound = false;
      for (const rawTok of tokens) {
        const tok = rawTok.replace(/(ni)?y[ou]q$/, '');
        if (quUnits[tok] !== undefined) {
          quCurr += quUnits[tok];
          quFound = true;
        } else if (tok === 'chunka') {
          quCurr = (quCurr > 0 ? quCurr : 1) * 10;
          quTotal += quCurr;
          quCurr = 0;
          quFound = true;
        } else if (tok === 'pachak') {
          quCurr = (quCurr > 0 ? quCurr : 1) * 100;
          quTotal += quCurr;
          quCurr = 0;
          quFound = true;
        } else if (quFound) {
          break;
        }
      }
      if (quFound && (quTotal + quCurr) > 0) {
        return quTotal + quCurr;
      }

      // Numerales en español (uno..cien y compuestos)
      const esNums = {
        un: 1, uno: 1, una: 1, dos: 2, tres: 3, cuatro: 4, cinco: 5,
        seis: 6, siete: 7, ocho: 8, nueve: 9, diez: 10,
        once: 11, doce: 12, trece: 13, catorce: 14, quince: 15,
        dieciseis: 16, diecisiete: 17, dieciocho: 18, diecinueve: 19,
        veinte: 20, veintiun: 21, veintiuno: 21, veintiuna: 21,
        veintidos: 22, veintitres: 23, veinticuatro: 24, veinticinco: 25,
        veintiseis: 26, veintisiete: 27, veintiocho: 28, veintinueve: 29,
        treinta: 30, cuarenta: 40, cincuenta: 50, sesenta: 60,
        setenta: 70, ochenta: 80, noventa: 90, cien: 100, ciento: 100
      };

      let esTotal = 0;
      let esFound = false;
      for (const tok of tokens) {
        if (esNums[tok] !== undefined) {
          esTotal += esNums[tok];
          esFound = true;
        } else if (tok === 'y' && esFound) {
          continue;
        } else if (esFound) {
          break;
        }
      }
      if (esFound && esTotal > 0) {
        return esTotal;
      }

      return null;
    }

    function reproducirTonoConfirmacion() {
      try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(660, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12);
        gain.gain.setValueAtTime(0.08, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.18);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.18);
        osc.onended = () => { try { ctx.close(); } catch (_) {} };
      } catch (_) {}
    }

    function hablarConfirmacion(langActual, speechLang, texto) {
      if (langActual === 'qu') {
        const voices = ('speechSynthesis' in window && typeof window.speechSynthesis.getVoices === 'function')
          ? window.speechSynthesis.getVoices()
          : [];
        const vozQuechua = voices.find(v => /^(qu|quz)\b/i.test(v.lang || ''));
        if (vozQuechua && 'speechSynthesis' in window) {
          const u = new SpeechSynthesisUtterance(texto);
          u.voice = vozQuechua;
          u.lang = vozQuechua.lang;
          window.speechSynthesis.speak(u);
          return true;
        }
        reproducirTonoConfirmacion();
        return false;
      }
      if ('speechSynthesis' in window) {
        const u = new SpeechSynthesisUtterance(texto);
        u.lang = speechLang;
        window.speechSynthesis.speak(u);
        return true;
      }
      return false;
    }

    function emparejarProveedor(frase) {
      if (!selectProv) return;
      const normFrase = normalizarTexto(frase);
      if (!normFrase) return;

      let mejorVal = null;
      let mejorPuntaje = 0;

      Array.from(selectProv.options).forEach(opt => {
        if (!opt.value) return;
        const textoOpt = opt.textContent.replace(/^[^-]+-\s*/, '').replace(/\([^)]*\)/g, '');
        const tokens = normalizarTexto(textoOpt).split(' ').filter(t => t.length >= 3 && t !== 'don' && t !== 'sra');
        let hits = 0;
        tokens.forEach(tok => {
          if (normFrase.includes(tok)) hits++;
        });
        if (hits > mejorPuntaje) {
          mejorPuntaje = hits;
          mejorVal = opt.value;
        }
      });

      if (mejorVal && mejorPuntaje > 0) {
        selectProv.value = mejorVal;
      }
    }

    function reposo() {
      escuchando = false;
      txtBtn.textContent = window.LAT_I18N ? window.LAT_I18N.t('m1.voice_btn') : 'Dictar acopio por voz';
      btnVoz.setAttribute('aria-pressed', 'false');
    }

    btnVoz.addEventListener('click', () => {
      if (escuchando) return;
      const tr = (key, fallback, vars) => (window.LAT_I18N ? window.LAT_I18N.t(key, vars) : fallback);
      const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SR) {
        transcripcion.removeAttribute('data-i18n');
        transcripcion.textContent = tr('m1.voice_unsupported', 'Este navegador no admite dictado por voz. Use la botonera táctil o escriba los litros.');
        return;
      }

      const langActual = window.LAT_I18N ? window.LAT_I18N.getLang() : 'es';
      const speechLang = langActual === 'en' ? 'en-US' : 'es-PE';

      const rec = new SR();
      rec.lang = speechLang;
      rec.interimResults = false;

      escuchando = true;
      transcripcion.removeAttribute('data-i18n');
      txtBtn.textContent = tr('m1.voice_listening', 'Escuchando… hable ahora');
      btnVoz.setAttribute('aria-pressed', 'true');

      rec.onresult = e => {
        const frase = e.results[0][0].transcript;
        document.getElementById('input-frase').value = frase;
        document.getElementById('input-metodo').value = 'voz';
        const litros = extraerLitrosDeTexto(frase);
        if (litros !== null && !Number.isNaN(litros)) {
          document.getElementById('input-litros').value = litros;
        }
        emparejarProveedor(frase);
        const textoConfirmacion = tr('m1.voice_spoken_ok', 'Acopio capturado correctamente');
        const hablo = hablarConfirmacion(langActual, speechLang, textoConfirmacion);
        transcripcion.removeAttribute('data-i18n');
        transcripcion.textContent = hablo
          ? tr('m1.voice_heard', 'Escuchado: “{frase}”', { frase })
          : tr('m1.voice_heard', 'Escuchado: “{frase}”', { frase }) + ' · ' + textoConfirmacion;
        reposo();
      };
      rec.onerror = () => {
        transcripcion.removeAttribute('data-i18n');
        transcripcion.textContent = tr('m1.voice_error', 'No se pudo escuchar. Intente de nuevo o use la botonera táctil.');
        reposo();
      };
      rec.onend = reposo;

      try {
        rec.start();
      } catch (_) {
        transcripcion.removeAttribute('data-i18n');
        transcripcion.textContent = tr('m1.voice_error', 'No se pudo iniciar el micrófono. Intente de nuevo o use la botonera táctil.');
        reposo();
      }
    });
  })();
</script>
@endpush
