@extends('layouts.app')

@section('titulo', 'LAT · Gestión Láctea Rural · Cusco Provincia')
@section('title_i18n', 'title.home')

@section('contenido')
  <section class="hero-farm-wrap hero-acuarela lienzo-acuarela" aria-labelledby="hero-titular">
    <div class="aguada aguada-cielo" aria-hidden="true"></div>
    <div class="contenedor hero-grid">
      <div>
        <h1 id="hero-titular" class="hero-titular">
          <span data-i18n="hero.title_1">Donde antes un cuaderno se perdía en el agua, </span><span class="hero-titular-highlight" data-i18n="hero.title_2">hoy manda la voz.</span>
        </h1>

        <p id="hero-sub" class="hero-bajada" data-i18n="hero.sub">
          Registro diario para centros de acopio rural, control de leche en recepción, seguimiento térmico de tinas y liquidaciones quincenales claras para el productor.
        </p>

        <div class="hero-acciones">
          <a href="{{ auth()->check() ? route('panel') : route('login') }}" class="btn btn-ember btn-grande" data-i18n="hero.cta_panel">
            Entrar a Mi Panel Operativo
          </a>
          <a href="#soluciones" class="btn btn-outline btn-grande">
            <span data-i18n="hero.cta_tools">Ver herramientas de terreno</span>
            <span class="btn-arrow" aria-hidden="true">↗</span>
          </a>
        </div>

        <ul class="hero-sellos" aria-label="Características principales" data-i18n-aria="hero.seals_aria">
          <li class="sello"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg><span data-i18n="hero.seal_1">S/ 0.00 comisión</span></li>
          <li class="sello"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg><span data-i18n="hero.seal_2">Dictado por voz y modo una mano</span></li>
          <li class="sello"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg><span data-i18n="hero.seal_3">Prueba de alcohol 74°</span></li>
          <li class="sello"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg><span data-i18n="hero.seal_4">Ley N.° 29733 (sin DNI)</span></li>
        </ul>
      </div>

      <div>
        <div class="hero-banner-frame" id="hero-carousel" role="group" aria-roledescription="carrusel" aria-label="Galería fotográfica de actividades lácteas" data-i18n-aria="hero.carousel_aria">
          <div class="hero-gradiente-mask"></div>

          <img src="{{ asset('brand/hero_pasturas.svg') }}"
               alt="Ganado lechero en pasturas andinas" data-i18n-alt="hero.alt_1" class="crossfade-img activa"
               data-caption="Ganado lechero en pasturas andinas" data-caption-key="hero.cap_1" loading="eager">
          <img src="{{ asset('brand/hero_establo.svg') }}"
               alt="Establo lechero familiar" data-i18n-alt="hero.alt_2" class="crossfade-img"
               data-caption="Establo lechero familiar" data-caption-key="hero.cap_2" loading="lazy">
          <img src="{{ asset('brand/hero_quesos.svg') }}"
               alt="Bodega de maduración de quesos artesanales" data-i18n-alt="hero.alt_3" class="crossfade-img"
               data-caption="Maduración de quesos artesanales" data-caption-key="hero.cap_3" loading="lazy">
          <img src="{{ asset('brand/hero_tina.svg') }}"
               alt="Tina de cuajado en una quesería" data-i18n-alt="hero.alt_4" class="crossfade-img"
               data-caption="Cuajado en tina" data-caption-key="hero.cap_4" loading="lazy">

          <div class="hero-banner-pie">
            <span id="banner-caption" aria-live="off" data-i18n="hero.cap_1">Ganado lechero en pasturas andinas</span>
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
              <div class="banner-dots" id="banner-dots">
                <button type="button" class="banner-dot activo" onclick="irASlide(0)" aria-label="Ver foto 1 de 4" data-i18n-aria="hero.dot_1"></button>
                <button type="button" class="banner-dot" onclick="irASlide(1)" aria-label="Ver foto 2 de 4" data-i18n-aria="hero.dot_2"></button>
                <button type="button" class="banner-dot" onclick="irASlide(2)" aria-label="Ver foto 3 de 4" data-i18n-aria="hero.dot_3"></button>
                <button type="button" class="banner-dot" onclick="irASlide(3)" aria-label="Ver foto 4 de 4" data-i18n-aria="hero.dot_4"></button>
              </div>
              <button type="button" id="btn-pausa-banner" class="btn-banner-control" onclick="togglePausaBanner()" aria-pressed="false">
                <svg id="icono-pausa" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                <span id="texto-pausa" class="txt" data-i18n="hero.pause">Pausa</span>
              </button>
            </div>
          </div>

          <button type="button" class="btn-plus-corner" id="btn-next-slide" aria-label="Siguiente fotografía" title="Siguiente fotografía" data-i18n-aria="hero.next_slide_aria" data-i18n-title="hero.next_slide_aria">+</button>
        </div>
      </div>
    </div>
  </section>

  <section class="franja stats-strip-wrap" aria-label="Indicadores operativos" data-i18n-aria="hero.strip_aria">
    <div class="contenedor">
      <ul class="franja-grid">
        <li class="franja-item"><strong data-i18n="strip.s1_val">S/ 0.00</strong><span data-i18n="strip.s1_lbl">Comisión por uso</span></li>
        <li class="franja-item"><strong data-i18n="strip.s2_val">74° alcohol</strong><span data-i18n="strip.s2_lbl">Leche clasificada al recibir</span></li>
        <li class="franja-item"><strong data-i18n="strip.s3_val">36–40 °C</strong><span data-i18n="strip.s3_lbl">Control térmico de tina</span></li>
        <li class="franja-item"><strong data-i18n="strip.s4_val">Modo 1 mano</strong><span data-i18n="strip.s4_lbl">Voz y botonera táctil de 54 px</span></li>
      </ul>
    </div>
  </section>

  <section id="soluciones" class="seccion" aria-labelledby="titulo-soluciones">
    <div class="contenedor">
      <div class="seccion-cabecera">
        <h2 id="titulo-soluciones" data-i18n="home.tools_title">Herramientas de trabajo en campo</h2>
        <p data-i18n="home.tools_sub">Pensadas para las condiciones reales de las microempresas lácteas: manos con guantes, humedad de suero y conectividad intermitente.</p>
        <hr class="hairline-rule-dashed" style="margin-top: 2rem;">
      </div>

      <div class="arbol">
        <ol class="arbol-lista">
          <li class="arbol-fila">
            <div class="arbol-texto">
              <span class="arbol-paso" data-i18n="steps.p1_tag">Paso 1</span>
              <h3 data-i18n="steps.p1_title">Acopio por voz, a una mano</h3>
              <p data-i18n="steps.p1_desc">Dictado por micrófono en la recepción de porongos, con confirmación hablada. Si no hay voz, una botonera táctil de 54 px permite registrar sin mirar.</p>
            </div>
            <div class="arbol-demo">
              <div class="demo-card">
                <p class="demo-etq" data-i18n="steps.p1_card_lbl">Frase dictada</p>
                <div class="waveform-anim" aria-hidden="true"><span class="waveform-bar"></span><span class="waveform-bar"></span><span class="waveform-bar"></span><span class="waveform-bar"></span><span class="waveform-bar"></span></div>
                <p class="demo-valor" style="font-size: 1.25rem; font-weight: 600;" data-i18n="steps.p1_sample">“Don Eloy Condori, 35 litros”</p>
              </div>
            </div>
          </li>

          <li class="arbol-fila">
            <div class="arbol-texto">
              <span class="arbol-paso" data-i18n="steps.p2_tag">Paso 2</span>
              <h3 data-i18n="steps.p2_title">Prueba de alcohol al recibir</h3>
              <p data-i18n="steps.p2_desc">Prueba rápida de alcohol a 74° al vaciar el porongo. La leche conforme va a queso maduro o yogur; la que presenta acidez se deriva a quesillos frescos de 4 días en lugar de descartarse.</p>
            </div>
            <div class="arbol-demo">
              <div class="demo-card">
                <p class="demo-etq" data-i18n="steps.p2_card_lbl">Destino según la prueba</p>
                <div class="demo-ruta">
                  <div><span data-i18n="steps.p2_good_lbl">Leche aprobada</span><span class="chip chip-verde" data-i18n="steps.p2_good_chip">Tina principal</span></div>
                  <div><span data-i18n="steps.p2_acid_lbl">Leche con acidez</span><span class="chip chip-amarillo" data-i18n="steps.p2_acid_chip">Quesillo 4 días</span></div>
                </div>
              </div>
            </div>
          </li>

          <li class="arbol-fila">
            <div class="arbol-texto">
              <span class="arbol-paso" data-i18n="steps.p3_tag">Paso 3</span>
              <h3 data-i18n="steps.p3_title">Monitor térmico y receta automática</h3>
              <p data-i18n="steps.p3_desc">Seguimiento del enfriamiento desde 72 °C hasta el punto de cuajado (36–40 °C) y cálculo de cuajo, sal y cultivo según el volumen de la tina.</p>
            </div>
            <div class="arbol-demo">
              <div class="demo-card">
                <p class="demo-etq" data-i18n="steps.p3_card_lbl">Dosificación estándar por cada 100 L</p>
                <div class="receta-grid">
                  <div><strong>2.5 ml</strong><span data-i18n="steps.p3_rennet">Cuajo líquido</span></div>
                  <div><strong>1.8 kg</strong><span data-i18n="steps.p3_salt">Sal común</span></div>
                  <div><strong>1.0 g</strong><span data-i18n="steps.p3_culture">Cultivo iniciador</span></div>
                  <div><strong>11</strong><span data-i18n="steps.p3_molds">Moldes de 1 kg (estimado)</span></div>
                </div>
              </div>
            </div>
          </li>

          <li class="arbol-fila">
            <div class="arbol-texto">
              <span class="arbol-paso" data-i18n="steps.p4_tag">Paso 4</span>
              <h3 data-i18n="steps.p4_title">Cuentas claras y respaldo contable</h3>
              <p data-i18n="steps.p4_desc">Liquidación quincenal con descuento de adelantos en efectivo o en queso, pago de ayuda del día sin exigir DNI (Ley N.° 29733) y balance exportable en Excel para el contador.</p>
            </div>
            <div class="arbol-demo">
              <div class="demo-card">
                <p class="demo-etq" data-i18n="steps.p4_card_lbl">Cálculo del bruto por entrega</p>
                <p class="demo-valor">35 L × S/ 1.65 = S/ 57.75</p>
              </div>
            </div>
          </li>
        </ol>
      </div>
    </div>
  </section>

  <section id="empresas-familiares" class="banda-cielo lienzo-acuarela" aria-labelledby="titulo-empresas">
    <div class="aguada aguada-nube" aria-hidden="true"></div>
    <div class="contenedor">
      <div class="seccion-cabecera" style="margin-bottom: 2.5rem;">
        <h2 id="titulo-empresas" data-i18n="home.families_title">Del campo a la quincena, sin cuaderno</h2>
        <p data-i18n="home.families_sub">Un mismo flujo conecta la ruta de acopio, la planta y la contabilidad.</p>
        <hr class="hairline-rule-dashed" style="margin-top: 1.5rem;">
      </div>

      <div class="bento">
        <div>
          <div class="bento-card">
            <h3 data-i18n="bento.how_title">Cómo funciona</h3>
            <ol class="pasos">
              <li><strong data-i18n="bento.how1_t">Captura en ruta sin papel</strong><span data-i18n="bento.how1_d">El recolector dicta o toca la cantidad; el registro queda con hora y productor.</span></li>
              <li><strong data-i18n="bento.how2_t">Clasificación y receta en planta</strong><span data-i18n="bento.how2_d">Se clasifica la leche y se calculan los insumos de cada tina.</span></li>
              <li><strong data-i18n="bento.how3_t">Liquidación y Excel</strong><span data-i18n="bento.how3_d">La quincena se cierra con adelantos descontados y balance descargable.</span></li>
            </ol>
          </div>
          <div class="bento-card">
            <h3 data-i18n="bento.comm_title">Trato parejo en ruta</h3>
            <p data-i18n="bento.comm_desc">El mismo precio por litro y registro inmediato para quien entrega 5 litros y para quien entrega 150.</p>
          </div>
        </div>

        <div class="bento-card">
          <h3 data-i18n="bento.fam_title">Dirigida a empresas familiares</h3>
          <p style="margin-bottom: 1.25rem; font-size: 0.9375rem; color: var(--color-graphite);" data-i18n="bento.fam_sub">
            Desarrollada para la realidad cotidiana de queserías artesanales, establos lecheros y microempresas gestionadas por familias:
          </p>
          <ul class="comunidades" style="grid-template-columns: 1fr;">
            <li style="flex-direction: column; align-items: flex-start; gap: 0.35rem; padding: 0.85rem 1rem;">
              <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-ink); font-weight: 700;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span data-i18n="bento.fam1_t">Queserías familiares y artesanales</span>
              </span>
              <small style="color: var(--color-graphite); font-size: 0.8125rem; font-weight: normal; line-height: 1.4;" data-i18n="bento.fam1_d">
                Unidades lecheras gestionadas entre padres e hijos que procesan de 50 a 1,000 litros al día sin licencias costosas.
              </small>
            </li>
            <li style="flex-direction: column; align-items: flex-start; gap: 0.35rem; padding: 0.85rem 1rem;">
              <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-ink); font-weight: 700;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/></svg>
                <span data-i18n="bento.fam2_t">Rutas de acopio de pequeños ganaderos</span>
              </span>
              <small style="color: var(--color-graphite); font-size: 0.8125rem; font-weight: normal; line-height: 1.4;" data-i18n="bento.fam2_d">
                Recepción por porongos en parcelas rurales con registro táctil o por voz sin necesidad de cuaderno escolar.
              </small>
            </li>
            <li style="flex-direction: column; align-items: flex-start; gap: 0.35rem; padding: 0.85rem 1rem;">
              <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-ink); font-weight: 700;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <span data-i18n="bento.fam3_t">Centros rurales de transformación de queso</span>
              </span>
              <small style="color: var(--color-graphite); font-size: 0.8125rem; font-weight: normal; line-height: 1.4;" data-i18n="bento.fam3_d">
                Control térmico de pasteurización en tina, cálculo de cuajo y liquidaciones quincenales transparentes.
              </small>
            </li>
          </ul>
          <p class="compromiso" style="margin-top: 1rem; font-size: 0.8125rem; color: var(--color-graphite);" data-i18n="bento.fam_note">
            Sin cuotas de suscripción ni cobros por litro procesado.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section id="nosotros" class="seccion nosotros" aria-labelledby="titulo-nosotros">
    <div class="contenedor">
      <div class="nosotros-intro">
        <h2 id="titulo-nosotros" data-i18n="home.about_title">La realidad de nuestras microempresas familiares</h2>
        <p data-i18n="about.intro">LAT apoya el trabajo lechero de padres e hijos en Cusco Provincia, evitando enredos en la liquidación quincenal y dando a cada planta control directo de sus registros.</p>
        <hr class="hairline-rule-dashed" style="margin-top: 1.5rem;">
      </div>

      <ul class="pilares">
        <li class="pilar">
          <span class="marca" aria-hidden="true"></span>
          <h3 data-i18n="about.p1_t">Comisión S/ 0.00</h3>
          <p data-i18n="about.p1_d">Cada céntimo del litro de leche pertenece al productor y a la microempresa quesera, sin comisiones por software.</p>
        </li>
        <li class="pilar">
          <span class="marca" aria-hidden="true"></span>
          <h3 data-i18n="about.p2_t">El mismo precio para todos</h3>
          <p data-i18n="about.p2_d">Mismo respeto, tarifa y precisión de pesaje para quien entrega 5 litros que para quien entrega 150.</p>
        </li>
        <li class="pilar">
          <span class="marca" aria-hidden="true"></span>
          <h3 data-i18n="about.p3_t">Privacidad según la Ley N.° 29733</h3>
          <p data-i18n="about.p3_d">Sin pedir DNI para jornales: no se exige documento a colaboradores de faena eventual de lavado o volteo.</p>
        </li>
        <li class="pilar">
          <span class="marca" aria-hidden="true"></span>
          <h3 data-i18n="about.p4_t">Cero desperdicio de leche</h3>
          <p data-i18n="about.p4_d">La leche no apta para queso maduro no se desecha: se transforma en quesillos frescos de 4 días.</p>
        </li>
      </ul>
    </div>
  </section>

  <section class="cierre lienzo-acuarela" aria-labelledby="titulo-cierre">
    <div class="aguada aguada-cielo" aria-hidden="true" style="transform: scaleX(-1) scale(1.12);"></div>
    <div class="contenedor">
      <div class="cierre-inner">
        <h2 id="titulo-cierre" data-i18n="home.close_title">Lleve el acopio de su planta al panel</h2>
        <p data-i18n="home.close_sub">Ingrese con su usuario de operario o el correo de la microempresa.</p>
        <a href="{{ auth()->check() ? route('panel') : route('login') }}" class="btn btn-ember btn-grande">
          <span data-i18n="{{ auth()->check() ? 'home.cta_go_panel' : 'nav.login' }}">{{ auth()->check() ? 'Ir a Mi Panel' : 'Iniciar Sesión' }}</span>
          <span class="btn-arrow" aria-hidden="true">↗</span>
        </a>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    let indiceActual = 0;
    let intervaloBanner = null;
    const reducirMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let bannerPausado = reducirMovimiento;
    const duracionSlide = 4200;

    const SVG_PAUSA = '<rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/>';
    const SVG_PLAY = '<path d="M7 4.5v15l13-7.5-13-7.5Z"/>';

    function mostrarSlide(indice) {
      const imagenes = document.querySelectorAll('#hero-carousel .crossfade-img');
      const dots = document.querySelectorAll('#banner-dots .banner-dot');
      const caption = document.getElementById('banner-caption');
      if (!imagenes.length) return;
      if (indice >= imagenes.length) indice = 0;
      if (indice < 0) indice = imagenes.length - 1;

      imagenes.forEach((img, i) => {
        img.classList.toggle('activa', i === indice);
        if (i === indice && caption) {
          const capKey = img.dataset.captionKey;
          if (capKey && window.LAT_I18N) {
            caption.setAttribute('data-i18n', capKey);
            caption.textContent = window.LAT_I18N.t(capKey);
          } else if (img.dataset.caption) {
            caption.textContent = img.dataset.caption;
          }
        }
      });
      dots.forEach((dot, i) => {
        dot.classList.toggle('activo', i === indice);
        dot.setAttribute('aria-current', i === indice ? 'true' : 'false');
      });
      indiceActual = indice;
    }

    function iniciarIntervalo() {
      if (intervaloBanner) clearInterval(intervaloBanner);
      if (!bannerPausado) intervaloBanner = setInterval(() => mostrarSlide(indiceActual + 1), duracionSlide);
    }

    function irASlide(indice) {
      mostrarSlide(indice);
      iniciarIntervalo();
    }

    function actualizarBotonPausa() {
      const icono = document.getElementById('icono-pausa');
      const texto = document.getElementById('texto-pausa');
      const btn = document.getElementById('btn-pausa-banner');
      icono.innerHTML = bannerPausado ? SVG_PLAY : SVG_PAUSA;
      const key = bannerPausado ? 'hero.resume' : 'hero.pause';
      texto.setAttribute('data-i18n', key);
      texto.textContent = window.LAT_I18N ? window.LAT_I18N.t(key) : (bannerPausado ? 'Reanudar' : 'Pausa');
      btn.setAttribute('aria-pressed', bannerPausado ? 'true' : 'false');
    }

    function togglePausaBanner() {
      bannerPausado = !bannerPausado;
      if (bannerPausado && intervaloBanner) clearInterval(intervaloBanner);
      else iniciarIntervalo();
      actualizarBotonPausa();
    }

    document.addEventListener('DOMContentLoaded', () => {
      mostrarSlide(0);
      actualizarBotonPausa();
      iniciarIntervalo();
      const btnNextSlide = document.getElementById('btn-next-slide');
      if (btnNextSlide) {
        btnNextSlide.addEventListener('click', () => irASlide(indiceActual + 1));
      }
      if (window.LAT_I18N && window.LAT_I18N.onChange) {
        window.LAT_I18N.onChange(() => {
          mostrarSlide(indiceActual);
          actualizarBotonPausa();
        });
      }
    });
  </script>
@endpush
