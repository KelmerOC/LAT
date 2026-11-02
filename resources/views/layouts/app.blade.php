<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('titulo', 'LAT · Plataforma Láctea Comunitaria')</title>

  <!-- Favicon Vectorial LAT (Frasco de Leche + Cuña de Queso) -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

  <!-- Sistema de Diseño Visual LAT: Flat Fill Contrast (Cero Sombras, Cero Bordes) -->
  <link rel="stylesheet" href="{{ asset('css/lat-design-system.css') }}">
  <style>
    /* Reglas maestras Flat Fill Contrast y Cifras Delgadas */
    .cifra-delgada {
      font-weight: 300 !important;
      font-variant-numeric: tabular-nums;
    }
    .sin-sombras {
      box-shadow: none !important;
    }
    .sin-bordes {
      border: none !important;
    }
    .tabla-plana {
      width: 100%;
      border-collapse: collapse;
      border-radius: 0 !important;
    }
    .tabla-plana th, .tabla-plana td {
      border-radius: 0 !important;
      padding: 0.75rem 1rem;
      border-bottom: 1px solid #e1f0fa;
    }
    .tabla-plana th {
      background-color: #e6f2fb;
      color: #0a2f4d;
      font-weight: 500;
      text-align: left;
    }
    .tabla-plana tr:nth-child(even) {
      background-color: #f7fbfe;
    }
    .tabla-plana tr:hover {
      background-color: #edf6fd;
    }
    .nav-tab-activa {
      background-color: var(--color-ember) !important;
      color: #ffffff !important;
    }
  </style>
  @stack('styles')
