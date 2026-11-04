---
name: LAT Lácteos (Editorial Swiss-Grid y Acuarela Andina)
description: Sistema de diseño editorial Swiss-Grid con hairlines expuestas, geometría recta de 0px a 2px, barra de navegación plana sin bordes y aguadas de acuarela blanco, azul y naranja
colors:
  primary: "#ff5900"
  primary-hover: "#e04e00"
  primary-watercolor-wash: "#fff0e6"
  primary-terracotta-pigment: "#f97316"
  secondary: "#0284c7"
  secondary-hover: "#0369a1"
  secondary-cobalt-wash: "#dbeafe"
  tertiary-sky-aquarelle: "#e1f0fa"
  tertiary-mist-blue: "#bae6fd"
  neutral-paper: "#ffffff"
  neutral-cotton-canvas: "#f8fbfe"
  neutral-fog: "#f2f7fb"
  neutral-ocean: "#0d4068"
  neutral-slate-deep: "#0a2f4d"
  text-ink: "#0a2f4d"
  text-graphite: "#4b6276"
  text-pewter: "#688094"
  text-white: "#ffffff"
  border-hairline: "rgba(10, 47, 77, 0.12)"
  border-hairline-solid: "#e2e8f0"
  semantic-green-fill: "#dcfce7"
  semantic-green-text: "#15803d"
  semantic-yellow-fill: "#fef3c7"
  semantic-yellow-text: "#b45309"
  semantic-red-fill: "#fee2e2"
  semantic-red-text: "#b91c1c"
typography:
  display:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, sans-serif"
    fontSize: "3.125rem"
    fontWeight: 700
    lineHeight: 1.12
    letterSpacing: "-0.03em"
  headline:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, sans-serif"
    fontSize: "2.25rem"
    fontWeight: 700
    lineHeight: 1.18
    letterSpacing: "-0.025em"
  title:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, sans-serif"
    fontSize: "1.375rem"
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: "-0.015em"
  body:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif"
    fontSize: "0.9375rem"
    fontWeight: 400
    lineHeight: 1.6
    letterSpacing: "-0.01em"
  label:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, sans-serif"
    fontSize: "0.8125rem"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "0.04em"
  tabular-kpi:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, sans-serif"
    fontSize: "2.3rem"
    fontWeight: 300
    lineHeight: 1
    letterSpacing: "-0.01em"
    fontFeature: "\"tnum\" 1"
rounded:
  none: "0px"
  sm: "2px"
  media: "8px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
  xxl: "48px"
  section: "80px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.text-white}"
    typography: "{typography.label}"
    rounded: "{rounded.none}"
    elevation: "none"
    padding: "12px 24px"
    height: "48px"
  button-secondary:
    backgroundColor: "transparent"
    borderColor: "{colors.neutral-slate-deep}"
    textColor: "{colors.neutral-slate-deep}"
    typography: "{typography.label}"
    rounded: "{rounded.none}"
    elevation: "none"
    padding: "12px 22px"
    height: "48px"
  button-dictate-field:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.text-white}"
    typography: "{typography.title}"
    rounded: "{rounded.sm}"
    padding: "16px 28px"
    height: "64px"
  card-paper:
    backgroundColor: "{colors.neutral-paper}"
    textColor: "{colors.text-ink}"
    borderColor: "{colors.border-hairline}"
    rounded: "{rounded.none}"
    elevation: "none"
    padding: "24px"
  card-bento:
    backgroundColor: "{colors.neutral-paper}"
    textColor: "{colors.text-ink}"
    borderColor: "{colors.border-hairline}"
    rounded: "{rounded.none}"
    elevation: "none"
    padding: "24px"
  card-media:
    backgroundColor: "{colors.neutral-ocean}"
    textColor: "{colors.text-white}"
    rounded: "{rounded.media}"
    overflow: "hidden"
    elevation: "none"
    padding: "0px"
  input-field:
    backgroundColor: "{colors.neutral-fog}"
    textColor: "{colors.text-ink}"
    borderColor: "{colors.border-hairline}"
    typography: "{typography.body}"
    rounded: "{rounded.none}"
    padding: "12px 16px"
    height: "48px"
  chip-status:
    backgroundColor: "{colors.secondary-cobalt-wash}"
    textColor: "{colors.secondary}"
    typography: "{typography.label}"
    rounded: "{rounded.sm}"
    padding: "4px 10px"
---

# Design System: LAT Lácteos (Editorial Swiss-Grid y Acuarela Andina)

## Overview

**Creative North Star: "Editorial Swiss-Grid y Acuarela Andina"**

