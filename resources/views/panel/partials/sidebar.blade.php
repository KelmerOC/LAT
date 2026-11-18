<aside class="dash-sidebar" id="dash-sidebar" aria-label="Navegación de módulos">
  <div class="dash-brand">
    <a href="{{ route('inicio') }}" class="dash-brand-link" title="Ir al portal público LAT">
      <img src="{{ asset('brand/isotipo_queso_frasco.svg') }}" alt="" width="36" height="36" onerror="this.src='{{ asset('favicon.svg') }}'">
      <span>
        <strong>LAT Panel</strong>
        <small>{{ $plantaActiva->codigo }} · {{ $plantaActiva->nombre }}</small>
      </span>
    </a>
    <button type="button" class="dash-sidebar-close" id="btn-sidebar-close" aria-label="Cerrar menú lateral">&times;</button>
  </div>

  <nav class="dash-nav" id="dash-nav-list" aria-label="Secciones autorizadas">
    @if($usuario->puedeVerSeccion('dashboard'))
      <div class="dash-nav-group" data-i18n="nav_group.main">Resumen</div>
      <button type="button" class="dash-nav-btn" data-section="sec-dashboard" data-titulo="Dashboard general" data-titulo-key="tab.dashboard">
        <span class="dash-nav-icon">{!! $icoDash !!}</span>
        <span class="dash-nav-label" data-i18n="tab.dashboard">Dashboard</span>
      </button>
    @endif

    @if($usuario->tieneAlgunPermiso(['acopio.ver', 'acopio.crear', 'acopio.editar', 'acopio.proveedores', 'calidad.ver', 'calidad.evaluar', 'tinas.ver', 'tinas.operar', 'tinas.gestionar', 'finanzas.ver', 'finanzas.registrar', 'finanzas.editar', 'finanzas.exportar', 'estadisticas.ver']))
      <div class="dash-nav-group" data-i18n="nav_group.ops">Operación de Planta</div>
    @endif

    @if($usuario->puedeVerSeccion('acopio'))
      <button type="button" class="dash-nav-btn" data-section="sec-acopio" data-titulo="1. Acopio por voz y tacto" data-titulo-key="tab.acopio">
        <span class="dash-nav-icon">{!! $icoMicS !!}</span>
        <span class="dash-nav-label" data-i18n="tab.acopio">1. Acopio</span>
        @if($acopiosHoy->count() > 0)
          <span class="dash-nav-badge" style="background: var(--celeste-fill); color: var(--celeste-text);">{{ $acopiosHoy->count() }}</span>
        @endif
      </button>
    @endif

    @if($usuario->puedeVerSeccion('calidad'))
      <button type="button" class="dash-nav-btn" data-section="sec-calidad" data-titulo="2. Calidad sanitaria (74°)" data-titulo-key="tab.calidad">
        <span class="dash-nav-icon">{!! $icoOk !!}</span>
        <span class="dash-nav-label" data-i18n="tab.calidad">2. Calidad sanitaria</span>
        @if($totalPendientesCalidad > 0)
          <span class="dash-nav-badge">{{ $totalPendientesCalidad }}</span>
        @endif
      </button>
    @endif

    @if($usuario->puedeVerSeccion('tinas'))
      <button type="button" class="dash-nav-btn" data-section="sec-tina" data-titulo="3. Tinas y receta" data-titulo-key="tab.tina">
        <span class="dash-nav-icon">{!! $icoTina !!}</span>
        <span class="dash-nav-label" data-i18n="tab.tina">3. Tinas y receta</span>
      </button>
    @endif

    @if($usuario->puedeVerSeccion('finanzas'))
      <button type="button" class="dash-nav-btn" data-section="sec-quincena" data-titulo="4. Liquidación y ventas" data-titulo-key="tab.quincena">
        <span class="dash-nav-icon">{!! $icoBalance !!}</span>
        <span class="dash-nav-label" data-i18n="tab.quincena">4. Liquidación y ventas</span>
      </button>
    @endif

    @if($usuario->puedeVerSeccion('estadisticas'))
      <button type="button" class="dash-nav-btn" data-section="sec-estadisticas" data-titulo="5. Estadísticas" data-titulo-key="tab.estadisticas">
        <span class="dash-nav-icon">{!! $icoStats !!}</span>
        <span class="dash-nav-label" data-i18n="tab.estadisticas">5. Estadísticas</span>
      </button>
    @endif

    @if($usuario->puedeVerSeccion('admin'))
      <div class="dash-nav-group" data-i18n="nav_group.admin">Administración</div>
      <button type="button" class="dash-nav-btn" data-section="sec-admin" data-titulo="6. Plantas y usuarios" data-titulo-key="tab.admin">
        <span class="dash-nav-icon">{!! $icoAdmin !!}</span>
        <span class="dash-nav-label" data-i18n="tab.admin">6. Plantas y usuarios</span>
      </button>
    @endif
  </nav>

  <div class="dash-sidebar-foot">
    <div class="dash-user-mini">
      <div class="dash-user-avatar" aria-hidden="true">{{ strtoupper(substr($usuario->name, 0, 2)) }}</div>
      <div class="dash-user-meta">
        <strong>{{ $usuario->name }}</strong>
        <span>{{ $usuario->etiquetaCargo() }} · {{ $usuario->etiquetaRango() }}</span>
      </div>
    </div>

    <div class="dash-lang-row" role="group" aria-label="Idioma">
      <button type="button" class="dash-lang-pill btn-lang-choice" data-lang="es">ES</button>
      <button type="button" class="dash-lang-pill btn-lang-choice" data-lang="qu">QU</button>
      <button type="button" class="dash-lang-pill btn-lang-choice" data-lang="en">EN</button>
    </div>

    <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
      @csrf
      <button type="submit" class="btn btn-outline btn-bloque" style="min-height: 40px; font-size: 0.84rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        <span data-i18n="nav.logout">Cerrar Sesión</span>
      </button>
    </form>
  </div>
</aside>
<div class="dash-sidebar-backdrop" id="dash-sidebar-backdrop" aria-hidden="true"></div>
