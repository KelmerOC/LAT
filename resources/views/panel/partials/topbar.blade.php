<header class="dash-topbar">
  <div class="dash-topbar-left">
    <button type="button" class="dash-burger" id="btn-sidebar-open" aria-label="Abrir menú lateral" aria-controls="dash-sidebar" aria-expanded="false">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    </button>
    <div class="dash-breadcrumb">
      <span class="dash-breadcrumb-trail">{{ $plantaActiva->nombre }} · {{ $plantaActiva->ubicacion }}</span>
      <h1 class="dash-breadcrumb-title" id="topbar-section-title">Mi Panel Operativo</h1>
    </div>
  </div>

  <div class="dash-topbar-right">
    <div class="dash-topbar-lang" role="group" aria-label="Idioma de la plataforma">
      <button type="button" class="dash-lang-pill btn-lang-choice" data-lang="es" title="Español">ES</button>
      <button type="button" class="dash-lang-pill btn-lang-choice" data-lang="qu" title="Runasimi (Quechua)">QU</button>
      <button type="button" class="dash-lang-pill btn-lang-choice" data-lang="en" title="English">EN</button>
    </div>

    @if($usuario->puedeVerSeccion('dashboard'))
      <nav class="dash-range-pills" id="dash-range-pills" aria-label="Filtro de período del dashboard">
        @foreach(['hoy' => 'Hoy', '7d' => '7 días', 'quincena' => 'Quincena', 'mes' => 'Mes'] as $claveRango => $etqRango)
          <a href="{{ route('panel', ['rango' => $claveRango]) }}" class="dash-range-pill {{ $rango === $claveRango ? 'activo' : '' }}" data-i18n="dash.range_{{ $claveRango }}">
            {{ $etqRango }}
          </a>
        @endforeach
      </nav>
    @endif

    @if($usuario->tienePermiso('admin.plantas') && $plantas->count() > 1)
      <form method="POST" action="{{ route('panel.cambiar_planta') }}" class="plant-switcher-form" style="margin: 0;">
        @csrf
        <label for="select-planta-activa" class="sr-only" data-i18n="panel.switch_plant">Planta activa</label>
        <select name="planta_id" id="select-planta-activa" class="plant-switcher-select" onchange="this.form.submit()">
          @foreach($plantas as $pl)
            <option value="{{ $pl->id }}" @selected($pl->id === $plantaActiva->id)>
              {{ $pl->codigo }} · {{ $pl->nombre }}
            </option>
          @endforeach
        </select>
      </form>
    @endif

    @if($usuario->tieneAlgunPermiso(['acopio.editar', 'finanzas.editar', 'acopio.proveedores']))
      <button type="button" id="btn-abrir-editar-datos" class="btn btn-outline" data-open-modal="modal-editar-datos">
        {!! $icoEditar !!} <span data-i18n="panel.btn_edit_data">Editar datos</span>
      </button>
    @endif

    @if($usuario->tienePermiso('finanzas.exportar'))
      <a href="{{ route('panel.exportar') }}" class="btn btn-ember">
        {!! $icoDescarga !!} <span data-i18n="panel.btn_export_excel">Exportar Excel (.xlsx)</span>
      </a>
    @endif
  </div>
</header>