El sistema visual de **LAT (Plataforma Comunitaria de Gestión Láctea Rural)** fusiona la precisión arquitectónica del diseño editorial suizo (*Swiss-Grid*) con la luz de las cuencas lecheras andinas expresada en aguadas de acuarela blanco papel, azul cielo/marino y naranja ember. El resultado es un lienzo tipográfico riguroso, de líneas expuestas y geometría nítida, que erradica bordes redondeados blandos, sombras flotantes y elementos decorativos innecesarios.

El portal público transmite rigor científico y compromiso ético (Comisión S/ 0.00), mientras que el panel operativo de planta garantiza alta legibilidad y respuesta táctil bajo condiciones rurales exigentes (guantes húmedos, luz solar y manipulación con una sola mano).

**Key Characteristics:**
- **Líneas Divisoras Expuestas (Exposed Hairline Grid):** Marco continuo de `1px solid` en los laterales del contenedor maestro, reglas horizontales continuas entre secciones y divisiones interiores de 1px entre celdas multicomumna.
- **Geometría Rectangular Recta (Sharp-to-Minimal Radii):** Botones y tarjetas de contenido con esquinas estrictamente rectas (`border-radius: 0px` o `2px`) y elevación plana (`box-shadow: none`). Solo las tarjetas multimedia usan esquinas sutilmente suavizadas (`8px`) con botón cuadrado `+` en la esquina inferior derecha.
- **Navegación Plana sin Bordes:** Barra horizontal limpia de `64px–72px` con línea inferior de 1px, logotipo a la izquierda, enlaces con tipografía pura y espaciado generoso (`24px–32px`) con chevrons minimalistas `⌄` (sin cápsulas ni píldoras), y acceso en texto plano seguido de botón primario rectangular recto a la derecha.
- **Proceso con Eje Central (Spine Timeline):** Un eje vertical central de `1px solid` conecta en zigzag las 4 etapas del flujo de leche con bloques alternados a izquierda y derecha.
- **Tipografía Técnica `Inter` con Cifras Tabulares Delgadas:** Titulares asertivos y cifras de planta (litros, temperatura, soles) en peso ligero (`300`) con alineación tabular.

---

## Colors

### Primary
- **Naranja Acuarela Ember** (`#ff5900`): Acento cromático en aguadas, nodos del eje central, barras de progreso y anillos de foco accesible.
- **Naranja Ember Accesible** (`#c2410c`): Relleno de botones primarios de acción, garantizando un ratio de contraste accesible $\ge 5.2:1$ contra texto blanco sobre fondos claros.
- **Naranja Ember Oscuro** (`#9a3412`): Estado `:hover` y `:active` de botones de acción.

### Secondary
- **Azul Manantial Celeste** (`#0284c7`): Color secundario para enlaces activos, bordes de botones secundarios y nodos de proceso.
- **Azul Cobalto Profundo** (`#0369a1`): Estado `:hover` de controles secundarios.
- **Lavado Cobalto Suave** (`#dbeafe`): Relleno para chips y selecciones.

### Hairline Borders
- **Línea Divisoria Expuesta** (`rgba(10, 47, 77, 0.12)` o `#e2e8f0`): Trazo milimétrico de `1px solid` que enmarca el contenedor principal, divide secciones y separa celdas bento.

### Neutral
- **Blanco Papel Algodón** (`#ffffff`): Lienzo base dominante y relleno de tarjetas en la rejilla.
- **Lienzo Blanco Leche** (`#f8fbfe`): Superficie de fondo alternada.
- **Niebla Andina (Fog)** (`#f2f7fb`): Fondo de celdas secundarias y campos de formulario.
- **Azul Oceánico Nocturno** (`#0d4068`): Fondo de tarjetas multimedia, testimonios y vitrinas oscuras.
- **Azul Pizarra Marino** (`#0a2f4d`): Color de tipografía principal, barra utilitaria superior y footer monumental.
- **Grafito Azulado** (`#4b6276`): Texto de lectura y párrafos.
- **Peltre Sereno** (`#688094`): Metadatos y notas técnicas.

### Named Rules
**The Swiss Hairline Grid Rule.** Todo contenedor principal, sección y módulo bento expone sus límites mediante líneas de `1px solid`. No se emplean sombras difusas ni bordes gruesos decorativos.

**The Accessible Ember Rule.** Todo botón o texto naranja sobre blanco utiliza `#c2410c` para garantizar contraste WCAG 2.1 AA ($\ge 4.5:1$). El tono `#ff5900` queda reservado para pigmento en aguadas, nodos y anillos de foco.

---

## Typography