</head>
<body>

  <!-- Barra Superior Trilingüe y Lema Comunitario -->
  <div style="background-color: var(--color-slate-deep); color: #e0f2fe; padding: 0.5rem 1.5rem; font-size: 0.82rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #22c55e;"></span>
      <strong id="top-lema">Tecnología Comunitaria sin Fines de Lucro · Comisión S/ 0.00</strong>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
      <span style="opacity: 0.7;">Idioma:</span>
      <button type="button" onclick="cambiarIdioma('es')" id="btn-lang-es" style="background: rgba(255,255,255,0.2); color: #fff; border: none; padding: 2px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem; font-weight: bold;">ES</button>
      <button type="button" onclick="cambiarIdioma('qu')" id="btn-lang-qu" style="background: transparent; color: #bae6fd; border: none; padding: 2px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;">QU</button>
      <button type="button" onclick="cambiarIdioma('en')" id="btn-lang-en" style="background: transparent; color: #bae6fd; border: none; padding: 2px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;">EN</button>
    </div>
  </div>

  <!-- Cabecera Institucional -->
  <header style="background-color: var(--color-paper); border-bottom: 2px solid var(--color-sky-wash); padding: 0.85rem 1.5rem;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
      <a href="{{ route('inicio') }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: inherit;">
        <img src="{{ asset('brand/logo_lat_azul.svg') }}" alt="LAT Lácteos" style="height: 40px;" onerror="this.src='{{ asset('favicon.svg') }}'">
        <div>
          <span style="font-family: var(--font-display); font-size: 1.45rem; font-weight: bold; color: var(--color-slate-deep); display: block; line-height: 1;">LAT</span>
          <span style="font-size: 0.72rem; color: var(--color-graphite); letter-spacing: 0.5px; text-transform: uppercase;">Gestión Láctea Rural</span>
        </div>
      </a>

      <nav style="display: flex; align-items: center; gap: 1rem;">
        <a href="{{ route('inicio') }}" style="color: var(--color-slate-deep); text-decoration: none; font-weight: 500; font-size: 0.92rem; padding: 0.5rem 0.75rem;">Inicio</a>
        <a href="{{ route('inicio') }}#nosotros" style="color: var(--color-slate-deep); text-decoration: none; font-weight: 500; font-size: 0.92rem; padding: 0.5rem 0.75rem;">Nosotros</a>

        @auth
          <a href="{{ route('panel') }}" class="btn-lat btn-ember" style="padding: 0.5rem 1rem; text-decoration: none; font-size: 0.9rem; font-weight: 500;">
            Mi Panel Lácteo
          </a>
          <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" style="background: var(--color-fog); color: var(--color-graphite); border: none; padding: 0.5rem 0.85rem; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
              Salir ({{ Auth::user()->name }})
            </button>
          </form>
        @else
          <a href="{{ route('login') }}" class="btn-lat btn-ember" style="padding: 0.5rem 1.25rem; text-decoration: none; font-size: 0.9rem; font-weight: 500;">
            Iniciar Sesión
          </a>
        @endauth
      </nav>
    </div>
  </header>

  <!-- Notificaciones Flash -->
  <div style="max-width: 1200px; margin: 1rem auto 0; padding: 0 1.5rem; width: 100%;">
    @if(session('exito'))
      <div style="background-color: var(--verde-fill); color: var(--verde-text); padding: 0.85rem 1.25rem; border-radius: 8px; margin-bottom: 1rem; font-weight: 500;">
        {{ session('exito') }}
      </div>
    @endif
    @if(session('info'))
      <div style="background-color: var(--celeste-fill); color: var(--celeste-text); padding: 0.85rem 1.25rem; border-radius: 8px; margin-bottom: 1rem; font-weight: 500;">
        {{ session('info') }}
      </div>
    @endif
    @if($errors->any())
      <div style="background-color: var(--rojo-fill); color: var(--rojo-text); padding: 0.85rem 1.25rem; border-radius: 8px; margin-bottom: 1rem;">
        <strong>Revise los siguientes campos:</strong>
        <ul style="margin: 0.5rem 0 0 1.25rem;">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>

  <!-- Contenido de Cada Vista -->
  <main style="flex: 1; max-width: 1200px; width: 100%; margin: 0 auto; padding: 1.5rem;">
    @yield('contenido')
  </main>

  <!-- Pie de Página Institucional Azul Océano Acuarela -->
  <footer style="background-color: var(--color-ocean); color: #e1f0fa; padding: 2.5rem 1.5rem; margin-top: 3rem;">
    <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
      <div>
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
          <img src="{{ asset('brand/logo_lat_blanco_transparente.svg') }}" alt="LAT" style="height: 36px;" onerror="this.src='{{ asset('favicon.svg') }}'">
          <span style="font-family: var(--font-display); font-size: 1.3rem; font-weight: bold; color: #ffffff;">LAT LÁCTEOS</span>
        </div>
        <p style="font-size: 0.85rem; line-height: 1.6; color: #bae6fd;">
          Software universitario comunitario diseñado para la soberanía técnica de microempresas queseras y asociaciones ganaderas altoandinas de Anta / Cusco.
        </p>
        <p style="font-size: 0.8rem; color: #7dd3fc; margin-top: 0.75rem;">
          Comisión: <strong>S/ 0.00 garantizada por principio de diseño</strong>.
        </p>
      </div>

      <div>
        <h4 style="color: #ffffff; font-size: 0.95rem; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Pilares Éticos</h4>
        <ul style="list-style: none; padding: 0; font-size: 0.82rem; line-height: 1.8; color: #bae6fd;">
          <li>✓ 0% Comisión a pequeños productores</li>
          <li>✓ Dictado por voz para campo y una sola mano</li>
          <li>✓ Triage sanitario ético: Leche ácida a quesillos de 4 días</li>
          <li>✓ Privacidad Ley N.° 29733 (Sin DNI a jornales de apoyo)</li>
        </ul>
      </div>

      <div>
        <h4 style="color: #ffffff; font-size: 0.95rem; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Arquitectura Académica</h4>
        <p style="font-size: 0.82rem; line-height: 1.6; color: #bae6fd;">
          Proyecto derivado optimizado para despliegue ligero en Apache / XAMPP con MariaDB. Implementa patrón MVC + Service Layer, prevención N+1 y estilo Flat Fill Contrast.
        </p>
        <div style="margin-top: 1rem; font-size: 0.78rem; color: #7dd3fc;">
          &copy; {{ date('Y') }} LAT Comunitario · Código Libre Académico
        </div>
      </div>
    </div>
  </footer>

  <!-- Script Dinámico Trilingüe (Español / Quechua / English) -->
  <script>
    const traducciones = {
      es: {
        lema: "Tecnología Comunitaria sin Fines de Lucro · Comisión S/ 0.00",
        heroTitular: "Donde antes un cuaderno se perdía en el agua, hoy manda la voz.",
        heroSub: "Digitalización accesible para centros de acopio rural, control sanitario de porongo en recepción, monitoreo térmico de tina y liquidaciones transparentes.",
        btnDemo: "Acceder a Mi Panel"
      },
      qu: {
        lema: "Llactakunapaq Allin Llamkay · Manan Qullqita Chaskinchu S/ 0.00",
        heroTitular: "Ñawpaqta qillqana maytu yakuwan chinkaqtin, kunanqa kunkallawanñam.",
        heroSub: "Chakra runakunapaq lechikuna qillqay, allin kaqta qhawariy, tina ruphay tupuy, chanta qusqa chaninpi qunakuy.",
        btnDemo: "Panel Lácteoman Yaykuy"
      },
      en: {
        lema: "Nonprofit Community Tech · Zero Commission S/ 0.00",
        heroTitular: "Where notebooks once drowned in whey, today your voice leads.",
        heroSub: "Accessible digital intake for rural dairy plants, rapid milk quality inspection, vat thermal monitoring, and fair bi-weekly settlements.",
        btnDemo: "Enter Dashboard"
      }
    };

    function cambiarIdioma(lang) {
      document.getElementById('top-lema').innerText = traducciones[lang].lema;
      const t = document.getElementById('hero-titular');
      if (t) t.innerText = traducciones[lang].heroTitular;
      const s = document.getElementById('hero-sub');
      if (s) s.innerText = traducciones[lang].heroSub;

      ['es', 'qu', 'en'].forEach(l => {
        const b = document.getElementById('btn-lang-' + l);
        if (b) {
          if (l === lang) {
            b.style.background = 'rgba(255,255,255,0.25)';
            b.style.color = '#ffffff';
            b.style.fontWeight = 'bold';
          } else {
            b.style.background = 'transparent';
            b.style.color = '#bae6fd';
            b.style.fontWeight = 'normal';
          }
        }
      });
    }
  </script>
  @stack('scripts')
</body>
</html>
