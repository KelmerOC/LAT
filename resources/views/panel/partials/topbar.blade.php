<header class="dash-topbar">
  <div class="dash-topbar-left">
    <button type="button" class="dash-burger" id="btn-sidebar-open" aria-label="Abrir menú lateral" data-i18n-aria="topbar.open_sidebar_aria" aria-controls="dash-sidebar" aria-expanded="false">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    </button>
    <div class="dash-breadcrumb">
      <span class="dash-breadcrumb-trail">{{ $plantaActiva->nombre }} · {{ $plantaActiva->ubicacion }}</span>
      <h1 class="dash-breadcrumb-title" id="topbar-section-title">Mi Panel Operativo</h1>
    </div>
  </div>

  <div class="dash-topbar-right">
    <div class="lat-net-status">
      <span id="lat-net-badge" class="chip chip-verde is-online" role="status" aria-live="polite">
        <span class="lat-net-dot" aria-hidden="true"></span>
        <span id="lat-net-text">En línea</span>
      </span>
      <button type="button" id="btn-sync-offline" class="btn-micro" hidden>Sincronizar ahora</button>
    </div>

    @php
      $notifData = $notificacionesPanel ?? ['items' => [], 'no_leidas' => 0, 'ultimo_id' => 0];
      $notifNoLeidas = (int) ($notifData['no_leidas'] ?? 0);
    @endphp
    <div class="lat-notif-wrap">
      <button type="button" id="btn-notificaciones" class="btn btn-outline lat-notif-btn" aria-expanded="false" aria-controls="tray-notificaciones" aria-label="Bandeja de notificaciones" data-i18n-aria="notif.tray_aria">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span class="lat-notif-label" data-i18n="notif.tray_btn">Bandeja</span>
        <span id="notif-badge-count" class="chip chip-ember lat-notif-count" @if($notifNoLeidas === 0) hidden @endif>{{ $notifNoLeidas }}</span>
      </button>
      <div id="tray-notificaciones" class="lat-notif-tray" hidden
        data-poll-url="{{ route('panel.notificaciones.poll') }}"
        data-read-url="{{ route('panel.notificaciones.leer') }}"
        data-last-id="{{ (int) ($notifData['ultimo_id'] ?? 0) }}">
        <div class="lat-notif-tray-head">
          <strong data-i18n="notif.tray_title">Alertas y bandeja operativa</strong>
          <button type="button" id="btn-notif-marcar-todas" class="btn-micro" data-i18n="notif.mark_all_read">Marcar leídas</button>
        </div>
        <ul id="notif-tray-list" class="lat-notif-list">
          @forelse(($notifData['items'] ?? []) as $nf)
            <li class="lat-notif-item {{ !empty($nf['leida']) ? 'is-read' : 'is-unread' }} is-{{ $nf['tipo'] ?? 'info' }}" data-notif-id="{{ $nf['id'] }}" data-modulo="{{ $nf['modulo_destino'] ?? 'general' }}">
              <div class="lat-notif-item-head">
                <strong>{{ $nf['titulo'] }}</strong>
                <small class="num-tabular">{{ $nf['creada_en'] ?? '' }}</small>
              </div>
              <p>{{ $nf['mensaje'] }}</p>
            </li>
          @empty
            <li class="lat-notif-empty" data-i18n="notif.empty">Sin alertas nuevas en esta planta.</li>
          @endforelse
        </ul>
      </div>
    </div>

    @if($usuario->puedeVerSeccion('dashboard'))
      <nav class="dash-range-pills" id="dash-range-pills" aria-label="Filtro de período del dashboard" data-i18n-aria="topbar.range_aria">
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
<div id="lat-live-alert-stack" class="lat-live-alert-stack" aria-live="polite"></div>
