@extends('layouts.app')

@section('titulo', 'LAT · Plataforma Láctea Comunitaria · Cusco Provincia')

@section('contenido')
  <!-- HERO: Lienzo acuarela + Grilla editorial suiza + Carrusel con botón (+) -->
  <section class="hero-farm-wrap hero-acuarela lienzo-acuarela" aria-labelledby="hero-titular">
    <div class="aguada aguada-cielo" aria-hidden="true"></div>
    <div class="contenedor hero-grid">
      <div>
        <h1 id="hero-titular" class="hero-titular">
          Donde antes un cuaderno se perdía en el agua, <span class="hero-titular-highlight">hoy manda la voz.</span>
        </h1>

        <p id="hero-sub" class="hero-bajada">
          Digitalización accesible para centros de acopio rural, control sanitario de porongo en recepción, monitoreo térmico de tina y liquidaciones transparentes al 0% de comisión.
        </p>

        <div class="hero-acciones">
          <a href="{{ auth()->check() ? route('panel') : route('login') }}" class="btn btn-ember btn-grande">
            Entrar a Mi Panel Operativo
          </a>
          <a href="#soluciones" class="btn btn-outline btn-grande">
            <span>Ver herramientas de terreno</span>
            <span class="btn-arrow" aria-hidden="true">↗</span>
          </a>
        </div>

        <ul class="hero-sellos" aria-label="Compromisos de la plataforma">
          <li class="sello"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>S/ 0.00 comisión</li>
          <li class="sello"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Dictado por voz y modo una mano</li>
          <li class="sello"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Triage sanitario 74°</li>
          <li class="sello"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Ley N.° 29733 (sin DNI)</li>
        </ul>
      </div>

      <div>
        <!-- Tarjeta de Medios: border-radius 8px, overflow: hidden, botón (+) en esquina -->
        <div class="hero-banner-frame" id="hero-carousel" role="group" aria-roledescription="carrusel" aria-label="Galería fotográfica referencial de actividades lácteas">
          <div class="hero-gradiente-mask"></div>

          <img src="https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=1200&q=80"
               alt="Ganado lechero en pasturas andinas" class="crossfade-img activa"
               data-caption="Ganado lechero en pasturas andinas" loading="eager">
          <img src="https://images.unsplash.com/photo-1596733430284-f7437764b1a9?auto=format&fit=crop&w=1200&q=80"
               alt="Establo lechero familiar" class="crossfade-img"
               data-caption="Establo lechero familiar" loading="lazy">
          <img src="https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?auto=format&fit=crop&w=1200&q=80"
               alt="Bodega de maduración de quesos artesanales" class="crossfade-img"
               data-caption="Maduración de quesos artesanales" loading="lazy">
          <img src="https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=1200&q=80"
               alt="Tina de cuajado en una quesería" class="crossfade-img"
               data-caption="Cuajado en tina" loading="lazy">

          <div class="hero-banner-pie">
            <span id="banner-caption" aria-live="off">Ganado lechero en pasturas andinas</span>
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; margin-right: 36px;">
              <div class="banner-dots" id="banner-dots">
                <button type="button" class="banner-dot activo" onclick="irASlide(0)" aria-label="Ver foto 1 de 4"></button>
                <button type="button" class="banner-dot" onclick="irASlide(1)" aria-label="Ver foto 2 de 4"></button>
                <button type="button" class="banner-dot" onclick="irASlide(2)" aria-label="Ver foto 3 de 4"></button>
                <button type="button" class="banner-dot" onclick="irASlide(3)" aria-label="Ver foto 4 de 4"></button>
              </div>
              <button type="button" id="btn-pausa-banner" class="btn-banner-control" onclick="togglePausaBanner()" aria-pressed="false">
                <svg id="icono-pausa" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                <span id="texto-pausa" class="txt">Pausa</span>
              </button>
            </div>
          </div>

          <!-- Botón cuadrado (+) anclado en esquina inferior derecha -->
          <button type="button" class="btn-plus-corner" aria-label="Información de la imagen referencial" title="Fotografía referencial">+</button>
        </div>
        <p class="nota-fotos">Fotografías referenciales de stock, no de socios de la plataforma.</p>
      </div>
    </div>
  </section>

  <!-- FRANJA: principios operativos reales en celdas Swiss Grid -->
  <section class="franja stats-strip-wrap" aria-label="Principios operativos">
    <div class="contenedor">
      <ul class="franja-grid">
        <li class="franja-item"><strong>S/ 0.00</strong><span>Comisión comunitaria</span></li>
        <li class="franja-item"><strong>74° alcohol</strong><span>Triage con cero desperdicio</span></li>
        <li class="franja-item"><strong>36–40 °C</strong><span>Control térmico de tina</span></li>
        <li class="franja-item"><strong>Modo 1 mano</strong><span>Voz y botonera táctil de 54 px</span></li>
      </ul>
    </div>
  </section>

  <!-- HERRAMIENTAS: Proceso con Eje Central Vertical (Spine Timeline) -->
  <section id="soluciones" class="seccion" aria-labelledby="titulo-soluciones">
    <div class="contenedor">
      <div class="seccion-cabecera">
        <h2 id="titulo-soluciones">Herramientas de trabajo en campo</h2>
        <p>Pensadas para las condiciones reales de las microempresas lácteas: manos con guantes, humedad de suero y conectividad intermitente.</p>
        <hr class="hairline-rule-dashed" style="margin-top: 2rem;">
      </div>

      <div class="arbol">
        <ol class="arbol-lista">
          <li class="arbol-fila">
            <div class="arbol-texto">
              <span class="arbol-paso">Paso 1</span>
              <h3>Acopio por voz, a una mano</h3>
              <p>Dictado por micrófono en la recepción de porongos, con confirmación hablada. Si no hay voz, una botonera táctil de 54 px permite registrar sin mirar.</p>
            </div>
            <div class="arbol-demo">
              <div class="demo-card">
                <p class="demo-etq">Frase dictada</p>
                <div class="waveform-anim" aria-hidden="true"><span class="waveform-bar"></span><span class="waveform-bar"></span><span class="waveform-bar"></span><span class="waveform-bar"></span><span class="waveform-bar"></span></div>
                <p class="demo-valor" style="font-size: 1.25rem; font-weight: 600;">“Don Eloy Condori, 35 litros”</p>
                <p class="demo-nota">Ejemplo ilustrativo del comando de voz.</p>
              </div>
            </div>
          </li>

          <li class="arbol-fila">
            <div class="arbol-texto">
              <span class="arbol-paso">Paso 2</span>
              <h3>Triage sanitario ético</h3>
              <p>Prueba rápida de alcohol a 74° al vaciar el porongo. La leche conforme va a queso maduro o yogur; la que presenta acidez se deriva a quesillos frescos de 4 días en lugar de descartarse.</p>
            </div>
            <div class="arbol-demo">
              <div class="demo-card">
                <p class="demo-etq">Destino según la prueba</p>
                <div class="demo-ruta">
                  <div><span>Leche aprobada</span><span class="chip chip-verde">Tina principal</span></div>
                  <div><span>Leche con acidez</span><span class="chip chip-amarillo">Quesillo 4 días</span></div>
                </div>
              </div>
            </div>
          </li>

          <li class="arbol-fila">
            <div class="arbol-texto">
              <span class="arbol-paso">Paso 3</span>
              <h3>Monitor térmico y receta automática</h3>
              <p>Seguimiento del enfriamiento desde 72 °C hasta el punto de cuajado (36–40 °C) y cálculo de cuajo, sal y cultivo según el volumen de la tina.</p>
            </div>
            <div class="arbol-demo">
              <div class="demo-card">
                <p class="demo-etq">Dosificación estándar por cada 100 L</p>
                <div class="receta-grid">
                  <div><strong>2.5 ml</strong>Cuajo líquido</div>
                  <div><strong>1.8 kg</strong>Sal común</div>
                  <div><strong>1.0 g</strong>Cultivo iniciador</div>
                  <div><strong>11</strong>Moldes de 1 kg (estimado)</div>
                </div>
              </div>
            </div>
          </li>

          <li class="arbol-fila">
            <div class="arbol-texto">
              <span class="arbol-paso">Paso 4</span>
              <h3>Cuentas claras y respaldo contable</h3>
              <p>Liquidación quincenal con descuento de adelantos en efectivo o en queso, pago de ayuda del día sin exigir DNI (Ley N.° 29733) y balance exportable en CSV para el contador.</p>
            </div>
            <div class="arbol-demo">
              <div class="demo-card">
                <p class="demo-etq">Cálculo del bruto por entrega</p>
                <p class="demo-valor">35 L × S/ 1.65 = S/ 57.75</p>
                <p class="demo-nota">Ejemplo ilustrativo. Comisión de la plataforma: S/ 0.00.</p>
              </div>
            </div>
          </li>
        </ol>
      </div>
    </div>
  </section>

  <!-- EMPRESAS FAMILIARES Y PROCESO: Bento Grid con Hairlines Expuestas -->
  <section id="empresas-familiares" class="banda-cielo lienzo-acuarela" aria-labelledby="titulo-empresas">
    <div class="aguada aguada-nube" aria-hidden="true"></div>
    <div class="contenedor">
      <div class="seccion-cabecera" style="margin-bottom: 2.5rem;">
        <h2 id="titulo-empresas">Del campo a la quincena, sin cuaderno</h2>
        <p>Un mismo flujo conecta la ruta de acopio, la planta y la contabilidad.</p>
        <hr class="hairline-rule-dashed" style="margin-top: 1.5rem;">
      </div>

      <div class="bento">
        <div>
          <div class="bento-card">
            <h3>Cómo funciona</h3>
            <ol class="pasos">
              <li><strong>Captura en ruta sin papel</strong>El recolector dicta o toca la cantidad; el registro queda con hora y productor.</li>
              <li><strong>Triage y receta en planta</strong>Se clasifica la leche y se calculan los insumos de cada tina.</li>
              <li><strong>Liquidación y CSV</strong>La quincena se cierra con adelantos descontados y balance descargable.</li>
            </ol>
          </div>
          <div class="bento-card">
            <h3>Compromiso comunitario</h3>
            <p>Software sin fines de lucro: <strong>comisión S/ 0.00</strong>, el mismo trato para quien entrega 5 litros que para quien entrega 150.</p>
          </div>
        </div>

        <div class="bento-card">
          <h3>Dirigida a empresas familiares</h3>
          <p style="margin-bottom: 1.25rem; font-size: 0.9375rem; color: var(--color-graphite);">
            Desarrollada para la realidad cotidiana de queserías artesanales, establos lecheros y microempresas gestionadas por familias:
          </p>
          <ul class="comunidades" style="grid-template-columns: 1fr;">
            <li style="flex-direction: column; align-items: flex-start; gap: 0.35rem; padding: 0.85rem 1rem;">
              <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-ink); font-weight: 700;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Queserías familiares y artesanales
              </span>
              <small style="color: var(--color-graphite); font-size: 0.8125rem; font-weight: normal; line-height: 1.4;">
                Unidades lecheras gestionadas entre padres e hijos que procesan de 50 a 1,000 litros al día sin licencias costosas.
              </small>
            </li>
            <li style="flex-direction: column; align-items: flex-start; gap: 0.35rem; padding: 0.85rem 1rem;">
              <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-ink); font-weight: 700;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/></svg>
                Rutas de acopio de pequeños ganaderos
              </span>
              <small style="color: var(--color-graphite); font-size: 0.8125rem; font-weight: normal; line-height: 1.4;">
                Recepción por porongos en parcelas rurales con registro táctil o por voz sin necesidad de cuaderno escolar.
              </small>
            </li>
            <li style="flex-direction: column; align-items: flex-start; gap: 0.35rem; padding: 0.85rem 1rem;">
              <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-ink); font-weight: 700;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                Centros rurales de transformación de queso
              </span>
              <small style="color: var(--color-graphite); font-size: 0.8125rem; font-weight: normal; line-height: 1.4;">
                Control térmico de pasteurización en tina, cálculo de cuajo y liquidaciones quincenales transparentes.
              </small>
            </li>
          </ul>
          <p class="compromiso" style="margin-top: 1rem; font-size: 0.8125rem; color: var(--color-graphite);">
            Sin cuotas de suscripción ni cobros por litro procesado (Comisión S/ 0.00).
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- NOSOTROS Y PILARES -->
  <section id="nosotros" class="seccion nosotros" aria-labelledby="titulo-nosotros">
    <div class="contenedor">
      <div class="nosotros-intro">
        <h2 id="titulo-nosotros">La realidad de nuestras microempresas familiares</h2>
        <p>LAT no es una startup de intermediación financiera ni una franquicia de software comercial. Nace para defender el trabajo lechero de padres e hijos en Cusco Provincia, eliminando las disputas en la liquidación quincenal y cuidando la soberanía de los datos productivos.</p>
        <hr class="hairline-rule-dashed" style="margin-top: 1.5rem;">
      </div>

      <ul class="pilares">
        <li class="pilar">
          <span class="marca" aria-hidden="true"></span>
          <h3>Comisión S/ 0.00</h3>
          <p>Cada céntimo del litro de leche pertenece al productor y a la microempresa quesera, sin comisiones por software.</p>
        </li>
        <li class="pilar">
          <span class="marca" aria-hidden="true"></span>
          <h3>Trato justo y sin sesgo</h3>
          <p>Mismo respeto, tarifa y precisión de pesaje para quien entrega 5 litros que para quien entrega 150.</p>
        </li>
        <li class="pilar">
          <span class="marca" aria-hidden="true"></span>
          <h3>Privacidad según la Ley N.° 29733</h3>
          <p>Minimización de datos: no se exige DNI a colaboradores de faena eventual de lavado o volteo.</p>
        </li>
        <li class="pilar">
          <span class="marca" aria-hidden="true"></span>
          <h3>Cero desperdicio de leche</h3>
          <p>La leche no apta para queso maduro no se desecha: se transforma en quesillos frescos de 4 días.</p>
        </li>
      </ul>
    </div>
  </section>

  <!-- CIERRE: Llamado a la acción recto Swiss -->
  <section class="cierre lienzo-acuarela" aria-labelledby="titulo-cierre">
    <div class="aguada aguada-cielo" aria-hidden="true" style="transform: scaleX(-1) scale(1.12);"></div>
    <div class="contenedor">
      <div class="cierre-inner">
        <h2 id="titulo-cierre">Lleve el acopio de su planta al panel</h2>
        <p>Ingrese con su usuario de operario o el correo de la microempresa.</p>
        <a href="{{ auth()->check() ? route('panel') : route('login') }}" class="btn btn-ember btn-grande">
          <span>{{ auth()->check() ? 'Ir a Mi Panel' : 'Iniciar Sesión' }}</span>
          <span class="btn-arrow" aria-hidden="true">↗</span>
        </a>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    // Carrusel con desvanecimiento, pausa accesible (WCAG 2.2.2) y respeto de movimiento reducido
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
        if (i === indice && caption && img.dataset.caption) caption.textContent = img.dataset.caption;
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
      texto.textContent = bannerPausado ? 'Reanudar' : 'Pausa';
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
    });
  </script>
@endpush