- **Display:** `Inter`, `700`, `clamp(2.25rem, 4.5vw, 3.25rem)`, `line-height: 1.12`, `letter-spacing: -0.03em`.
- **Headline (H1 / H2):** `Inter`, `700`, `1.75rem` a `2.25rem`, `line-height: 1.18`, `letter-spacing: -0.025em`.
- **Title (H3 / Celdas):** `Inter`, `600`, `1.125rem` a `1.375rem`, `line-height: 1.25`, `letter-spacing: -0.015em`.
- **Body:** `Inter`, `400`, `0.9375rem`, `line-height: 1.6`, `letter-spacing: -0.01em`.
- **Label / Microcopy:** `Inter`, `600`, `0.8125rem`, `letter-spacing: 0.04em`.
- **Tabular KPI:** `Inter`, `300`, `2.3rem`, `line-height: 1.0`, con `tabular-nums`.

**The Heading-Speaks Rule.** Los titulares abren cada bloque sin etiquetas accesorias arriba. La tipografía lidera por escala y peso.

---

## Layout & Exposed Grid

1. **Marco Continuo de Contenedor:**
   - El contenedor central (`.contenedor`) lleva `border-left: 1px solid var(--color-hairline)` y `border-right: 1px solid var(--color-hairline)` de inicio a fin.
2. **Líneas Divisorias de Sección:**
   - Reglas horizontales de `1px solid` a ancho completo o ancho de contenedor (`<hr class="hairline-rule">`) separan cada área temática.
3. **Celdas de Rejilla Multicomumna:**
   - Cuadrículas bento y métricas con divisores verticales y horizontales de `1px solid` entre celdas continuas (`border-collapse` visual).
4. **Columna Vertebral de Proceso (Central Spine):**
   - Una línea vertical de `1px solid` en el eje central de la sección de herramientas/proceso conecta los 4 pasos, con brazos de conexión horizontales de `1px` hacia las tarjetas en zigzag.
5. **Divisores Discontinuos Sutiles:**
   - Líneas de `1px dashed var(--color-hairline)` entre intros de casos de estudio y franjas de métricas.

---

## Shapes & Geometry

- **Botones Primarios:** Esquinas rectas (`border-radius: 0px` o `2px`), elevación plana (`box-shadow: none`), relleno sólido.
- **Botones Secundarios:** Esquinas rectas (`border-radius: 0px` o `2px`), fondo transparente, reborde nítido `1px solid`, acompañados por flecha `↗` o glifo `+`.
- **Tarjetas y Celdas Bento:** Esquinas rectas (`border-radius: 0px`), enmarcadas estrictamente por `1px solid` hairline, `box-shadow: none`.
- **Tarjetas Multimedia / Socios:** Esquinas sutilmente suavizadas (`border-radius: 8px`), `overflow: hidden`, con botón cuadrado (`border-radius: 2px`, `28px × 28px`) con icono `+` anclado abajo a la derecha.
- **Controles de Navegación:** Sin fondos en píldora ni bordes redondeados flotantes.

---

## Navigation Bar Style

- **Estructura y Dimensiones:** Altura de `64px–72px`, pegajosa o fija, con borde inferior `1px solid var(--color-hairline)` y cero sombras.
- **Izquierda:** Logotipo vectorial oficial y marca LAT.
- **Centro-Izquierda:** Lista horizontal de enlaces con tipografía limpia, separación de `24px–32px`, y chevrons minimalistas hacia abajo (`⌄`) en menús desplegables (sin fondos píldora ni bordes decorativos).
- **Derecha:** Enlace en texto plano para acceso secundario ("Iniciar sesión" o "Mi Panel") seguido de botón rectangular recto primario (`border-radius: 0px`) con relleno sólido ("Entrar a Mi Panel" / "Acceso Operativo"). Sin barra de búsqueda ni atajos de teclado.
- **Móvil:** Mantiene el cajón desplegable táctil accesible y el selector de módulos del panel, sincronizados con la geometría recta y las hairlines expuestas.

---

## Do's and Don'ts

### Do:
- **Do** enmarcar el contenedor principal con bordes verticales de `1px solid` y separar secciones con hairlines de `1px`.
- **Do** construir botones primarios con esquinas rectas (`0px` o `2px`) y botones secundarios con reborde de `1px` y flecha `↗` o `+`.
- **Do** implementar un eje vertical central de `1px solid` para el flujo de proceso lácteo de 4 pasos.
- **Do** suavizar las tarjetas multimedia a `8px` con botón cuadrado `+` en la esquina inferior derecha.
- **Do** mantener la barra de navegación entre `64px` y `72px` con línea inferior de 1px y enlaces sin fondos de cápsula.

### Don't:
- **Don't** aplicar `border-radius: 9999px` (píldoras) en botones o enlaces de navegación.
- **Don't** usar sombras flotantes difusas (`box-shadow`) en tarjetas o navegación.
- **Don't** omitir el glifo direccional (`↗` o `+`) en botones secundarios.
- **Don't** inventar métricas, socios o testimonios falsos; toda cifra es un dato real o ejemplo honestamente rotulado.
