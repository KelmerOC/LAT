# PRODUCT SPECIFICATION: LAT (PLATAFORMA WEB PARA MICROEMPRESAS LÁCTEAS)

## 1. Visión y Propósito Generalizado

LAT es una plataforma web comunitaria y abierta diseñada para apoyar a **microempresas familiares, asociaciones ganaderas y pequeños productores dedicados a la transformación de leche, queso, yogur y derivados lácteos**.

Nace como respuesta integral a la problemática del acopio rural, donde la humedad, el suero y las condiciones de campo deterioraban los cuadernos escolares de pesaje, generando disputas en la liquidación quincenal. La plataforma digitaliza de manera accesible el flujo operativo completo: acopio por voz a una sola mano, control sanitario en recepción, estandarización de recetas de tina, liquidación de pagos y adelantos sin comisiones (S/ 0.00), y exportación de balances para contabilidad formal y entidades bancarias.

---

## 2. Arquitectura de Navegación en Dos Zonas

Para evitar la sobrecarga cognitiva de los operarios de campo y proteger la soberanía de los datos productivos, LAT divide rigurosamente su navegación en dos áreas:

### A. Portal Público (Abierto y Transparente)
1. **Inicio (`/`):**
   - **Hero con Transición de Fotos y Máscara Gradiente:** Titular humano a la izquierda (*"Donde antes un cuaderno se perdía en el agua, hoy manda la voz"*), selector trilingüe (Español, Quechua y English) y carrusel fotográfico con botón accesible de pausa/reanudación (WCAG 2.2.2).
   - **Herramientas Operativas:** 4 bloques de contraste por relleno sin bordes explicando el acopio por voz, el control sanitario rápido, el monitor térmico de tina y el pago quincenal limpio.
   - **Qué Ofrece la Plataforma al Sector Lácteo (Propuesta de Valor Real y Honesta):**
     *En estricto cumplimiento del principio ético de Veracidad y No Alucinación, la plataforma erradica métricas falsas, contadores inflados de empresas asociadas o números de impacto inventados. En su lugar, presenta sus 4 soluciones directas:*
     1. **Digitalización Accesible (Voz y Tacto):** Reemplazo del cuaderno físico mediante dictado por voz o teclas grandes táctiles adaptadas a operarios con guantes o adultos mayores.
     2. **Calidad y Cero Desperdicio:** Clasificación ágil de leche fresca para queso maduro y desvío ético a quesillos frescos de 4 días si existe acidez. Cada litro conserva su valor.
     3. **Estandarización de Recetas:** Cálculo exacto de insumos (cuajo líquido, sal común y cultivo iniciador) según el volumen recibido en el día para garantizar uniformidad en cada lote.
     4. **Cuentas Claras y Respaldo Contable:** Liquidaciones quincenales justas con descuento de adelantos en efectivo o queso, y balances consolidados en formato CSV para SUNAT y bancos.
2. **Nosotros (`/nosotros`):**
   - Historia de las microempresas familiares queseras y la realidad del trabajo conjunto entre padres e hijos.
   - Los **7 Pilares Éticos y Sociales de LAT:**
     1. Comisión S/ 0.00 (software comunitario, sin costo por litro acopiado).
     2. Trato Justo y Sin Sesgo (igual precio y respeto a proveedores de 5 L o 150 L).
     3. Tecnologías Abiertas y Soberanas (OpenStreetMap y Leaflet.js sin licencias de pago).
     4. Privacidad y Cumplimiento de la Ley N.° 29733 (sin exigencia de DNI a colaboradores de apoyo por jornal eventual).
     5. Resiliencia Operativa Offline (PWA / localStorage: trabajo continuo sin internet en zonas rurales).
     6. Formación y Soporte Abierto (articulación con escuelas técnicas, universidades y asociaciones aliadas).
     7. Cero Desperdicio y Aprovechamiento Total (transformación solidaria de leche ácida en subproductos).
