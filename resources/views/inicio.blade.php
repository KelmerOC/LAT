@extends('layouts.app')

@section('titulo', 'LAT · Donde manda la voz y el trabajo lechero')

@section('contenido')
  <!-- SECCIÓN HERO: Portada Humana No Genérica -->
  <section style="background-color: var(--color-fog); border-radius: 12px; padding: 3rem 2.5rem; margin-bottom: 2.5rem; display: grid; grid-template-columns: 1fr; gap: 2rem;">
    <div>
      <span style="display: inline-block; background-color: var(--color-celeste-suave); color: var(--color-celeste); font-size: 0.8rem; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1rem;">
        Digitalización Rural Andina · Anta, Cusco
      </span>
      <h1 id="hero-titular" style="font-family: var(--font-display); font-size: clamp(2rem, 4vw, 3.2rem); line-height: 1.15; color: var(--color-slate-deep); margin-bottom: 1.25rem;">
        Donde antes un cuaderno se perdía en el agua, hoy manda la voz.
      </h1>
      <p id="hero-sub" style="font-size: 1.1rem; line-height: 1.65; color: var(--color-graphite); max-width: 800px; margin-bottom: 2rem;">
        Digitalización accesible para centros de acopio rural, control sanitario de porongo en recepción, monitoreo térmico de tina y liquidaciones transparentes al 0% de comisión.
      </p>

      <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
        <a href="{{ route('panel') }}" class="btn-lat btn-ember" style="padding: 0.85rem 1.75rem; font-size: 1rem; font-weight: 500; text-decoration: none;">
          Entrar a Mi Panel Operativo
        </a>
        <a href="#soluciones" style="background-color: var(--color-sky-wash); color: var(--color-slate-deep); padding: 0.85rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 0.95rem;">
          Conocer las 4 Herramientas
        </a>
      </div>
    </div>
  </section>

  <!-- SECCIÓN 1: Las 4 Herramientas Operativas de Terreno -->
  <section id="soluciones" style="margin-bottom: 3.5rem;">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 2.5rem;">
      <h2 style="font-family: var(--font-display); font-size: 2rem; color: var(--color-slate-deep); margin-bottom: 0.5rem;">
        Herramientas de Trabajo en Campo
      </h2>
      <p style="color: var(--color-graphite); font-size: 0.95rem;">
        Diseñadas para resistir las condiciones reales de planta: manos con guantes, humedad de suero y conectividad intermitente.
      </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
      <!-- Herramienta 1: Acopio por voz -->
      <div style="background-color: var(--color-fog); padding: 2rem; border-radius: 12px; display: flex; flex-direction: column;">
        <div style="background-color: var(--color-celeste-suave); color: var(--color-celeste); width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 1.25rem;">
          01
        </div>
        <h3 style="color: var(--color-slate-deep); font-size: 1.25rem; margin-bottom: 0.75rem;">Acopio por Voz (1 Mano)</h3>
        <p style="color: var(--color-graphite); font-size: 0.9rem; line-height: 1.6; flex: 1;">
          Dictado por micrófono <em>"Don Eloy, 35 litros"</em> con síntesis de audio confirmatoria. Teclado táctil de 54px para uso sin mirar y cola offline.
        </p>
        <span style="margin-top: 1rem; font-size: 0.8rem; font-weight: 600; color: var(--color-celeste);">✓ Sin cuadernos mojados</span>
      </div>

      <!-- Herramienta 2: Control Sanitario -->
      <div style="background-color: var(--color-sky-wash); padding: 2rem; border-radius: 12px; display: flex; flex-direction: column;">
        <div style="background-color: #ffffff; color: var(--color-slate-deep); width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 1.25rem;">
          02
        </div>
        <h3 style="color: var(--color-slate-deep); font-size: 1.25rem; margin-bottom: 0.75rem;">Triage Sanitario Ético</h3>
        <p style="color: var(--color-graphite); font-size: 0.9rem; line-height: 1.6; flex: 1;">
          Prueba rápida de alcohol 74°. La leche buena ingresa a tina de queso maduro y la ácida se deriva éticamente a quesillos frescos de 4 días.
        </p>
        <span style="margin-top: 1rem; font-size: 0.8rem; font-weight: 600; color: #15803d;">✓ Cero litros desperdiciados</span>
      </div>

      <!-- Herramienta 3: Monitor Térmico -->
      <div style="background-color: var(--color-fog); padding: 2rem; border-radius: 12px; display: flex; flex-direction: column;">
        <div style="background-color: var(--color-celeste-suave); color: var(--color-celeste); width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 1.25rem;">
          03
        </div>
        <h3 style="color: var(--color-slate-deep); font-size: 1.25rem; margin-bottom: 0.75rem;">Monitor de Tina y Receta</h3>
        <p style="color: var(--color-graphite); font-size: 0.9rem; line-height: 1.6; flex: 1;">
          Curva térmica desde 72 °C hasta el punto de cuajado (36-40 °C). Dosificación exacta de cuajo líquido, sal común y cultivo iniciador según volumen.
        </p>
        <span style="margin-top: 1rem; font-size: 0.8rem; font-weight: 600; color: var(--color-ember);">✓ 2.5 ml cuajo / 100 L</span>
      </div>

      <!-- Herramienta 4: Liquidación Limpia -->
      <div style="background-color: var(--color-sky-wash); padding: 2rem; border-radius: 12px; display: flex; flex-direction: column;">
        <div style="background-color: #ffffff; color: var(--color-slate-deep); width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 1.25rem;">
          04
        </div>
        <h3 style="color: var(--color-slate-deep); font-size: 1.25rem; margin-bottom: 0.75rem;">Cuentas Claras (S/ 0.00)</h3>
        <p style="color: var(--color-graphite); font-size: 0.9rem; line-height: 1.6; flex: 1;">
          Liquidaciones netas con descuento de adelantos en efectivo o moldes de queso. Pago ágil de ayuda del día sin DNI y exportación formal en CSV.
        </p>
        <span style="margin-top: 1rem; font-size: 0.8rem; font-weight: 600; color: var(--color-slate-deep);">✓ Comisión cero por litro</span>
      </div>
    </div>
  </section>

  <!-- SECCIÓN 2: Nosotros y los 7 Pilares Éticos -->
  <section id="nosotros" style="background-color: var(--color-fog); border-radius: 12px; padding: 3rem 2.5rem; margin-bottom: 3rem;">
    <div style="max-width: 800px; margin-bottom: 2rem;">
      <span style="font-size: 0.8rem; font-weight: 600; color: var(--color-celeste); text-transform: uppercase; letter-spacing: 0.5px;">Propósito Comunitario</span>
      <h2 style="font-family: var(--font-display); font-size: 2rem; color: var(--color-slate-deep); margin-top: 0.35rem; margin-bottom: 0.75rem;">
        La realidad de nuestras microempresas familiares
      </h2>
      <p style="color: var(--color-graphite); font-size: 0.95rem; line-height: 1.65;">
        LAT no es una startup de intermediación financiera ni una franquicia de software comercial. Nace para defender el trabajo lechero de padres e hijos en la pampa de Anta, eliminando las disputas en la liquidación quincenal y asegurando la soberanía de los datos productivos.
      </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem;">
      <div style="background-color: #ffffff; padding: 1.25rem 1.5rem; border-radius: 8px;">
        <strong style="color: var(--color-slate-deep); display: block; margin-bottom: 0.35rem;">1. Comisión S/ 0.00</strong>
        <p style="color: var(--color-graphite); font-size: 0.85rem; line-height: 1.5; margin: 0;">Cada céntimo del litro de leche pertenece íntegramente al productor y a la microempresa quesera.</p>
      </div>

      <div style="background-color: #ffffff; padding: 1.25rem 1.5rem; border-radius: 8px;">
        <strong style="color: var(--color-slate-deep); display: block; margin-bottom: 0.35rem;">2. Trato Justo y Sin Sesgo</strong>
        <p style="color: var(--color-graphite); font-size: 0.85rem; line-height: 1.5; margin: 0;">Mismo respeto, tarifa y precisión de pesaje al ganadero de 5 litros que al de 150 litros.</p>
      </div>

      <div style="background-color: #ffffff; padding: 1.25rem 1.5rem; border-radius: 8px;">
        <strong style="color: var(--color-slate-deep); display: block; margin-bottom: 0.35rem;">3. Privacidad Ley N.° 29733</strong>
        <p style="color: var(--color-graphite); font-size: 0.85rem; line-height: 1.5; margin: 0;">Minimización de datos: No se exige DNI ni cuenta bancaria obligatoria a colaboradores de faena eventual.</p>
      </div>

      <div style="background-color: #ffffff; padding: 1.25rem 1.5rem; border-radius: 8px;">
        <strong style="color: var(--color-slate-deep); display: block; margin-bottom: 0.35rem;">4. Cero Desperdicio de Leche</strong>
        <p style="color: var(--color-graphite); font-size: 0.85rem; line-height: 1.5; margin: 0;">La leche no apta para queso maduro no se desecha; se transforma en quesillo fresco de 4 días.</p>
      </div>
    </div>
  </section>
@endsection
