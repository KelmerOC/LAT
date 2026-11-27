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
    'sueldo_personal'   => 'Sueldo de personal',
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
<script src="{{ asset('js/lat-finanzas-grid.js') }}"></script>
<script src="{{ asset('js/lat-notificaciones.js') }}" defer></script>
<script>
  const recetasBasePlanta = @json($recetasBase ?? []);
  const tiposTinaPreset = new Set(['principal_queso', 'quesillo', 'yogur', 'multiproposito']);

  function traducirTipoTina(tipo) {
    const clave = String(tipo || '');
    if (tiposTinaPreset.has(clave) && window.LAT_I18N) {
      return window.LAT_I18N.t('tina.type_' + clave);
    }
    return clave;
  }

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
    const hashId = (window.location.hash || '').replace(/^#/, '');
    const searchParams = new URLSearchParams(window.location.search);
    const paramKeys = Array.from(searchParams.keys());
    const hasAcopioFilter = paramKeys.some(k => k.startsWith('acopio_'));
    const hasCalidadFilter = paramKeys.some(k => k.startsWith('cal_'));
    const hasFinanzasFilter = paramKeys.some(k => k.startsWith('liq_') || k.startsWith('v_') || k.startsWith('s_'));
    const hasInformesFilter = paramKeys.some(k => k.startsWith('inf_'));

    if (hashId && document.getElementById(hashId)) {
      inicial = hashId;
    } else if (hasAcopioFilter && document.getElementById('sec-acopio')) {
      inicial = 'sec-acopio';
    } else if (hasCalidadFilter && document.getElementById('sec-calidad')) {
      inicial = 'sec-calidad';
    } else if (hasFinanzasFilter && document.getElementById('sec-quincena')) {
      inicial = 'sec-quincena';
    } else if (hasInformesFilter && document.getElementById('sec-estadisticas')) {
      inicial = 'sec-estadisticas';
    } else {
      try {
        const saved = sessionStorage.getItem(storageKey);
        if (saved && document.getElementById(saved)) {
          inicial = saved;
        }
      } catch (_) {}
    }
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
    const confirmBadgeEl = document.getElementById('confirm-modal-badge');
    const confirmMsgEl = document.getElementById('confirm-modal-message');
    const confirmReasonWrapEl = document.getElementById('confirm-modal-reason-wrap');
    const confirmReasonInputEl = document.getElementById('confirm-modal-reason');
    const btnConfirmar = document.getElementById('btn-confirmar-accion-js');

    const tr = (key, fallback, vars) => {
      if (window.LAT_I18N) return window.LAT_I18N.t(key, vars);
      let out = fallback;
      if (vars) {
        Object.keys(vars).forEach(k => {
          out = out.replace(new RegExp('\\{' + k + '\\}', 'g'), vars[k]);
        });
      }
      return out;
    };

    function resetConfirmModalVisuals() {
      if (confirmBadgeEl) {
        confirmBadgeEl.hidden = true;
        confirmBadgeEl.className = 'confirm-status-notice';
        confirmBadgeEl.textContent = '';
      }
      if (confirmReasonWrapEl) {
        confirmReasonWrapEl.hidden = true;
      }
      if (confirmReasonInputEl) {
        confirmReasonInputEl.value = '';
      }
      if (btnConfirmar) {
        btnConfirmar.className = 'btn btn-ember';
      }
    }

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
      if (id === 'modal-confirmacion-lat') {
        if (pendingForm) {
          pendingForm.dataset.confirmed = '';
        }
        pendingForm = null;
        resetConfirmModalVisuals();
      }
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
        if (modalConfirm && !modalConfirm.hidden) {
          cerrarModal('modal-confirmacion-lat');
          return;
        }
        document.querySelectorAll('.modal-overlay:not([hidden])').forEach(ov => cerrarModal(ov.id));
      }
    });

    function setConfirmMsgBold(before, boldText, after) {
      if (!confirmMsgEl) return;
      confirmMsgEl.textContent = '';
      if (before) confirmMsgEl.appendChild(document.createTextNode(before));
      if (boldText) {
        const strongEl = document.createElement('strong');
        strongEl.textContent = boldText;
        confirmMsgEl.appendChild(strongEl);
      }
      if (after) confirmMsgEl.appendChild(document.createTextNode(after));
    }

    // Interceptor JS para Calidad Sanitaria, Cierre de Lote, Finanzas y Eliminaciones
    document.addEventListener('submit', function (e) {
      const form = e.target;
      if (!form.classList || !form.classList.contains('js-confirm-form')) return;
      if (form.dataset.confirmed === 'true') return;

      e.preventDefault();
      resetConfirmModalVisuals();

      const kind = form.dataset.confirmKind;
      let msg = tr('confirm.default_msg', '¿Está seguro de que desea aplicar este cambio?');
      let customMsgRendered = false;
      if (kind === 'toggle_admin_ops') {
        const activar = form.dataset.activar === '1';
        if (confirmBadgeEl) {
          confirmBadgeEl.className = activar ? 'confirm-status-notice is-verde' : 'confirm-status-notice is-amarillo';
          confirmBadgeEl.textContent = activar
            ? tr('confirm.admin_ops_on_badge', 'ACTIVAR MODO OPERATIVO DE PLANTA')
            : tr('confirm.admin_ops_off_badge', 'VOLVER A MODO ADMINISTRADOR');
          confirmBadgeEl.hidden = false;
        }
        if (btnConfirmar) btnConfirmar.className = activar ? 'btn btn-verde' : 'btn btn-ember';
        setConfirmMsgBold(
          tr('confirm.admin_ops_prefix', '¿Confirmas '),
          activar ? tr('confirm.admin_ops_on_bold', 'activar el modo operativo') : tr('confirm.admin_ops_off_bold', 'desactivar el modo operativo'),
          tr('confirm.admin_ops_suffix', ' en esta sesión para registrar y operar módulos de planta?')
        );
        customMsgRendered = true;
      } else if (kind === 'save_liq_grid') {
        if (confirmBadgeEl) {
          confirmBadgeEl.className = 'confirm-status-notice is-amarillo';
          confirmBadgeEl.textContent = tr('confirm.liq_grid_badge', 'ACTUALIZAR LIQUIDACIÓN QUINCENAL');
          confirmBadgeEl.hidden = false;
        }
        if (btnConfirmar) btnConfirmar.className = 'btn btn-ember';
        setConfirmMsgBold(
          tr('confirm.liq_grid_prefix', '¿Guardar los cambios editados en '),
          tr('confirm.liq_grid_bold', 'precios por litro, adelantos y estados de pago'),
          tr('confirm.liq_grid_suffix', ' para los productores filtrados?')
        );
        customMsgRendered = true;
      } else if (kind === 'send_excel') {
        const destEl = form.querySelector('#rep-destinatario');
        const tipoEl = form.querySelector('#rep-tipo');
        const destNombre = destEl?.selectedOptions?.[0]?.textContent?.trim() || 'Contador';
        const tipoNombre = tipoEl?.selectedOptions?.[0]?.textContent?.trim() || 'Excel';
        if (confirmBadgeEl) {
          confirmBadgeEl.className = 'confirm-status-notice is-verde';
          confirmBadgeEl.textContent = tr('confirm.send_excel_badge', 'ENVIAR LIBRO EXCEL (.XLSX) AL CONTADOR');
          confirmBadgeEl.hidden = false;
        }
        if (btnConfirmar) btnConfirmar.className = 'btn btn-verde';
        setConfirmMsgBold(
          tr('confirm.send_excel_prefix', '¿Generar y enviar '),
          `${tipoNombre} → ${destNombre}`,
          tr('confirm.send_excel_suffix', ' con notificación verde inmediata?')
        );
        customMsgRendered = true;
      } else if (kind === 'review_excel') {
        const archivo = form.dataset.archivo || 'reporte.xlsx';
        if (confirmBadgeEl) {
          confirmBadgeEl.className = 'confirm-status-notice is-verde';
          confirmBadgeEl.textContent = tr('confirm.review_excel_badge', 'CONFIRMAR REVISIÓN CONTABLE');
          confirmBadgeEl.hidden = false;
        }
        if (btnConfirmar) btnConfirmar.className = 'btn btn-verde';
        setConfirmMsgBold(
          tr('confirm.review_excel_prefix', '¿Marcar como revisado el archivo '),
          archivo,
          tr('confirm.review_excel_suffix', ' y notificar al remitente?')
        );
        customMsgRendered = true;
      } else if (kind === 'send_accounting_notif') {
        const destEl = form.querySelector('#nc-destinatario');
        const asuntoEl = form.querySelector('#nc-asunto');
        const destNombre = destEl?.selectedOptions?.[0]?.textContent?.trim() || 'Destinatario';
        const asunto = asuntoEl?.value?.trim() || '';
        if (confirmBadgeEl) {
          confirmBadgeEl.className = 'confirm-status-notice is-verde';
          confirmBadgeEl.textContent = tr('confirm.accounting_notif_badge', 'AVISO CONTABLE VERDE EN VIVO');
          confirmBadgeEl.hidden = false;
        }
        if (btnConfirmar) btnConfirmar.className = 'btn btn-verde';
        setConfirmMsgBold(
          tr('confirm.accounting_notif_prefix', '¿Enviar notificación contable a '),
          `${destNombre} (${asunto})`,
          '?'
        );
        customMsgRendered = true;
      } else if (kind === 'send_plant_report') {
        const plantaNombre = form.dataset.planta || '';
        if (confirmBadgeEl) {
          confirmBadgeEl.className = 'confirm-status-notice is-verde';
          confirmBadgeEl.textContent = tr('confirm.plant_report_badge', 'INFORME DE ACTIVIDAD DE PLANTA');
          confirmBadgeEl.hidden = false;
        }
        if (btnConfirmar) btnConfirmar.className = 'btn btn-ember';
        setConfirmMsgBold(
          tr('confirm.plant_report_prefix', '¿Compilar y enviar el informe de las últimas 24h de '),
          plantaNombre || tr('confirm.plant_report_default', 'esta planta'),
          tr('confirm.plant_report_suffix', ' a Administración?')
        );
        customMsgRendered = true;
      } else if (kind === 'trash_plant') {
        const nombrePl = form.dataset.nombre || '';
        const impacto = form.dataset.impacto || '';
        msg = `¿Enviar la planta "${nombrePl}" al tacho? (${impacto}). Podrás restaurarla durante 30 días antes de su eliminación definitiva.`;
      } else if (kind === 'restore_plant') {
        msg = `¿Restaurar la planta "${form.dataset.nombre || ''}" desde el tacho y reactivar todos sus registros?`;
      } else if (kind === 'force_delete_plant') {
        msg = `ATENCIÓN: ¿Eliminar DEFINITIVAMENTE la planta "${form.dataset.nombre || ''}" y todos sus registros históricos? Esta acción no se puede deshacer.`;
      } else if (kind === 'edit_acopio') {
        const provEl = document.getElementById('edit-ac-prov');
        const litrosEl = document.getElementById('edit-ac-litros');
        const calEl = document.getElementById('edit-ac-calidad');
        const nombre = provEl?.selectedOptions?.[0]?.textContent?.trim() || '';
        const litros = litrosEl?.value || '0';
        const calidad = calEl?.selectedOptions?.[0]?.textContent?.trim() || '';
        msg = tr(
          'confirm.edit_acopio_msg',
          '¿Está seguro de que quieres guardar la corrección de {nombre} ({litros} L · {calidad})?',
          { nombre, litros, calidad }
        );
      } else if (kind === 'start_batch') {
        const cbs = Array.from(form.querySelectorAll('.js-it-acopio-cb'));
        const checkedCount = cbs.filter(cb => cb.checked).length;
        const litrosNum = parseFloat(form.querySelector('input[name="litros"]')?.value || '0') || 0;
        const capVal = parseFloat(document.getElementById('it-tina-capacidad')?.value || form.dataset.tinaCapacidad || '0') || 0;
        const resumenEl = document.getElementById('it-litros-resumen');

        if ((form.id === 'form-iniciar-tina' || cbs.length > 0) && (checkedCount < 1 || litrosNum <= 0)) {
          if (resumenEl) {
            resumenEl.classList.add('is-over');
            resumenEl.textContent = tr('modal.milk_need_one', 'Selecciona al menos una entrega de leche para iniciar el lote.');
          }
          return;
        }
        if (capVal > 0 && litrosNum > capVal) {
          if (resumenEl) {
            resumenEl.classList.add('is-over');
            resumenEl.textContent = tr(
              'modal.milk_over_cap',
              'El volumen seleccionado ({litros} L) supera la capacidad de la tina ({cap} L).',
              { litros: litrosNum.toFixed(1), cap: capVal }
            );
          }
          return;
        }

        const litros = form.querySelector('input[name="litros"]')?.value || '0';
        const temp = form.querySelector('input[name="temperatura_actual"]')?.value || '72.0';
        const duracion = form.querySelector('#it-duracion, input[name="duracion_minutos"], input[name="tiempo_minutos"]')?.value || '';
        const codigo = form.dataset.tinaCodigo || '';
        const nombre = form.dataset.tinaNombre || '';
        const tipoKey = form.dataset.tinaTipo || 'principal_queso';
        const uso = traducirTipoTina(tipoKey);
        msg = tr(
          'confirm.start_batch_msg',
          '¿Está seguro de que quieres iniciar un lote en {codigo} ({nombre} · {uso}) con {litros} L a {temp} °C?',
          { codigo, nombre, uso, litros, temp }
        );
        if (checkedCount > 0 && duracion) {
          msg += tr('confirm.start_batch_extra', ' ({entregas} entregas · {duracion} min)', {
            entregas: checkedCount,
            duracion
          });
        }
        const hiddenChecked = cbs.filter(cb => cb.checked && cb.closest('.it-acopio-item')?.hidden);
        if (hiddenChecked.length > 0) {
          const hiddenLiters = hiddenChecked.reduce((acc, cb) => acc + (parseFloat(cb.dataset.litros || cb.closest('.it-acopio-item')?.dataset.litros || '0') || 0), 0);
          msg += ' ' + tr('m3.hidden_selection_notice', 'Incluye {count} entrega(s) ({litros} L) marcadas fuera del filtro actual que siguen sumando al lote.', {
            count: hiddenChecked.length,
            litros: hiddenLiters.toFixed(1)
          });
        }
      } else if (kind === 'calidad') {
        const estadoKey = (e.submitter && e.submitter.dataset.estadoKey) || form.dataset.estadoKey || 'buena';
        const nombre = form.dataset.nombre || '';
        const litros = form.dataset.litros || '0';

        if (estadoKey === 'descartada') {
          if (confirmBadgeEl) {
            confirmBadgeEl.className = 'confirm-status-notice is-rojo';
            confirmBadgeEl.textContent = tr('confirm.quality_warn_descartada', 'ATENCIÓN: DESCARTAR LECHE (NO INGRESA A PRODUCCIÓN)');
            confirmBadgeEl.hidden = false;
          }
          if (confirmReasonWrapEl) {
            confirmReasonWrapEl.hidden = false;
          }
          if (btnConfirmar) btnConfirmar.className = 'btn btn-rojo';
          msg = tr(
            'confirm.quality_discard_msg',
            '¿Está seguro de que quieres descartar la entrega de {nombre} ({litros} L)? Se conservará en el historial como Descartada y no entrará a tina ni a liquidación.',
            { nombre, litros }
          );
        } else {
          const esAcida = estadoKey === 'acida';
          if (confirmBadgeEl) {
            confirmBadgeEl.className = esAcida ? 'confirm-status-notice is-amarillo' : 'confirm-status-notice is-verde';
            confirmBadgeEl.textContent = esAcida
              ? tr('confirm.quality_warn_acida', 'DERIVAR A QUESILLO FRESCO (4 DÍAS · CERO DESPERDICIO)')
              : tr('confirm.quality_warn_buena', 'APROBAR PARA TINA PRINCIPAL (QUESO / YOGUR)');
            confirmBadgeEl.hidden = false;
          }
          if (btnConfirmar) btnConfirmar.className = esAcida ? 'btn btn-ember' : 'btn btn-verde';
          const estadoLabel = tr(
            esAcida ? 'calidad.state_acida' : 'calidad.state_buena',
            esAcida ? 'Ácida (Quesillo 4 días)' : 'Aprobada (Tina principal)'
          );
          msg = tr(
            'confirm.quality_msg',
            '¿Está seguro de que quieres cambiar el estado sanitario de {nombre} ({litros} L) a «{estado}»?',
            { nombre, litros, estado: estadoLabel }
          );
        }
      } else if (kind === 'finish_batch') {
        msg = tr(
          'confirm.finish_batch_msg',
          '¿Está seguro de que quieres finalizar el lote {codigo} y cerrar su registro térmico?',
          { codigo: form.dataset.codigo || '' }
        );
      } else if (kind === 'delete') {
        if (btnConfirmar) btnConfirmar.className = 'btn btn-rojo';
        const detalleTipo = form.dataset.detalleTipo || (e.submitter && e.submitter.dataset.detalleTipo) || '';
        const detalleNombre = form.dataset.detalleNombre || (e.submitter && e.submitter.dataset.detalleNombre) || '';
        const detalleLitros = form.dataset.litros || (e.submitter && e.submitter.dataset.litros) || '';
        let detalle = form.dataset.detalle || detalleNombre;
        if (detalleTipo === 'tina') {
          detalle = tr('confirm.detail_tina', 'tina «{nombre}»', { nombre: detalleNombre });
        } else if (detalleTipo === 'acopio') {
          if (detalleLitros && detalleNombre) {
            detalle = tr(
              'confirm.detail_acopio_full',
              'entrega de leche de {nombre} ({litros} L)',
              { nombre: detalleNombre, litros: detalleLitros }
            );
          } else {
            detalle = tr('confirm.detail_acopio', 'entrega de leche', { nombre: detalleNombre });
          }
        } else if (detalleTipo) {
          detalle = tr('confirm.detail_mov', 'movimiento financiero', { nombre: detalleNombre });
        }
        msg = tr(
          'confirm.delete_msg',
          '¿Está seguro de que quieres eliminar este registro de {detalle}? Esta acción no se puede deshacer.',
          { detalle }
        );
      }

      pendingForm = form;
      if (confirmMsgEl && !customMsgRendered) confirmMsgEl.textContent = msg;
      abrirModal('modal-confirmacion-lat', e.submitter || form.querySelector('button[type="submit"]'));
    });

    if (btnConfirmar) {
      btnConfirmar.addEventListener('click', function () {
        if (!pendingForm) return;
        const target = pendingForm;
        if (confirmReasonWrapEl && !confirmReasonWrapEl.hidden && confirmReasonInputEl) {
          let hiddenReason = target.querySelector('input[name="motivo_descarte"]');
          if (!hiddenReason) {
            hiddenReason = document.createElement('input');
            hiddenReason.type = 'hidden';
            hiddenReason.name = 'motivo_descarte';
            target.appendChild(hiddenReason);
          }
          hiddenReason.value = confirmReasonInputEl.value.trim();
        }
        pendingForm = null;
        cerrarModal('modal-confirmacion-lat');
        target.dataset.confirmed = 'true';
        target.submit();
        target.dataset.confirmed = '';
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

    function vincularPrefijoFijo(sufijoId, hiddenId, prefijo) {
      const sufEl = document.getElementById(sufijoId);
      const hidEl = document.getElementById(hiddenId);
      if (!sufEl || !hidEl) return null;
      const prefBase = prefijo.replace(/-$/, '');
      const rePref = new RegExp('^' + prefBase + '[\\s-]*', 'i');
      const sync = () => {
        const limpio = (sufEl.value || '').trim().replace(rePref, '').toUpperCase();
        if (sufEl.value !== limpio && document.activeElement !== sufEl) {
          sufEl.value = limpio;
        }
        hidEl.value = limpio ? (prefijo + limpio) : '';
      };
      sufEl.addEventListener('input', () => {
        const limpio = (sufEl.value || '').replace(rePref, '').toUpperCase();
        if (sufEl.value !== limpio) sufEl.value = limpio;
        hidEl.value = limpio.trim() ? (prefijo + limpio.trim()) : '';
      });
      const form = sufEl.closest('form');
      if (form) form.addEventListener('submit', sync);
      sync();
      return {
        setFromFullCode(fullCode) {
          const limpio = String(fullCode || '').trim().replace(rePref, '').toUpperCase();
          sufEl.value = limpio;
          hidEl.value = limpio ? (prefijo + limpio) : '';
        },
        sync
      };
    }

    function vincularPrefijoTina(sufijoId, hiddenId, modalId, initialPrefix) {
      const sufEl = document.getElementById(sufijoId);
      const hidEl = document.getElementById(hiddenId);
      if (!sufEl || !hidEl) return null;
      const modalEl = document.getElementById(modalId);
      const addonEl = modalEl ? modalEl.querySelector('.js-tina-prefix-addon, .input-prefix-addon') : null;
      const reTina = /^(?:TINA|VAT)[\s-]*/i;
      let prefActual = initialPrefix === 'VAT-' ? 'VAT-' : 'TINA-';

      const sync = () => {
        const limpio = (sufEl.value || '').trim().replace(reTina, '').toUpperCase();
        if (sufEl.value !== limpio && document.activeElement !== sufEl) {
          sufEl.value = limpio;
        }
        hidEl.value = limpio ? (prefActual + limpio) : '';
      };
      const setPrefix = (nuevo) => {
        prefActual = nuevo === 'VAT-' ? 'VAT-' : 'TINA-';
        if (addonEl) addonEl.textContent = prefActual;
        sync();
      };
      sufEl.addEventListener('input', () => {
        const limpio = (sufEl.value || '').replace(reTina, '').toUpperCase();
        if (sufEl.value !== limpio) sufEl.value = limpio;
        hidEl.value = limpio.trim() ? (prefActual + limpio.trim()) : '';
      });
      const form = sufEl.closest('form');
      if (form) form.addEventListener('submit', sync);
      setPrefix(prefActual);
      return {
        setPrefix,
        setFromFullCode(fullCode) {
          const raw = String(fullCode || '').trim();
          const activePrefix = (window.LAT_I18N && window.LAT_I18N.getLang() === 'en') ? 'VAT-' : 'TINA-';
          if (/^VAT[\s-]*/i.test(raw)) {
            prefActual = 'VAT-';
          } else if (/^TINA[\s-]*/i.test(raw)) {
            prefActual = 'TINA-';
          } else {
            prefActual = activePrefix;
          }
          if (addonEl) addonEl.textContent = prefActual;
          const limpio = raw.replace(reTina, '').toUpperCase();
          sufEl.value = limpio;
          hidEl.value = limpio ? (prefActual + limpio) : '';
        },
        sync
      };
    }

    const nuevaTinaCodigo = vincularPrefijoTina('nt-codigo-sufijo', 'nt-codigo', 'modal-nueva-tina', 'TINA-');
    const editTinaCodigo = vincularPrefijoTina('et-codigo-sufijo', 'et-codigo', 'modal-edit-tina', 'TINA-');
    if (nuevaTinaCodigo) {
      const actualizarPrefijoNuevaTina = (lang) => {
        nuevaTinaCodigo.setPrefix(lang === 'en' ? 'VAT-' : 'TINA-');
      };
      actualizarPrefijoNuevaTina(window.LAT_I18N ? window.LAT_I18N.getLang() : 'es');
      if (window.LAT_I18N && window.LAT_I18N.onChange) {
        window.LAT_I18N.onChange(actualizarPrefijoNuevaTina);
      }
    }
    vincularPrefijoFijo('np-cod-sufijo', 'np-cod', 'PLT-');
    const editPlantaCodigo = vincularPrefijoFijo('ep-cod-sufijo', 'ep-cod', 'PLT-');

    // Disparadores para editar registros individuales (solo si tiene el control asignado)
    @if($usuario->tienePermiso('acopio.editar'))
    const baseAcopioUrl = @json(url('/panel/acopio'));
    document.querySelectorAll('.js-edit-acopio').forEach(btn => {
      btn.addEventListener('click', () => {
        const formEditAcopio = document.getElementById('form-edit-acopio');
        if (formEditAcopio) {
          formEditAcopio.action = baseAcopioUrl + '/' + btn.dataset.id;
          formEditAcopio.dataset.confirmed = '';
        }
        document.getElementById('edit-ac-prov').value = btn.dataset.proveedor;
        document.getElementById('edit-ac-litros').value = btn.dataset.litros;
        document.getElementById('edit-ac-calidad').value = btn.dataset.calidad;
        const calHiddenEl = document.getElementById('edit-ac-calidad-hidden');
        if (calHiddenEl) calHiddenEl.value = btn.dataset.calidad || '';
        document.getElementById('edit-ac-metodo').value = btn.dataset.metodo;
        const fechaEl = document.getElementById('edit-ac-fecha');
        if (fechaEl) fechaEl.value = btn.dataset.fecha || '';
        document.getElementById('edit-ac-hora').value = btn.dataset.hora;
        const fraseEl = document.getElementById('edit-ac-frase');
        if (fraseEl) fraseEl.value = btn.dataset.frase || '';
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
        document.getElementById('edit-mov-tipo').value = btn.dataset.tipo || 'venta_producto';
        const fechaMovEl = document.getElementById('edit-mov-fecha');
        if (fechaMovEl) fechaMovEl.value = btn.dataset.fecha || '';
        const prodMovEl = document.getElementById('edit-mov-producto');
        if (prodMovEl) prodMovEl.value = btn.dataset.producto || '';
        const canalMovEl = document.getElementById('edit-mov-canal');
        if (canalMovEl) canalMovEl.value = btn.dataset.canal || '';
        document.getElementById('edit-mov-prov').value = btn.dataset.proveedor || '';
        document.getElementById('edit-mov-concepto').value = btn.dataset.concepto || '';
        document.getElementById('edit-mov-unidades').value = btn.dataset.unidades || '0';
        const punitMovEl = document.getElementById('edit-mov-punit');
        if (punitMovEl) punitMovEl.value = btn.dataset.precioUnitario || '';
        document.getElementById('edit-mov-monto').value = btn.dataset.monto || '';
        const fallbackTrigger = btn.closest('#modal-editar-datos') ? document.getElementById('btn-abrir-editar-datos') : btn;
        cerrarModal('modal-editar-datos');
        abrirModal('modal-edit-mov', fallbackTrigger || btn);
      });
    });
    @endif

    @if($usuario->esAdmin())
    document.querySelectorAll('.js-ver-informe-planta').forEach(btn => {
      btn.addEventListener('click', () => {
        const titleEl = document.getElementById('informe-modal-title');
        const metaEl = document.getElementById('informe-modal-meta');
        const preEl = document.getElementById('informe-modal-pre');
        const dlEl = document.getElementById('informe-modal-download');
        if (titleEl) titleEl.textContent = btn.dataset.titulo || tr('m5.report_modal_title', 'Informe de actividad de planta');
        if (metaEl) metaEl.textContent = btn.dataset.meta || '';
        if (preEl) preEl.textContent = btn.dataset.contenido || '';
        if (dlEl) dlEl.href = btn.dataset.downloadUrl || '#';
        abrirModal('modal-ver-informe-planta', btn);
      });
    });
    @endif

    @if($usuario->tienePermiso('tinas.gestionar'))
    const baseTinaUrl = @json(url('/panel/tinas'));
    document.querySelectorAll('.js-edit-tina').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-tina').action = baseTinaUrl + '/' + btn.dataset.id;
        if (editTinaCodigo) {
          editTinaCodigo.setFromFullCode(btn.dataset.codigo);
        } else {
          document.getElementById('et-codigo').value = btn.dataset.codigo;
        }
        document.getElementById('et-nombre').value = btn.dataset.nombre;
        document.getElementById('et-cap').value = btn.dataset.capacidad;
        const etTipoEl = document.getElementById('et-tipo');
        if (etTipoEl) {
          const rawTipo = btn.dataset.tipo || '';
          etTipoEl.value = etTipoEl.tagName === 'SELECT' ? rawTipo : traducirTipoTina(rawTipo);
        }
        document.getElementById('et-activa').value = btn.dataset.activa;
        abrirModal('modal-edit-tina', btn);
      });
    });
    @endif

    // Controlador interactivo de #modal-iniciar-tina (filtros de acopio, dosificación y receta)
    (function () {
      const formIniciar = document.getElementById('form-iniciar-tina');
      if (!formIniciar) return;

      const capInput = document.getElementById('it-tina-capacidad');
      const tinaResumenEl = document.getElementById('it-tina-resumen');
      const presetSelect = document.getElementById('it-receta-preset');
      const recetaNombreInput = document.getElementById('it-receta-nombre');
      const tempInput = document.getElementById('it-temp') || formIniciar.querySelector('input[name="temperatura_actual"]');
      const duracionInput = document.getElementById('it-duracion') || formIniciar.querySelector('input[name="duracion_minutos"], input[name="tiempo_minutos"]');
      const cuajoInput = document.getElementById('it-cuajo') || formIniciar.querySelector('input[name="cuajo_ml"]');
      const salInput = document.getElementById('it-sal') || formIniciar.querySelector('input[name="sal_kg"]');
      const cultivoInput = document.getElementById('it-cultivo') || formIniciar.querySelector('input[name="cultivo_g"]');
      const litrosTotalInput = document.getElementById('it-litros-total') || formIniciar.querySelector('input[name="litros"]');
      const litrosResumenEl = document.getElementById('it-litros-resumen');
      const filtroCalidad = document.getElementById('it-filtro-calidad');
      const filtroDia = document.getElementById('it-filtro-dia');
      const filtroFecha = document.getElementById('it-filtro-fecha');
      const filtroOrden = document.getElementById('it-filtro-orden') || document.getElementById('it-orden');
      const emptyEl = document.getElementById('it-acopios-empty');
      const hiddenNoticeEl = document.getElementById('it-hidden-selection-notice');
      const hiddenNoticeTextEl = document.getElementById('it-hidden-selection-text');
      const btnDesmarcarOcultas = document.getElementById('it-btn-desmarcar-ocultas');
      const btnSelVisibles = document.getElementById('it-btn-sel-visibles');
      const btnLimpiar = document.getElementById('it-btn-limpiar');
      const btnRecalcular = document.getElementById('it-btn-recalcular');

      let cuajoManual = false;
      let salManual = false;
      let cultivoManual = false;
      let duracionManual = false;

      function obtenerRecetaPreset(key) {
        if (recetasBasePlanta && !Array.isArray(recetasBasePlanta) && recetasBasePlanta[key]) {
          return recetasBasePlanta[key];
        }
        if (Array.isArray(recetasBasePlanta)) {
          const found = recetasBasePlanta.find(r => r && (r.clave === key || r.key === key || r.tipo === key));
          if (found) return found;
        }
        return {};
      }

      function extraerTasasReceta(r, presetKey) {
        const pickNum = (keys, fallback) => {
          for (const k of keys) {
            if (r && r[k] !== undefined && r[k] !== null && r[k] !== '') {
              const num = Number(r[k]);
              if (!Number.isNaN(num)) return num;
            }
          }
          return fallback;
        };

        let cuajoPerL = pickNum(['cuajo_ml_litro', 'cuajo_ml_x_litro', 'cuajo_por_litro', 'cuajo_ml_por_litro', 'cuajo_litro'], null);
        if (cuajoPerL === null) {
          const c100 = pickNum(['cuajo_x_100l', 'cuajo_ml_100l', 'cuajo_100l'], null);
          if (c100 !== null) cuajoPerL = c100 / 100;
          else {
            const cRaw = pickNum(['cuajo_ml'], null);
            cuajoPerL = cRaw !== null ? (cRaw >= 0.5 ? cRaw / 100 : cRaw) : 0.025;
          }
        }

        let salPerL = pickNum(['sal_kg_litro', 'sal_kg_x_litro', 'sal_por_litro', 'sal_kg_por_litro', 'sal_litro'], null);
        if (salPerL === null) {
          const s100 = pickNum(['sal_x_100l', 'sal_kg_100l', 'sal_100l'], null);
          if (s100 !== null) salPerL = s100 / 100;
          else {
            const sRaw = pickNum(['sal_kg'], null);
            salPerL = sRaw !== null ? (sRaw >= 0.2 ? sRaw / 100 : sRaw) : 0.018;
          }
        }

        let cultivoPerL = pickNum(['cultivo_g_litro', 'cultivo_g_x_litro', 'cultivo_por_litro', 'cultivo_g_por_litro', 'cultivo_litro'], null);
        if (cultivoPerL === null) {
          const u100 = pickNum(['cultivo_x_100l', 'cultivo_g_100l', 'cultivo_100l'], null);
          if (u100 !== null) cultivoPerL = u100 / 100;
          else {
            const uRaw = pickNum(['cultivo_g'], null);
            cultivoPerL = uRaw !== null ? (uRaw >= 0.2 ? uRaw / 100 : uRaw) : 0.010;
          }
        }

        const temp = pickNum(['temperatura_inicial', 'temperatura', 'temp_inicial', 'temp', 'temperatura_objetivo'], 72.0);
        const duracion = pickNum(['duracion_minutos', 'tiempo_minutos', 'duracion', 'minutos'], 45);
        const esPersonalizada = presetKey === 'personalizada' || presetKey === 'personalizado' || presetKey === 'custom';
        const nombre = (r && (r.nombre || r.titulo || r.label || r.nombre_receta))
          || (esPersonalizada
            ? tr('modal.recipe_custom', 'Receta personalizada')
            : traducirTipoTina(presetKey));

        return { cuajoPerL, salPerL, cultivoPerL, temp, duracion, nombre };
      }

      function aplicarFiltrosAcopios() {
        const items = Array.from(formIniciar.querySelectorAll('.it-acopio-item'));
        const calVal = filtroCalidad ? filtroCalidad.value : 'todas';
        const diaVal = filtroDia ? filtroDia.value : 'todos';
        const fechaVal = filtroFecha ? filtroFecha.value : '';
        const ordenVal = filtroOrden ? filtroOrden.value : 'reciente';

        if (filtroFecha) {
          const fechaWrap = document.getElementById('it-filtro-fecha-wrap') || filtroFecha.closest('.js-it-fecha-wrap');
          if (fechaWrap) {
            fechaWrap.hidden = diaVal !== 'fecha';
          } else {
            filtroFecha.disabled = diaVal !== 'fecha';
          }
        }

        const now = new Date();
        const hoyStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

        const listContainer = items[0]?.parentElement || document.getElementById('it-lista-acopios') || document.getElementById('it-acopios-list');
        if (listContainer && items.length > 1) {
          items.sort((a, b) => {
            if (ordenVal === 'antiguo') {
              return (Number(a.dataset.evalTs || 0) - Number(b.dataset.evalTs || 0));
            }
            if (ordenVal === 'litros') {
              return (Number(b.dataset.litros || 0) - Number(a.dataset.litros || 0));
            }
            return (Number(b.dataset.evalTs || 0) - Number(a.dataset.evalTs || 0));
          });
          items.forEach(item => listContainer.appendChild(item));
          if (emptyEl && emptyEl.parentElement === listContainer) {
            listContainer.appendChild(emptyEl);
          }
        }

        let visibles = 0;
        items.forEach(item => {
          const itemCal = item.dataset.calidad || item.querySelector('.js-it-acopio-cb')?.dataset.calidad || '';
          const itemFecha = item.dataset.fecha || '';
          const esHoy = item.dataset.esHoy === '1' || itemFecha === hoyStr;

          const okCal = calVal === 'todas' || itemCal === calVal;
          let okDia = true;
          if (diaVal === 'hoy') {
            okDia = esHoy;
          } else if (diaVal === 'fecha' && fechaVal) {
            okDia = itemFecha === fechaVal;
          }

          const mostrar = okCal && okDia;
          item.hidden = !mostrar;
          if (mostrar) visibles++;
        });

        if (emptyEl) {
          emptyEl.hidden = visibles > 0;
        }
        recalcularLoteDesdeSeleccion(false);
      }

      function recalcularLoteDesdeSeleccion(forzar) {
        const cbs = Array.from(formIniciar.querySelectorAll('.js-it-acopio-cb'));
        let totalLitros = 0;
        let count = 0;
        let ocultasCount = 0;
        let ocultasLitros = 0;

        if (cbs.length > 0) {
          cbs.forEach(cb => {
            if (cb.checked) {
              const row = cb.closest('.it-acopio-item');
              const l = parseFloat(cb.dataset.litros || row?.dataset.litros || '0') || 0;
              totalLitros += l;
              count++;
              if (row && row.hidden) {
                ocultasCount++;
                ocultasLitros += l;
              }
            }
          });
          if (litrosTotalInput) {
            litrosTotalInput.value = totalLitros > 0 ? totalLitros.toFixed(1) : '0';
          }
        } else if (litrosTotalInput) {
          totalLitros = parseFloat(litrosTotalInput.value || '0') || 0;
        }

        if (hiddenNoticeEl && hiddenNoticeTextEl) {
          if (ocultasCount > 0) {
            hiddenNoticeTextEl.textContent = tr(
              'm3.hidden_selection_notice',
              'Incluye {count} entrega(s) ({litros} L) marcadas fuera del filtro actual que siguen sumando al lote.',
              { count: ocultasCount, litros: ocultasLitros.toFixed(1) }
            );
            hiddenNoticeEl.hidden = false;
          } else {
            hiddenNoticeEl.hidden = true;
            hiddenNoticeTextEl.textContent = '';
          }
        }

        const capacidad = parseFloat(capInput?.value || formIniciar.dataset.tinaCapacidad || '0') || 0;
        if (litrosResumenEl) {
          const over = capacidad > 0 && totalLitros > capacidad;
          litrosResumenEl.classList.toggle('is-over', over);
          if (over) {
            litrosResumenEl.textContent = tr(
              'modal.milk_over_cap',
              'El volumen seleccionado ({litros} L) supera la capacidad de la tina ({cap} L).',
              { litros: totalLitros.toFixed(1), cap: capacidad }
            );
          } else {
            const unitLbl = tr('m3.deliveries_count', 'entregas');
            litrosResumenEl.textContent = `${totalLitros.toFixed(1)} / ${capacidad} L (${count} ${unitLbl})`;
          }
        }

        const presetKey = presetSelect?.value || 'principal_queso';
        const rates = extraerTasasReceta(obtenerRecetaPreset(presetKey), presetKey);

        if (cuajoInput && (forzar || !cuajoManual)) {
          cuajoInput.value = (totalLitros * rates.cuajoPerL).toFixed(2);
        }
        if (salInput && (forzar || !salManual)) {
          salInput.value = (totalLitros * rates.salPerL).toFixed(2);
        }
        if (cultivoInput && (forzar || !cultivoManual)) {
          cultivoInput.value = (totalLitros * rates.cultivoPerL).toFixed(2);
        }
        if (duracionInput && (forzar || !duracionManual)) {
          duracionInput.value = String(rates.duracion);
        }
      }

      function aplicarPresetEnModal(presetKey, forzarCampos) {
        const rates = extraerTasasReceta(obtenerRecetaPreset(presetKey), presetKey);
        if (recetaNombreInput) {
          recetaNombreInput.value = rates.nombre || '';
        }
        if (tempInput) {
          tempInput.value = Number(rates.temp).toFixed(1);
        }
        if (duracionInput && (forzarCampos || !duracionManual)) {
          duracionInput.value = String(rates.duracion);
        }
        recalcularLoteDesdeSeleccion(Boolean(forzarCampos));
      }

      document.querySelectorAll('.js-open-iniciar-tina').forEach(btn => {
        btn.addEventListener('click', () => {
          const tinaCodigo = btn.dataset.tinaCodigo || '';
          const tinaNombre = btn.dataset.tinaNombre || '';
          const tinaTipo = btn.dataset.tinaTipo || 'principal_queso';
          const tinaCap = btn.dataset.tinaCapacidad || '0';

          formIniciar.action = btn.dataset.actionUrl || '';
          formIniciar.dataset.tinaCodigo = tinaCodigo;
          formIniciar.dataset.tinaNombre = tinaNombre;
          formIniciar.dataset.tinaTipo = tinaTipo;
          formIniciar.dataset.tinaCapacidad = tinaCap;
          formIniciar.dataset.confirmed = '';

          if (capInput) capInput.value = tinaCap;
          if (tinaResumenEl) {
            tinaResumenEl.textContent = `${tinaCodigo} · ${tinaNombre} (${traducirTipoTina(tinaTipo)}) · ${tinaCap} L`;
          }

          formIniciar.querySelectorAll('.js-it-acopio-cb').forEach(cb => { cb.checked = false; });
          cuajoManual = false;
          salManual = false;
          cultivoManual = false;
          duracionManual = false;

          const hasPreset = Boolean(
            recetasBasePlanta && !Array.isArray(recetasBasePlanta)
              ? recetasBasePlanta[tinaTipo]
              : (Array.isArray(recetasBasePlanta) && recetasBasePlanta.some(r => r && (r.clave === tinaTipo || r.key === tinaTipo || r.tipo === tinaTipo)))
          );
          const initialPreset = hasPreset ? tinaTipo : 'principal_queso';
          if (presetSelect) presetSelect.value = initialPreset;

          if (filtroCalidad) {
            filtroCalidad.value = String(tinaTipo).toLowerCase().includes('quesillo') ? 'acida' : 'todas';
          }
          if (filtroDia) filtroDia.value = 'todos';

          aplicarFiltrosAcopios();
          aplicarPresetEnModal(initialPreset, true);
          abrirModal('modal-iniciar-tina', btn);
        });
      });

      [filtroCalidad, filtroDia, filtroFecha, filtroOrden].forEach(el => {
        if (el) el.addEventListener('change', aplicarFiltrosAcopios);
      });

      if (btnSelVisibles) {
        btnSelVisibles.addEventListener('click', () => {
          formIniciar.querySelectorAll('.it-acopio-item:not([hidden]) .js-it-acopio-cb').forEach(cb => {
            cb.checked = true;
          });
          recalcularLoteDesdeSeleccion(false);
        });
      }

      if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
          formIniciar.querySelectorAll('.js-it-acopio-cb').forEach(cb => { cb.checked = false; });
          recalcularLoteDesdeSeleccion(false);
        });
      }

      if (btnDesmarcarOcultas) {
        btnDesmarcarOcultas.addEventListener('click', () => {
          formIniciar.querySelectorAll('.it-acopio-item[hidden] .js-it-acopio-cb').forEach(cb => {
            cb.checked = false;
          });
          recalcularLoteDesdeSeleccion(false);
        });
      }

      formIniciar.addEventListener('change', (e) => {
        if (e.target && e.target.classList && e.target.classList.contains('js-it-acopio-cb')) {
          recalcularLoteDesdeSeleccion(false);
        }
      });

      if (presetSelect) {
        presetSelect.addEventListener('change', () => aplicarPresetEnModal(presetSelect.value, false));
      }

      if (cuajoInput) cuajoInput.addEventListener('input', () => { cuajoManual = true; });
      if (salInput) salInput.addEventListener('input', () => { salManual = true; });
      if (cultivoInput) cultivoInput.addEventListener('input', () => { cultivoManual = true; });
      if (duracionInput) duracionInput.addEventListener('input', () => { duracionManual = true; });

      if (btnRecalcular) {
        btnRecalcular.addEventListener('click', () => {
          cuajoManual = false;
          salManual = false;
          cultivoManual = false;
          duracionManual = false;
          recalcularLoteDesdeSeleccion(true);
        });
      }
    })();

    @if($usuario->tienePermiso('admin.plantas'))
    const basePlantaUrl = @json(url('/panel/admin/plantas'));
    document.querySelectorAll('.js-edit-planta').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('form-edit-planta').action = basePlantaUrl + '/' + btn.dataset.id;
        if (editPlantaCodigo) {
          editPlantaCodigo.setFromFullCode(btn.dataset.codigo);
        } else {
          document.getElementById('ep-cod').value = btn.dataset.codigo;
        }
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
    const formAcopio = document.getElementById('form-acopio');
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
      if (!selectProv) return false;
      const normFrase = normalizarTexto(frase);
      if (!normFrase) return false;

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
        return true;
      }
      return false;
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
        const inputFrase = document.getElementById('input-frase');
        const inputMetodo = document.getElementById('input-metodo');
        const inputLitros = document.getElementById('input-litros');
        if (inputFrase) inputFrase.value = frase;
        if (inputMetodo) inputMetodo.value = 'voz';
        const litros = extraerLitrosDeTexto(frase);
        if (litros !== null && !Number.isNaN(litros) && inputLitros) {
          inputLitros.value = Math.max(1, litros);
        }
        emparejarProveedor(frase);

        const completo = Boolean(
          selectProv && selectProv.value !== ''
          && litros !== null && !Number.isNaN(litros) && litros >= 1 && litros <= 5000
        );
        const textoConfirmacion = tr('m1.voice_spoken_ok', 'Acopio capturado correctamente');
        hablarConfirmacion(langActual, speechLang, textoConfirmacion);
        transcripcion.removeAttribute('data-i18n');

        if (completo && formAcopio) {
          transcripcion.textContent = tr('m1.voice_auto_saving', 'Escuchado: “{frase}” · Registrando acopio automáticamente…', { frase });
          reposo();
          setTimeout(() => {
            if (formAcopio.requestSubmit) {
              formAcopio.requestSubmit();
            } else {
              formAcopio.submit();
            }
          }, 300);
        } else {
          transcripcion.textContent = tr('m1.voice_heard', 'Escuchado: “{frase}”', { frase })
            + ' · '
            + tr('m1.voice_missing_fields', 'Complete el productor o litros y guarde manualmente.');
          reposo();
        }
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