3. **Registro Centralizado e Inicio de Sesión (`/registrarse`, `/iniciar-sesion`):**
   - Inscripción de la microempresa mediante su correo principal (validación de sintaxis RFC y registros DNS).
   - Verificación obligatoria con código temporal de 6 dígitos.
   - Creación interna de cuentas para operarios por usuario y contraseña, sin exigir correos personales a los trabajadores de campo.

### B. Portal Privado (`Mi Panel` — `/mi-panel`)
Herramientas estándar y configurables para cualquier quesería o microempresa láctea:

1. **Tablero de Control y Métricas Diarias:**
   - Leche total recibida hoy en litros.
   - Precio por litro vigente fijado por la microempresa.
   - Estado térmico y lote activo de tina.
   - Estado del control sanitario (entregas verificadas / pendientes).
2. **Módulo 1: Acopio por Voz y Modo 1 Mano (`/mi-panel/acopio`):**
   - Dictado rápido por voz (*"Don Eloy Condori, 35 litros"*) con síntesis de audio confirmatoria.
   - Respaldo manual con teclado táctil de 54px para uso con una sola mano.
   - Cola fuera de línea en `localStorage` con sincronización automática al detectar conexión.
   - Mapa de ruta con georreferenciación de comunidades productoras sobre OpenStreetMap.
3. **Módulo 2: Calidad Sanitaria y Desvío a Quesillos (`/mi-panel/calidad`):**
   - Prueba rápida de alcohol a 74° al vaciar el porongo.
   - Aprobación para tina principal (queso maduro / yogur) o desvío ágil a quesillos frescos de 4 días.
4. **Módulo 3: Monitor de Tina y Receta Automática (`/mi-panel/tina`):**
   - Curva térmica de enfriamiento desde 72 °C hasta la temperatura óptima de inoculación (36–40 °C).
   - Dosificación automática basada en ratios de producción estándar:
     - Cuajo: 2.5 ml por cada 100 L.
     - Sal común: 1.8 kg por cada 100 L.
     - Cultivo lácteo: 1.0 g por cada 100 L.
     - Rendimiento estimado: 11 moldes de 1 kg por cada 100 L.
5. **Módulo 4: Liquidación Quincenal, Gastos y Contador (`/mi-panel/quincena`):**
   - Liquidación neta automática con descuento de adelantos en efectivo o en moldes de queso.
   - **Registro de Apoyo del Día (Jornal Eventual):** Asentamiento ágil de pagos en mano por faenas de lavado o volteo sin exigir DNI, cumpliendo el principio de minimización de datos (Ley N.° 29733).
   - Registro de ventas de mostrador y otros gastos de operación.
6. **Módulo 5: Exportación Contable para SUNAT y Bancos:**
   - Descarga directa en un solo clic de balance mensual en formato CSV para el contador externo.
7. **Módulo 6: Accesos por Cargo (`/mi-panel/accesos`):**
   - Creación y administración ligera de operadores con contraseñas seguras Bcrypt, sin requerir correos electrónicos personales.

---

## 3. Modelo de Roles y Permisos (RBAC)

| Cargo | Usuario de Prueba | Permisos Operativos |
| :--- | :--- | :--- |
| **Administración** | `donjusto@sanborreal.pe` | Control total de módulos, finanzas, usuarios y configuración de la microempresa. |
| **Encargado de Planta** | `carlos.anta` | Gestión diaria de acopio, control sanitario, tina y producción de derivados. |
| **Recolector en Ruta** | `mateo.ruta` | Acceso optimizado para acopio por voz y mapa de ruta; restringido de finanzas y accesos. |
| **Control de Tina / Calidad** | `sabina.tina` | Pruebas sanitarias de acidez, termómetro de pasteurización y cálculo de receta. |
| **Contador Externo** | `contador.marco` | Consulta de liquidaciones quincenales, gastos operativos y descarga de balances CSV. |
