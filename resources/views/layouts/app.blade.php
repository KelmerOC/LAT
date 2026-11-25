<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"@if(session()->has('locale')) data-server-locale="{{ session('locale') }}"@endif>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#0a2f4d">
  <title>@yield('titulo', 'LAT · Plataforma Láctea Comunitaria')</title>

  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="preload" href="{{ asset('fonts/inter-var-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="{{ asset('css/lat-design-system.css') }}">
  <script src="{{ asset('js/lat-i18n.js') }}"></script>
  @stack('styles')
</head>
<body class="@yield('body_class')">
  <a href="#contenido-principal" class="skip-link" data-i18n="nav.skip">Saltar al contenido principal</a>

  @unless(View::hasSection('sin_chrome_publico'))
  <div class="header-sitio">
    <header class="nav-principal">
      <div class="contenedor nav-principal-inner">
        <a href="{{ route('inicio') }}" class="logo-lat" aria-label="LAT Lácteos, ir al inicio">
          <img src="{{ asset('brand/isotipo_queso_frasco.svg') }}" alt="" width="40" height="40" onerror="this.src='{{ asset('favicon.svg') }}'">
          <span>
            <strong>LAT</strong>
            <small data-i18n="nav.brand_sub">Gestión Láctea Rural</small>
          </span>
        </a>

        <nav aria-label="Principal" class="menu-links" id="menu-links">
          <div class="menu-links-list">
            <a class="enlace-nav" href="{{ route('inicio') }}" @if(request()->routeIs('inicio')) aria-current="page" @endif data-i18n="nav.home">Inicio</a>
            <a class="enlace-nav" href="{{ route('inicio') }}#soluciones" data-i18n="nav.tools">Herramientas</a>
            <a class="enlace-nav" href="{{ route('inicio') }}#empresas-familiares" data-i18n="nav.families">Empresas Familiares</a>
            <a class="enlace-nav" href="{{ route('inicio') }}#nosotros" data-i18n="nav.about">Nosotros</a>
          </div>

          <!-- Acciones y selector integrados en el menú desplegable móvil -->
          <div class="menu-mobile-session">
            <div class="mobile-lang-section">
              <span class="mobile-lang-title" data-i18n="nav.lang_title">Idioma de la plataforma</span>
              <div class="mobile-lang-grid" role="group" aria-label="Seleccionar idioma">
                <button type="button" class="btn-lang-choice activo" data-lang="es" aria-pressed="true">
                  <strong>ES</strong>
                  <span>Español</span>
                </button>
                <button type="button" class="btn-lang-choice" data-lang="qu" aria-pressed="false">
                  <strong>QU</strong>
                  <span>Runasimi</span>
                </button>
                <button type="button" class="btn-lang-choice" data-lang="en" aria-pressed="false">
                  <strong>EN</strong>
                  <span>English</span>
                </button>
              </div>
            </div>

            @auth
              <div class="mobile-user-card">
                <span class="mobile-user-name">{{ Auth::user()->name }}</span>
                <span class="mobile-user-role">{{ strtoupper(str_replace('_', ' ', Auth::user()->cargo)) }}</span>
              </div>
              <a href="{{ route('panel') }}" class="btn btn-ember btn-bloque" @if(request()->routeIs('panel')) aria-current="page" @endif>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
                <span data-i18n="nav.panel_full">Mi Panel Operativo</span>
              </a>
              <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
                @csrf
                <button type="submit" class="btn btn-fog btn-bloque">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                  <span data-i18n="nav.logout">Cerrar Sesión</span>
                </button>
              </form>
            @else
              <a href="{{ route('login') }}" class="btn btn-ember btn-bloque">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                <span data-i18n="nav.login">Iniciar Sesión</span>
              </a>
              <a href="{{ route('registro') }}" class="btn btn-outline btn-bloque" data-i18n="nav.register">Registrarse</a>
            @endauth
          </div>
        </nav>

        <div class="nav-acciones nav-acciones-desktop">
          <div class="lang-dropdown" id="lang-dropdown">
            <button type="button" class="btn-lang-trigger" id="btn-lang-trigger" aria-haspopup="listbox" aria-expanded="false" aria-label="Cambiar idioma de la plataforma">
              <svg class="lang-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20M2 12h20"/></svg>
              <span id="lang-current-label">ES</span>
              <svg class="chevron-nav" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="lang-dropdown-menu" id="lang-dropdown-menu" role="listbox" aria-label="Idiomas disponibles">
              <button type="button" class="lang-opt activo" role="option" aria-selected="true" data-lang="es">
                <strong>ES</strong> · Español
              </button>
              <button type="button" class="lang-opt" role="option" aria-selected="false" data-lang="qu">
                <strong>QU</strong> · Runasimi
              </button>
              <button type="button" class="lang-opt" role="option" aria-selected="false" data-lang="en">
                <strong>EN</strong> · English
              </button>
            </div>
          </div>

          @auth
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
              @csrf
              <button type="submit" class="enlace-auth-texto" aria-label="Salir de la sesión de {{ Auth::user()->name }}" data-i18n="nav.logout">Cerrar Sesión</button>
            </form>
            <a href="{{ route('panel') }}" class="btn btn-ember btn-cta-nav" @if(request()->routeIs('panel')) aria-current="page" @endif data-i18n="nav.panel">Mi Panel</a>
          @else
            <a href="{{ route('registro') }}" class="enlace-auth-texto" data-i18n="nav.register">Registrarse</a>
            <a href="{{ route('login') }}" class="btn btn-ember btn-cta-nav" data-i18n="nav.login">Iniciar Sesión</a>
          @endauth
        </div>

        <!-- Controles Móvil: Dropdown de Idioma + Menú Hamburguesa (<= 860px) -->
        <div class="nav-controles-mobile">
          <div class="lang-dropdown" id="lang-dropdown-mobile">
            <button type="button" class="btn-lang-trigger btn-lang-compact" id="btn-lang-trigger-mobile" aria-haspopup="listbox" aria-expanded="false" aria-label="Cambiar idioma">
              <svg class="lang-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20M2 12h20"/></svg>
              <span class="lang-mobile-current">ES</span>
              <svg class="chevron-nav" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <div class="lang-dropdown-menu" id="lang-dropdown-menu-mobile" role="listbox" aria-label="Idiomas disponibles">
              <button type="button" class="lang-opt activo" role="option" aria-selected="true" data-lang="es">
                <strong>ES</strong> · Español
              </button>
              <button type="button" class="lang-opt" role="option" aria-selected="false" data-lang="qu">
                <strong>QU</strong> · Runasimi
              </button>
              <button type="button" class="lang-opt" role="option" aria-selected="false" data-lang="en">
                <strong>EN</strong> · English
              </button>
            </div>
          </div>

          <button type="button" class="btn-menu-hamburguesa" id="btn-menu" aria-expanded="false" aria-controls="menu-links" aria-label="Abrir menú de navegación">
            <span class="hamb-box" aria-hidden="true">
              <span class="hamb-line"></span>
              <span class="hamb-line"></span>
              <span class="hamb-line"></span>
            </span>
            <span class="btn-menu-txt" data-i18n="nav.menu">Menú</span>
          </button>
        </div>
      </div>
    </header>
  </div>
  @endunless

  <!-- Notificaciones -->
  @if(!View::hasSection('sin_chrome_publico') && (session('exito') || session('info') || $errors->any()))
    <div class="contenedor" style="margin-top: 1rem;">
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

  <main id="contenido-principal" tabindex="-1">
    @yield('contenido')
  </main>

  @unless(View::hasSection('sin_chrome_publico'))
  <footer class="footer-sitio">
    <div class="aguada" aria-hidden="true"></div>
    <div class="contenedor">
      <div class="footer-grid">
        <div>
          <h2 class="footer-col-tit" style="font-size: 1.25rem;">LAT Lácteos</h2>
          <p data-i18n="footer.desc">Herramienta de registro diario para queserías artesanales y asociaciones ganaderas de Cusco Provincia.</p>
        </div>
        <nav aria-label="Pie de página">
          <h2 class="footer-col-tit" data-i18n="footer.explore">Explorar</h2>
          <ul class="footer-links-list">
            <li><a href="{{ route('inicio') }}" data-i18n="nav.home">Inicio</a></li>
            <li><a href="{{ route('inicio') }}#soluciones" data-i18n="nav.tools">Herramientas</a></li>
            <li><a href="{{ route('inicio') }}#empresas-familiares" data-i18n="nav.families">Empresas Familiares</a></li>
            <li><a href="{{ route('inicio') }}#nosotros" data-i18n="nav.about">Nosotros</a></li>
            <li><a href="{{ route('login') }}" data-i18n="nav.login">Iniciar Sesión</a></li>
          </ul>
        </nav>
        <div>
          <h2 class="footer-col-tit" data-i18n="footer.commitments">Compromisos</h2>
          <ul class="footer-links-list" style="gap: 0;">
            <li style="min-height: 36px; display: flex; align-items: center;" data-i18n="footer.c1">Sin comisión a pequeños productores</li>
            <li style="min-height: 36px; display: flex; align-items: center;" data-i18n="footer.c2">Dictado por voz y modo una mano</li>
            <li style="min-height: 36px; display: flex; align-items: center;" data-i18n="footer.c3">Leche ácida a quesillos de 4 días</li>
            <li style="min-height: 36px; display: flex; align-items: center;" data-i18n="footer.c4">Ley N.° 29733: sin DNI a jornales</li>
          </ul>
        </div>
      </div>
      <p class="footer-bottom" data-i18n="footer.bottom">&copy; {{ date('Y') }} LAT · Gestión Láctea Rural · Cusco</p>
    </div>
    <span class="footer-wordmark" aria-hidden="true">LAT</span>
  </footer>
  @endunless

  <script>
    function cerrarTodosDropdownsIdioma() {
      document.querySelectorAll('.lang-dropdown').forEach(dd => {
        dd.classList.remove('abierto');
        const trigger = dd.querySelector('.btn-lang-trigger');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
      });
    }

    function cambiarIdioma(lang, persistServer = true) {
      if (window.LAT_I18N) {
        window.LAT_I18N.apply(lang);
      }
      cerrarTodosDropdownsIdioma();
      if (persistServer) {
        fetch('{{ url('/idioma') }}/' + encodeURIComponent(lang), {
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin'
        }).catch(() => {});
      }
    }

    // Aplicar idioma guardado al cargar la vista
    document.addEventListener('DOMContentLoaded', () => {
      if (window.LAT_I18N) {
        window.LAT_I18N.apply(window.LAT_I18N.getLang());
      }
    });

    function setupLangDropdown(dropdownId, triggerId, menuId) {
      const dd = document.getElementById(dropdownId);
      const trigger = document.getElementById(triggerId);
      const menu = document.getElementById(menuId);
      if (!dd || !trigger || !menu) return;

      trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const abierto = dd.classList.toggle('abierto');
        trigger.setAttribute('aria-expanded', abierto ? 'true' : 'false');
      });

      menu.querySelectorAll('.lang-opt').forEach(opt => {
        opt.addEventListener('click', (e) => {
          e.stopPropagation();
          cambiarIdioma(opt.dataset.lang);
        });
      });
    }

    setupLangDropdown('lang-dropdown', 'btn-lang-trigger', 'lang-dropdown-menu');
    setupLangDropdown('lang-dropdown-mobile', 'btn-lang-trigger-mobile', 'lang-dropdown-menu-mobile');

    // Botones de selección de idioma en el drawer móvil
    document.querySelectorAll('.btn-lang-choice').forEach(btn => {
      btn.addEventListener('click', () => cambiarIdioma(btn.dataset.lang));
    });

    // Menú móvil desplegable (drawer)
    const btnMenu = document.getElementById('btn-menu');
    const menu = document.getElementById('menu-links');
    if (btnMenu && menu) {
      btnMenu.addEventListener('click', () => {
        const abierto = menu.classList.toggle('abierto');
        btnMenu.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        btnMenu.setAttribute('aria-label', abierto ? 'Cerrar menú de navegación' : 'Abrir menú de navegación');
      });
      // Cerrar al hacer clic en un enlace de navegación
      menu.addEventListener('click', e => {
        if (e.target.closest('a')) {
          menu.classList.remove('abierto');
          btnMenu.setAttribute('aria-expanded', 'false');
          btnMenu.setAttribute('aria-label', 'Abrir menú de navegación');
        }
      });
    }

    // Tecla Escape y Clic Fuera para cerrar dropdowns y drawers
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        cerrarTodosDropdownsIdioma();
        if (menu && menu.classList.contains('abierto')) {
          menu.classList.remove('abierto');
          if (btnMenu) {
            btnMenu.setAttribute('aria-expanded', 'false');
            btnMenu.setAttribute('aria-label', 'Abrir menú de navegación');
            btnMenu.focus();
          }
        }
      }
    });

    document.addEventListener('click', e => {
      if (!e.target.closest('.lang-dropdown')) {
        cerrarTodosDropdownsIdioma();
      }
    });
  </script>
  @stack('scripts')
</body>
</html>