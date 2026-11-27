<div align="center">

“Año de la Esperanza y el Fortalecimiento de la Democracia”

# UNIVERSIDAD CONTINENTAL

**Facultad de Ingeniería**  
**Escuela Profesional de Ingeniería de Sistemas e Informática**

---

**Asignatura:** Programación Web — **NRC:** 24589

### PROYECTO: DESARROLLO DE APLICACIÓN WEB CON IMPACTO SOCIAL
## "PLATAFORMA LÁCTEA COMUNITARIA LAT COMPACTO"
### SISTEMA DE ACOPIO RURAL POR VOZ, CONTROL SANITARIO Y LIQUIDACIÓN JUSTA

**Caso Social:** Acopio rural por voz, control sanitario y liquidación justa en cadena láctea comunitaria

---

**ESTUDIANTES:**
- Ortiz Gutierrez, Nasdig Jordan (Arquitecto de Base de Datos y Persistencia) – 33.3%
- Lovatón Casas, Arantxa Alejandra (Ingeniero Backend y Lógica Core) – 33.3%
- Obregon Choque, Kelmer Wilder (Diseñador Frontend UI/UX y Accesibilidad) – 33.4%

**DOCENTE:** ALFREDO COLLANTES MENDOZA

**Fecha y lugar:** Cusco, 2026  
**Enlace al repositorio GitHub:** https://github.com/KelmerOC/LAT

</div>

---

## ÍNDICE DE CONTENIDO

- [1. RESUMEN EJECUTIVO](#1-resumen-ejecutivo)
- [2. INTRODUCCIÓN](#2-introducción)
  - [2.1 Contexto general](#21-contexto-general)
  - [2.2 Objetivo general](#22-objetivo-general)
  - [2.3 Objetivos específicos](#23-objetivos-específicos)
  - [2.4 Alcance actual del proyecto](#24-alcance-actual-del-proyecto)
  - [2.5 Beneficiarios directos e indirectos](#25-beneficiarios-directos-e-indirectos)
  - [2.6 Justificación del uso de tecnología web con impacto social](#26-justificación-del-uso-de-tecnología-web-con-impacto-social)
- [3. PROBLEMÁTICA SOCIAL Y JUSTIFICACIÓN](#3-problemática-social-y-justificación)
  - [3.1 Descripción de la problemática](#31-descripción-de-la-problemática)
  - [3.2 Evidencia de entrevistas (mínimo 3)](#32-evidencia-de-entrevistas-mínimo-3)
  - [3.3 Justificación del impacto social](#33-justificación-del-impacto-social)
  - [3.4 Análisis del rol de la ingeniería en la solución](#34-análisis-del-rol-de-la-ingeniería-en-la-solución)
- [4. MARCO ÉTICO Y RESPONSABILIDAD SOCIAL](#4-marco-ético-y-responsabilidad-social)
  - [4.1 Impacto de la solución tecnológica en la sociedad](#41-impacto-de-la-solución-tecnológica-en-la-sociedad)
  - [4.2 Consideraciones éticas en el desarrollo](#42-consideraciones-éticas-en-el-desarrollo)
  - [4.3 Plan de sostenibilidad y transferencia a la comunidad](#43-plan-de-sostenibilidad-y-transferencia-a-la-comunidad)
  - [4.4 Políticas de respeto a la propiedad intelectual](#44-políticas-de-respeto-a-la-propiedad-intelectual)
  - [4.5 Acuerdos de trabajo y compromisos del equipo](#45-acuerdos-de-trabajo-y-compromisos-del-equipo)
- [5. DISEÑO Y ARQUITECTURA DE LA SOLUCIÓN](#5-diseño-y-arquitectura-de-la-solución)
  - [5.1 Framework seleccionado y justificación](#51-framework-seleccionado-y-justificación)
  - [5.2 Arquitectura MVC](#52-arquitectura-mvc)
  - [5.3 Modelo de datos](#53-modelo-de-datos)
  - [5.4 Diseño de interfaz](#54-diseño-de-interfaz)
  - [5.5 Diseño de seguridad web](#55-diseño-de-seguridad-web)
  - [5.6 Escalabilidad y sostenibilidad](#56-escalabilidad-y-sostenibilidad)
- [6. INFORME DE FUNCIONALIDAD DE LA PÁGINA WEB](#6-informe-de-funcionalidad-de-la-página-web)
  - [6.1 Módulos del sistema](#61-módulos-del-sistema)
  - [6.2 Flujo de navegación](#62-flujo-de-navegación)
  - [6.3 Roles y permisos](#63-roles-y-permisos)
  - [6.4 Casos de uso principales](#64-casos-de-uso-principales)
  - [6.5 Funcionalidades CRUD completas](#65-funcionalidades-crud-completas)
  - [6.6 Métricas proyectadas de impacto social](#66-métricas-proyectadas-de-impacto-social)
- [7. DESARROLLO INICIAL Y OPTIMIZACIÓN TÉCNICA](#7-desarrollo-inicial-y-optimización-técnica)
  - [7.7 Optimización de consultas y rendimiento](#77-optimización-de-consultas-y-rendimiento)
    - [7.7.1 Diagnóstico inicial](#771-diagnóstico-inicial)
    - [7.7.2 Técnicas aplicadas](#772-técnicas-aplicadas)
    - [7.7.3 Evidencias antes/después](#773-evidencias-antesdespués)
  - [7.8 Seguridad web implementada y evidencias](#78-seguridad-web-implementada-y-evidencias)
    - [7.8.1 Amenazas consideradas](#781-amenazas-consideradas)
    - [7.8.2 Controles implementados](#782-controles-implementados)
    - [7.8.3 Evidencias de pruebas](#783-evidencias-de-pruebas)
- [8. GESTIÓN DEL PROYECTO](#8-gestión-del-proyecto)
  - [8.1 Cronograma con hitos y responsables](#81-cronograma-con-hitos-y-responsables)
  - [8.2 Distribución de responsabilidades](#82-distribución-de-responsabilidades)
  - [8.3 Registro de reuniones y acuerdos](#83-registro-de-reuniones-y-acuerdos)
  - [8.4 Acuerdos de trabajo y compromisos](#84-acuerdos-de-trabajo-y-compromisos)
- [9. REFERENCIAS Y PROPIEDAD INTELECTUAL](#9-referencias-y-propiedad-intelectual)
  - [9.1 Referencias bibliográficas](#91-referencias-bibliográficas)
  - [9.2 Licencias de software utilizadas](#92-licencias-de-software-utilizadas)
  - [9.3 Declaración de originalidad](#93-declaración-de-originalidad)
- [10. ANEXOS](#10-anexos)
  - [Anexo A: Manual de usuario](#anexo-a-manual-de-usuario)
  - [Anexo B: Evidencias de entrevistas](#anexo-b-evidencias-de-entrevistas)
  - [Anexo C: Capturas de pantalla del sistema](#anexo-c-capturas-de-pantalla-del-sistema)
  - [Anexo D: Diagramas adicionales](#anexo-d-diagramas-adicionales)
  - [Anexo E: Enlace al repositorio Git](#anexo-e-enlace-al-repositorio-git)
  - [Anexo F: Declaración de originalidad firmada](#anexo-f-declaración-de-originalidad-firmada)
  - [Anexo G: Evidencias de optimización de consultas](#anexo-g-evidencias-de-optimización-de-consultas)
  - [Anexo H: Evidencias de pruebas de seguridad web](#anexo-h-evidencias-de-pruebas-de-seguridad-web)

---

## 1. RESUMEN EJECUTIVO

La **Plataforma Láctea Comunitaria LAT Compacto** es una aplicación web con impacto social pensada para microempresas queseras familiares, asociaciones ganaderas y pequeños productores de Zurite, Anta y Cusco Provincia. Busca resolver problemas cotidianos del acopio matutino: cuadernos borrados por la lluvia o el suero, desacuerdos al calcular las liquidaciones quincenales, pérdida de leche con acidez y pantallas incómodas de usar con manos mojadas o guantes.

El sistema fue programado en **Laravel 13** (PHP 8.3+, validado localmente en PHP 8.5 y en contenedor Docker `php:8.4-apache`) con arquitectura MVC y capa de cuatro servicios (`LacteoService`, `TinaWorkflowService`, `InformePlantaService` y `ExcelExportService`), sobre base de datos MariaDB/MySQL y listo para ejecutarse en XAMPP o Render. Integra gobierno multi-planta con aislamiento estricto por planta, acopio por voz automático y modo PWA offline (`localStorage` + Service Worker), control sanitario a 74° con desvío a quesillos de 4 días y descarte trazable, monitoreo térmico de múltiples tinas con selección de entregas y recetas/cronómetro editables, liquidación quincenal estilo Excel en tres tablas separadas sin comisión (S/ 0.00) y descuento FIFO idempotente de adelantos, informes automáticos cada 24 horas, notificaciones segmentadas por rol, exportación nativa `.xlsx`, gráficas Chart.js, permisos granulares, interfaz trilingüe oficial (español, quechua cusqueño trivocálico e inglés) y **103 pruebas automatizadas en PHPUnit (1,114 aserciones)**. *(187 palabras)*

---

## 2. INTRODUCCIÓN

### 2.1 Contexto general

En la provincia de Anta (especialmente en el distrito de Zurite) y en comunidades ganaderas de Cusco Provincia como Markjo, Compone, Izcuchaca, Huayllacocha, Cachimayo y Chinchero, la producción de leche fresca sostiene la economía diaria de cientos de familias campesinas. Cada madrugada, entre las 06:00 y las 08:00 horas, la leche llega en porongos de aluminio hacia pequeñas queserías familiares administradas generalmente por padres e hijos.

En la mayoría de estos centros, el control diario todavía se lleva en cuadernos escolares. La lluvia, el frío, el lavado constante de envases y las salpicaduras de suero mojan y borran los apuntes de pesaje. Cuando termina la quincena y toca calcular cuánto corresponde a cada ganadero —restando los adelantos en efectivo o en moldes de queso que pidió durante los quince días—, aparecen diferencias que dañan la confianza entre productores y queseros.

Por otro lado, los programas comerciales o ERP de suscripción mensual cobran licencias o porcentajes por operación que una planta artesanal de 50 a 1,000 litros diarios no puede cubrir, y tampoco están adaptados al uso con una sola mano ni al idioma quechua (*Runasimi*). Para responder a este problema construimos **LAT Compacto** dentro del curso de Programación Web, aplicando los temas del semestre: diseño responsivo, patrón MVC con capa de servicios, optimización de consultas en Eloquent y seguridad web con roles granulares.

### 2.2 Objetivo general

Desarrollar e implementar la aplicación web comunitaria **LAT Compacto** en Laravel y MariaDB/MySQL para microempresas queseras y asociaciones ganaderas de Anta y Cusco Provincia, digitalizando el acopio de leche por voz y botonera táctil, el control sanitario sin desperdicio, el monitoreo térmico de múltiples tinas con receta estandarizada, el análisis visual mediante dashboards y la liquidación quincenal sin cobro de comisiones (S/ 0.00).

### 2.3 Objetivos específicos

- Diseñar e implementar una base de datos relacional normalizada (**9 migraciones** y **12 tablas activas**, más 1 tabla *legacy* de acceso QR) con claves foráneas e índices compuestos para operar varias plantas lácteas (con papelera de 30 días), múltiples tinas, productores, acopios trazables por lote, aplicaciones de adelantos, reportes Excel, notificaciones e informes de planta.
- Separar la lógica de negocio en cuatro servicios dedicados (`LacteoService`, `TinaWorkflowService`, `InformePlantaService` y `ExcelExportService`) para dosificar recetas configurables, controlar la curva térmica (72 °C a 36–40 °C), ejecutar liquidaciones quincenales sin consultas N+1 y con descuento FIFO idempotente, compilar informes cada 24 horas y exportar archivos Excel (`.xlsx`).
- Construir una interfaz web responsiva y accesible basada en `DESIGN.md`, con botones táctiles grandes (`>= 54 px` y `>= 64 px`), dictado por voz automático, cola offline PWA (`sw.js`), tablas estilo Excel (`lat-finanzas-grid.js`), gráficas en Chart.js y conmutador completo entre español, quechua cusqueño trivocálico e inglés.
- Implementar autenticación y control de acceso por cargo, rango y **19 permisos granulares**, gobierno supervisado para Administración Total, aislamiento estricto por planta, defensas OWASP Top 10 (incluyendo cabeceras HTTP de seguridad, limitación de intentos y protección anti-fórmulas Excel) y cumplimiento de la Ley N.° 29733 [1].
- Validar el sistema con **103 pruebas automatizadas en PHPUnit (1,114 aserciones)** distribuidas en pruebas unitarias, funcionales, de flujo y seguridad OWASP, junto con verificación visual responsiva en Playwright (`tests/e2e/visual-standards.cjs`) y despliegue listo para XAMPP y Docker/Render.

### 2.4 Alcance actual del proyecto

Al cierre del curso, el proyecto creció respecto a su propuesta inicial de una sola planta y 5 tablas. Tras revisar el código real del repositorio `https://github.com/KelmerOC/LAT`, la siguiente tabla resume con claridad qué está implementado y qué funciones quedaron como mejoras futuras:

| Componente / Funcionalidad | Estado actual | Evidencia real en el repositorio |
| :--- | :---: | :--- |
| Portal público honesto + anuncio QR de registro + Registro de nueva planta + Login híbrido | **Implementado** | `HomeController.php`, `AuthController.php`, `inicio.blade.php`, `public/js/lat-qr.js`, `login.blade.php`, `register.blade.php` |
| Gobierno multi-planta, papelera de 30 días, aislamiento de encargado y modo operativo de Admin | **Implementado** | `Planta.php` (`softDeletes`), `PanelController@alternarModoOperativo`, `routes/console.php` (`lat:purgar-plantas`) |
| Dashboard interactivo con 4 KPIs, filtros temporales, 3 gráficas Chart.js y notificaciones en vivo | **Implementado** | `LacteoService::obtenerDatosDashboard()`, `NotificacionPanel.php`, `public/js/lat-notificaciones.js`, `sections/dashboard.blade.php` |
| Módulo 1: Acopio por voz automático, botonera 54 px, edición/borrado protegido y cola offline PWA | **Implementado** | `PanelController@guardarAcopio/sincronizarAcopiosOffline/actualizarAcopio`, `public/sw.js`, `public/js/lat-offline.js`, `sections/acopio.blade.php` |
| Módulo 2: Triage sanitario (74°) con aprobar, desvío a quesillo 4 días, descarte trazable y bloqueo por lote | **Implementado** | `PanelController@evaluarCalidad`, `sections/calidad.blade.php`, `partials/modals.blade.php` |
| Módulo 3: Múltiples tinas, selección múltiple de acopios filtrables, recetas base y cronómetro editables | **Implementado** | `Tina.php`, `Lote.php`, `TinaWorkflowService.php`, `sections/tinas.blade.php` |
| Módulo 4: Finanzas estilo Excel en 3 tablas separadas, descuento FIFO de adelantos y bandeja Contador | **Implementado** | `LacteoService::liquidarQuincena()`, `AdelantoAplicacion.php`, `ReporteExcel.php`, `public/js/lat-finanzas-grid.js`, `sections/finanzas.blade.php` |
| Módulo 5: Estadísticas comparativas, informes de planta 24h (`.txt` Admin) y descargas `.xlsx` | **Implementado** | `InformePlantaService.php`, `ExcelExportService.php`, `sections/estadisticas.blade.php` |
| Módulo 6: Administración de plantas (papelera 30d) y usuarios con 19 permisos y 4 rangos | **Implementado** | `AdminController.php`, `User.php`, `CheckLatPermission.php`, `sections/admin.blade.php` |
| Internacionalización trilingüe oficial (ES / QU / EN: 838 claves JS + 179 claves PHP) | **Implementado** | `public/js/lat-i18n.js`, `lang/{es,qu,en}/lat.php`, `SetLocaleMiddleware.php`, `GET /idioma/{lang}` |
| Suites PHPUnit (**103 tests / 1,114 aserciones**) + E2E Playwright (**294/294 checks**) + Docker/Render y XAMPP | **Implementado** | `tests/Feature/*Test.php`, `tests/Unit/LacteoServiceTest.php`, `tests/e2e/visual-standards.cjs`, `Dockerfile` |
| Mapa de rutas con Leaflet/OSM, OTP por correo y Libro de Reclamaciones D.S. 011-2011-PCM | **Fuera de esta versión (trabajo futuro)** | Preparados en esquema los campos `latitud`/`longitud` (`proveedores`); se proyectan para una fase posterior. |

### 2.5 Beneficiarios directos e indirectos

| Tipo de beneficiario | Grupo identificado | Beneficio concreto con LAT Compacto |
| :--- | :--- | :--- |
| **Directos** | Microempresas queseras familiares y encargados de planta (Zurite, Anta y Cusco) | Reemplazan los cuadernos mojados por registros digitales ordenados, estandarizan la receta de cada tina y obtienen balances en Excel listos para su gestión. |
| **Directos** | Recolectores en ruta y operarios de planta (incluidos adultos mayores) | Capturan litros dictando al micrófono o tocando teclas grandes con una mano, sin necesidad de tener correo electrónico personal ni memorizar menús complejos. |
| **Directos** | Pequeños productores ganaderos (proveedores de 5 L a 150 L por entrega) | Reciben cuentas claras cada quincena sin comisión (S/ 0.00), el mismo precio por litro sin importar su volumen y aprovechamiento de leche ácida en quesillos en vez de rechazo. |
| **Indirectos** | Contadores externos, cajas rurales y entidades bancarias | Cuentan con reportes `.xlsx` ordenados con sumatorias automáticas (`SUM`), facilitando declaraciones ante SUNAT y respaldo crediticio para las familias. |
| **Indirectos** | Trabajadores de apoyo eventual en planta y consumidores locales | Los apoyos por jornal cobran sin entregar datos sensibles innecesarios (Ley N.° 29733), y los consumidores reciben quesos y derivados elaborados bajo control térmico y sanitario uniforme. |

### 2.6 Justificación del uso de tecnología web con impacto social

Optar por una **aplicación web responsiva** en lugar de una aplicación móvil nativa o un programa de escritorio tiene sentido práctico para la realidad rural cusqueña:

- **Sin barreras de dispositivo ni instalación:** Corre directamente desde el navegador de cualquier celular Android de gama básica o computadora en planta, sin depender de tiendas de aplicaciones ni pagar licencias de publicación.
- **Costo cero y doble despliegue:** Una quesería sin internet permanente puede instalarla en una laptop local con XAMPP (`README.md`), mientras que una asociación con varias plantas conectadas puede publicarla en la nube mediante el `Dockerfile` configurado para Render.
- **Inclusión real:** Al combinar HTML semántico, diseño de alto contraste, dictado por voz y traducción completa al quechua (*Runasimi*), la web acerca la gestión digital a operarios que suelen quedar excluidos de los sistemas empresariales tradicionales.

---

## 3. PROBLEMÁTICA SOCIAL Y JUSTIFICACIÓN

### 3.1 Descripción de la problemática

A partir del trabajo previo de levantamiento en el distrito de Zurite (provincia de Anta) y las comunidades lecheras de Cusco Provincia —complementado con datos sectoriales de la Dirección Regional de Agricultura de Cusco [2] y el INEI [3]—, se identificaron cinco problemas que frenan el crecimiento de las queserías artesanales:

1. **Deterioro de los cuadernos de pesaje:** En la cuenca de Cusco y Anta se producen más de 45,000 litros diarios de leche cruda [2], y alrededor del 78% de las queserías artesanales aún anota el acopio en cuadernos de papel. La lluvia de madrugada y las salpicaduras de suero borran cifras y provocan desacuerdos financieros al liquidar la quincena.
2. **Dificultad para usar interfaces convencionales en campo:** Los recolectores y encargados manipulan porongos metálicos con manos frías, mojadas o con guantes; los formularios pequeños de escritorio o celulares resultan lentos e incómodos.
3. **Pérdida de valor por acidificación prematura:** En temporadas cálidas o lluviosas, entre el 8% y el 12% de la leche llega con acidez detectable en la prueba de alcohol a 74°. Si no se clasifica y deriva rápido a quesillos frescos de consumo corto (4 días), se malogra toda una tina o se descarta la leche perjudicando al productor.
4. **Cálculos manuales de tina y desorden en adelantos:** Sin una referencia rápida de enfriamiento (de 72 °C a 36–40 °C) ni una calculadora exacta de cuajo, sal y cultivo según los litros reales del día, los quesos salen desparejos. A la vez, los adelantos en dinero o moldes de queso que piden los ganaderos se apuntan en hojas sueltas y se olvidan al cerrar la quincena.
5. **Informalidad en pagos eventuales y riesgos de privacidad:** Cerca del 92% del personal de apoyo temporal (lavado de porongos o volteo de quesos) cobra jornales en efectivo en mano. Pedir DNI y fichas extensas para estos pagos menores complica el registro y contradice el principio de proporcionalidad de la Ley N.° 29733 [1].

### 3.2 Evidencia de entrevistas (mínimo 3)

Durante la etapa de diagnóstico en campo se realizaron entrevistas semiestructuradas a actores de la cadena láctea de Zurite (Anta). De la versión anterior del informe (`docs/INFORME_PROYECTO_LAT_CUSCO.docx.md`) se rescataron los hallazgos de las dos entrevistas con identificación completa y los apuntes de una tercera entrevista de ruta/adelantos cuyo registro nominal y ficha firmada están pendientes de adjuntar en físico/imagen:

| N.° | Entrevistado | Rol / Lugar y fecha | Problema declarado en entrevista | Requerimiento derivado para LAT Compacto |
| :-: | :--- | :--- | :--- | :--- |
| 1 | **Don Eloy Condori** | Ganadero y recolector — Zurite, Anta (11/10/2026) | *"El cuaderno se moja y se borran los números. En la quincena hay dudas con el quesero."* | Registro digital de entregas con dictado por voz, confirmación auditiva y liquidación quincenal clara. |
| 2 | **Elvis (hijo)** | Productor y encargado de planta — Zurite, Anta (11/10/2026) | *"Al tanteo en vasito; si la leche cambia de cantidad, a veces el queso no cuaja parejo. Si la leche cambia, hay que saber si va a queso o a otro derivado."* | Calculadora automática de cuajo, sal y cultivo según litros en tina, más triage sanitario a 74° con desvío a quesillo de 4 días. |
| 3 | **Actor de ruta / gestión de planta** *(nombre completo y ficha firmada por adjuntar)* | Recolector en moto / apoyo de planta — Anta (octubre 2026) | *"Con manos mojadas y frío no se puede escribir en teclado; hablándole al celular es inmediato. Además, si los adelantos no se anotan bien, al final de la quincena se olvidan."* | Botonera táctil grande (`>= 54 px`) como respaldo del dictado por voz y módulo de adelantos (efectivo y queso) descontados automáticamente en la quincena. |

> **[INSERTAR EVIDENCIA VISUAL AQUÍ]**
> - **Qué mostrar:** Escaneos o fotografías legibles de las 3 fichas de entrevista firmadas (completando el nombre y consentimiento del tercer entrevistado). Las fotografías de campo del cuaderno físico de acopio y la tina quesera artesanal tomadas durante la visita a Zurite/Anta se incluyen en el **Anexo B**.
> - **Herramienta recomendada:** Cámara fotográfica o escáner de fichas físicas de entrevista (respetando la privacidad de los entrevistados).
> - **Archivo/Ruta de referencia en el repo:** `docs/INFORME_PROYECTO_LAT_CUSCO.docx.md` (secciones 2.6.2 y 12.1–12.2) y `Anexo B` de `docs/INFORME_FINAL_LAT.md`.

### 3.3 Justificación del impacto social

Frente a las opciones que hoy existen en el mercado, **LAT Compacto** se diseñó expresamente para no expulsar a las familias de menores recursos:

| Criterio de comparación | Cuaderno de papel | Hoja de Excel manual | ERP comercial SaaS | LAT Compacto (actual) |
| :--- | :--- | :--- | :--- | :--- |
| **Costo mensual / licencia** | Bajo costo inicial | Licencia ofimática | Alto (S/ 150 a S/ 450/mes) | **S/ 0.00** (código libre MIT) |
| **Uso con manos húmedas o guantes** | Nulo (el papel se rompe) | Difícil (requiere teclado) | Bajo (pantallas cargadas) | **Alto** (voz + teclas de 54 px) |
| **Idioma quechua (*Runasimi*)** | No aplica | No disponible | No disponible | **Trilingüe nativo** (ES / QU / EN) |
| **Control de tinas y recetas** | Al cálculo manual | Fórmulas frágiles | Módulos industriales caros | **Automático** por volumen y °C |
| **Liquidación y adelantos** | Suma manual con errores | Armado lento | Exige plan contable complejo | **Automático** (0% comisión) + `.xlsx` |

Además, el proyecto se alinea directamente con cinco **Objetivos de Desarrollo Sostenible (ODS)** de la Agenda 2030:
- **ODS 1 (Fin de la Pobreza) y ODS 2 (Hambre Cero):** Protege el ingreso de las familias ganaderas evitando pérdidas por apuntes borrados o descarte de leche.
- **ODS 8 (Trabajo Decente y Crecimiento Económico):** Ordena la gestión financiera de las microempresas familiares y facilita reportes Excel para formalización.
- **ODS 10 (Reducción de las Desigualdades):** Incorpora el idioma quechua, dictado vocal y trato igualitario sin sesgo entre productores pequeños y grandes.
- **ODS 12 (Producción y Consumo Responsables):** Promueve el principio de cero desperdicio al transformar la leche con acidez en quesillos frescos de 4 días.

### 3.4 Análisis del rol de la ingeniería en la solución

Desde la Ingeniería de Sistemas e Informática, resolver este caso significó tomar decisiones técnicas pensadas para las condiciones reales del campo y no simplemente adaptar una plantilla genérica:

- **Modelado relacional y rendimiento:** Se evolucionó a **12 tablas activas** (construidas con **9 migraciones**) e índices compuestos adicionales (`2026_11_25_153707`, `2026_11_26_184041`, `2026_11_26_210000`, `2026_11_26_221223`) para mantener el presupuesto en `<= 42` consultas SQL por carga de `/panel`.
- **Separación de responsabilidades (SRP):** La lógica lechera, la trazabilidad de lotes y recetas (`TinaWorkflowService`), la bitácora e informes cada 24h (`InformePlantaService`) y la generación `.xlsx` (`ExcelExportService`) residen en `app/Services/`, manteniendo ligeros los controladores.
- **Accesibilidad (WCAG 2.1 AA):** Se definieron colores con contraste comprobado (`#c2410c` sobre blanco con ratio `>= 5.2:1`, `#0a2f4d` para texto principal), navegación por teclado, etiquetas `<label for="...">` en todos los controles visibles, atributos `aria-*` y botón de pausa en el carrusel de portada (WCAG 2.2.2) [4].
- **Seguridad desde el diseño:** El acceso combina **19 permisos** y 4 rangos con aislamiento estricto por `planta_id`, bloqueo operativo por defecto para Administración Total (activable en sesión), `SecurityHeadersMiddleware` y saneamiento anti-fórmulas Excel.

---

## 4. MARCO ÉTICO Y RESPONSABILIDAD SOCIAL

### 4.1 Impacto de la solución tecnológica en la sociedad

Digitalizar el acopio y la liquidación cambia la dinámica diaria de la quesería comunitaria: cada entrega queda respaldada con fecha, hora, volumen, método de captura (voz o manual) y resultado sanitario. Esto reduce los malentendidos al final de la quincena y fortalece la confianza entre los comuneros y la planta. A la vez, el dashboard visual con gráficas de litros, ventas, gastos y moldes permite que los jóvenes de la familia colaboren en la administración del negocio con información clara en tiempo real.

### 4.2 Consideraciones éticas en el desarrollo

Los principios éticos definidos en `PRODUCT.md` y `DESIGN.md` están trasladados directamente al comportamiento del código:

| Principio ético | Cómo se cumple en el código de LAT Compacto |
| :--- | :--- |
| **Comisión S/ 0.00** | Configurado en `config/lat.php` (`LAT_COMISION=0.00`), en el valor por defecto de `liquidaciones.comision` y en `LacteoService::liquidarQuincena()`. |
| **Trato justo sin sesgo** | Cada planta aplica un mismo `precio_litro` a todos sus ganaderos, ya entreguen 5 L o 150 L. |
| **Cero desperdicio sanitario y descarte trazable** | Al clasificar un porongo como `acida` en `PanelController@evaluarCalidad`, el destino pasa automáticamente a `quesillo_4_dias`. Si la leche resulta no apta (`descartada`), se conserva el registro con `motivo_descarte` y `evaluado_por` excluyéndolo de la liquidación (`buena` + `acida`). |
| **Minimización de datos (Ley N.° 29733)** [1] | La tabla `proveedores` identifica a cada ganadero con código comunitario (`PRV-01`) sin solicitar DNI; la tabla separada de sueldos y `ayuda_del_dia` permite pagos eventuales con `proveedor_id = null` sin datos sensibles; el correo electrónico (`users.email`) es opcional para operarios. |
| **Veracidad y honestidad visual** | La portada (`inicio.blade.php`) no muestra contadores inventados ni testimonios ficticios, incluye un anuncio QR público honesto hacia `/registrarse` (`lat-qr.js`) y las fotografías reales del carrusel (`public/brand/hero_*.jpg`) llevan su nota visible. |
| **Inclusión cultural y lingüística** | El sistema ofrece **838 claves** en `public/js/lat-i18n.js` y **179 claves PHP** en `lang/{es,qu,en}/lat.php` en Español, Quechua cusqueño trivocálico oficial y Inglés, sincronizadas con `SetLocaleMiddleware` y modo PWA offline para zonas rurales (`sw.js`, `lat-offline.js`). |

### 4.3 Plan de sostenibilidad y transferencia a la comunidad

Para que LAT Compacto pueda mantenerse operativo al terminar el curso sin depender de pagos mensuales:

1. **Software 100% libre de regalías:** Tanto el backend (PHP/Laravel), la base de datos (MariaDB/MySQL) como las librerías de interfaz (Chart.js, fuentes abiertas Inter) usan licencias permisivas. Incluso la exportación a Excel (`ExcelExportService.php`) fue desarrollada en PHP puro con `zlib`, sin paquetes comerciales.
2. **Despliegue flexible según conectividad:** Las plantas sin internet estable pueden ejecutarlo en red local usando XAMPP (`http://localhost/lat-compacto/`), mientras que las asociaciones que requieran acceso remoto entre varias plantas ya cuentan con `Dockerfile`, `docker/000-default.conf` y `docker/start.sh` preparados para Render.
3. **Transferencia y capacitación:** Se contempla entregar el repositorio público en GitHub, el manual de usuario ilustrado (Anexo A) y sesiones prácticas con los responsables de planta de Anta para configurar sus proveedores, tinas y cuentas de operarios.

### 4.4 Políticas de respeto a la propiedad intelectual

- Todas las librerías declaradas en `composer.json` y `package.json` se utilizan respetando sus licencias originales (MIT, BSD-3-Clause, Apache-2.0 e ISC), listadas con su versión exacta en la sección 9.2.
- El diseño visual (`DESIGN.md`), las hojas CSS (`lat-design-system.css` y `lat-dashboard.css`), los logotipos SVG en `public/brand/`, los servicios (`LacteoService`, `ExcelExportService`) y las traducciones (`lat-i18n.js`) fueron elaborados por el equipo para este proyecto académico.
- Las fotografías del carrusel de portada provienen de Unsplash bajo licencia libre y se citan explícitamente como referenciales.

### 4.5 Acuerdos de trabajo y compromisos del equipo

El equipo estableció reglas claras de trabajo colaborativo: dividir responsabilidades por capa arquitectónica (Base de datos, Backend Core y Frontend/UX), documentar los cambios en GitHub con mensajes semánticos, revisar entre los tres integrantes el funcionamiento antes de cada avance y mantener honestidad técnica al diferenciar lo que ya está implementado de lo que queda como trabajo futuro.

---

## 5. DISEÑO Y ARQUITECTURA DE LA SOLUCIÓN

### 5.1 Framework seleccionado y justificación

Para el desarrollo se eligió **Laravel 13** (`laravel/framework v13.39.0` sobre **PHP 8.3+**, ejecutado también en imagen `php:8.4-apache` en Docker) [7], [8], [11] junto con **Blade**, **Eloquent ORM**, **MariaDB/MySQL** y **JavaScript Vanilla + Chart.js 4.4.7** [10]. Las razones prácticas de esta elección son:

| Criterio técnico | Justificación con Laravel 13 + Stack elegido |
| :--- | :--- |
| **Productividad y orden MVC** | Separa con claridad rutas (`routes/web.php`), middlewares, controladores, modelos, vistas Blade y servicios, reduciendo código repetitivo [7]. |
| **Seguridad integrada por defecto** | Incluye consultas preparadas PDO vía Eloquent, escape automático en Blade (`{{ }}`), protección `@csrf`, hashing Bcrypt y manejo seguro de sesiones [7], [8]. |
| **Bajo peso en redes rurales** | Al generar el HTML en el servidor con Blade y usar JS nativo sin frameworks SPA pesados, las pantallas cargan rápido en celulares económicos y conexiones móviles lentas. |
| **Compatibilidad local y nube** | Funciona sin complicaciones en XAMPP local y también dentro de un contenedor Docker (`Dockerfile`) para despliegue en la nube [11]. |
| **Pruebas automatizadas nativas** | Se integra directamente con **PHPUnit 12** y SQLite en memoria (`:memory:`), permitiendo validar permisos, rutas y reglas de negocio en pocos segundos. |

### 5.2 Arquitectura MVC

El sistema aplica una arquitectura **MVC apoyada en Capa de Servicios y Middlewares de Autorización e Idioma**:

- **Cliente / Navegador:** Renderiza las vistas Blade con estilos Swiss-Grid (`lat-design-system.css` y `lat-dashboard.css`), ejecuta `lat-i18n.js` (**838 claves** ES/QU/EN), `lat-offline.js` + `sw.js` (`lat-compacto-v8`, sincronización offline por bloques de hasta 50 entregas), `lat-notificaciones.js` (polling cada 12 s), `lat-finanzas-grid.js` (tablas estilo Excel con barra `fx`), `lat-qr.js` (QR público en `/`), `Chart.js` [10] (4 gráficas en `/panel`) y la **Web Speech API** [9] con envío automático al dictar productor y litros válidos.
- **Enrutamiento y Middlewares (`bootstrap/app.php`, `routes/web.php`):** Cada petición pasa por `SecurityHeadersMiddleware` (CSP, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Permissions-Policy`), `SetLocaleMiddleware`, protección CSRF, `auth` en rutas `/panel/*`, `throttle:5,1` en autenticación/registro y `CheckLatPermission` (`permiso:*`).
- **Controladores (`app/Http/Controllers/`):** Reciben la petición HTTP (`HomeController`, `AuthController`, `PanelController`, `AdminController`), validan datos, verifican el modo operativo de `administracion` o el aislamiento de `encargado_planta` y delegan la lógica a los servicios.
- **Capa de Servicios (`app/Services/`):** Cuatro servicios especializados: `LacteoService` (cálculo de insumos, curva térmica, liquidación FIFO sin N+1, estadísticas y dashboard), `TinaWorkflowService` (asignación atómica de acopios a tinas, recetas configurables, cronómetro y notificaciones segmentadas), `InformePlantaService` (bitácora operativa e informes consolidados 24h) y `ExcelExportService` (libros OOXML `.xlsx` completo y por tabla con saneamiento anti-fórmulas).
- **Modelos y Persistencia (`app/Models/`, MariaDB/MySQL):** **12 modelos activos** (`Planta`, `User`, `Proveedor`, `Tina`, `Acopio`, `Lote`, `Liquidacion`, `AdelantoAplicacion`, `ReporteExcel`, `NotificacionPanel`, `ActividadPlanta`, `InformeActividadPlanta`, además de `QrAcceso` *legacy*) con relaciones, casts y *scopes* reutilizables (`dePlanta`, `activos`, `delDia`, `delPeriodo`, `liquidables`, `enProceso`).

```mermaid
flowchart TB
    subgraph Cliente["Navegador Web (PC / Smartphone)"]
        UI["Vistas Blade + CSS Swiss-Grid"]
        I18N["lat-i18n.js (838 claves ES/QU/EN)"]
        JS["Chart.js v4.4.7 + Web Speech API + PWA sw.js + Finanzas Grid"]
    end

    subgraph Middleware["Pipeline HTTP Laravel 13"]
        MW1["SecurityHeadersMiddleware + SetLocaleMiddleware + VerifyCsrfToken"]
        MW2["Middleware auth + throttle:5,1 + CheckLatPermission (permiso:*)"]
    end

    subgraph Controladores["Capa de Controladores (app/Http/Controllers)"]
        C1["HomeController (/)"]
        C2["AuthController (/login, /registrarse, /idioma)"]
        C3["PanelController (/panel/*)"]
        C4["AdminController (/panel/admin/*)"]
    end

    subgraph Servicios["Capa de Servicios (app/Services)"]
        S1["LacteoService + TinaWorkflowService"]
        S2["InformePlantaService + ExcelExportService (.xlsx)"]
    end

    subgraph Datos["Capa de Modelo y Persistencia"]
        M["12 Modelos activos Eloquent (Planta..InformeActividadPlanta)"]
        DB[("MariaDB / MySQL (lat_compacto_db) / SQLite testing")]
    end

    UI --> MW1
    I18N --> UI
    JS --> UI
    MW1 --> MW2
    MW2 --> C1
    MW2 --> C2
    MW2 --> C3
    MW2 --> C4
    C3 --> S1
    C3 --> S2
    C2 --> M
    C3 --> M
    C4 --> M
    S1 --> M
    M --> DB
```

![Figura 1. Arquitectura MVC con capa de servicios y middlewares de LAT Compacto](imagenes/diagramas/01_arquitectura_mvc.png)

### 5.3 Modelo de datos

La base de datos `lat_compacto_db` se construye mediante **9 migraciones versionadas** en `database/migrations/`:
1. `2026_01_01_000001_crear_tablas_lat_compacto.php` (esquema base de 5 tablas e índices compuestos iniciales).
2. `2026_01_01_000002_agregar_plantas_tinas_y_ventas.php` (evolución multi-planta, tabla `tinas`, relaciones `planta_id`/`tina_id` y ventas de productos).
3. `2026_11_18_205518_agregar_permisos_personalizados_usuarios.php` (soporte para cargo `personalizado`, columna `rango`, JSON `permisos` y auditoría `creado_por`).
4. `2026_11_25_153704_agregar_papelera_plantas.php` (`softDeletes`, `eliminado_por` e índice `idx_plantas_deleted_at` para papelera de 30 días en `plantas`).
5. `2026_11_25_153706_crear_reportes_excel_y_qr_accesos.php` (tablas `reportes_excel` para intercambio con el contador y `qr_accesos` *legacy*).
6. `2026_11_25_153707_optimizar_indices_lat_compacto.php` (índices compuestos adicionales en `liquidaciones`, `acopios`, `lotes`, `users` y `UNIQUE (planta_id, codigo)` en `proveedores`).
7. `2026_11_26_184041_actualizar_flujo_tinas_recetas_y_notificaciones.php` (`acopios.lote_id`, `evaluado_en`, `tinas.tipo` `VARCHAR(80)`, `lotes` con receta/cronómetro, `plantas.recetas_base` y tabla `notificaciones_panel`).
8. `2026_11_26_210000_actualizar_roles_finanzas_notificaciones_e_informes.php` (segmentación y `color_tema` en `notificaciones_panel`, columnas de ventas/sueldos en `liquidaciones`, tablas `actividades_planta` e `informes_actividad_planta` y limpieza de `admin.qr`).
9. `2026_11_26_221223_actualizar_adelantos_aplicaciones_y_acopios_descartados.php` (tabla `adelanto_aplicaciones` para descuento FIFO idempotente y campos `motivo_descarte`/`evaluado_por` en `acopios`).

#### Diagrama Entidad-Relación (Mermaid `erDiagram`)

```mermaid
erDiagram
    PLANTAS ||--o{ USERS : "asigna personal"
    PLANTAS ||--o{ PROVEEDORES : "agrupa ganaderos"
    PLANTAS ||--o{ TINAS : "opera tinas"
    PLANTAS ||--o{ ACOPIOS : "registra entregas"
    PLANTAS ||--o{ LOTES : "procesa lotes"
    PLANTAS ||--o{ LIQUIDACIONES : "controla finanzas"
    USERS ||--o{ USERS : "creado_por"
    USERS ||--o{ ACOPIOS : "registrado_por"
    PROVEEDORES ||--o{ ACOPIOS : "entrega leche"
    PROVEEDORES ||--o{ LIQUIDACIONES : "recibe adelanto o pago"
    TINAS ||--o{ LOTES : "ejecuta ciclo termico"

    PLANTAS {
        bigint id PK
        varchar codigo UK
        varchar nombre
        varchar ubicacion
        decimal precio_litro
        boolean activa
        timestamp created_at
        timestamp updated_at
    }

    USERS {
        bigint id PK
        bigint planta_id FK
        varchar name
        varchar usuario UK
        varchar email UK
        varchar password
        enum cargo
        varchar rango
        json permisos
        boolean activo
        bigint creado_por FK
        varchar remember_token
        timestamp created_at
        timestamp updated_at
    }

    PROVEEDORES {
        bigint id PK
        bigint planta_id FK
        varchar codigo UK
        varchar nombre
        varchar sector
        decimal latitud
        decimal longitud
        boolean activo
        timestamp created_at
        timestamp updated_at
    }

    TINAS {
        bigint id PK
        bigint planta_id FK
        varchar codigo UK
        varchar nombre
        decimal capacidad_litros
        enum tipo
        boolean activa
        timestamp created_at
        timestamp updated_at
    }

    ACOPIOS {
        bigint id PK
        bigint planta_id FK
        bigint proveedor_id FK
        bigint registrado_por FK
        uuid uuid_offline UK
        date fecha
        time hora
        decimal litros
        decimal precio_litro
        enum metodo
        varchar frase_dictada
        enum calidad
        enum destino
        timestamp created_at
        timestamp updated_at
    }

    LOTES {
        bigint id PK
        bigint planta_id FK
        bigint tina_id FK
        varchar codigo UK
        date fecha
        decimal litros
        decimal temperatura_actual
        decimal cuajo_ml
        decimal sal_kg
        decimal cultivo_g
        int moldes_producidos
        enum estado
        timestamp created_at
        timestamp updated_at
    }

    LIQUIDACIONES {
        bigint id PK
        bigint planta_id FK
        bigint proveedor_id FK
        date fecha
        enum tipo
        varchar concepto
        decimal litros
        int cantidad_unidades
        decimal monto
        decimal comision
        boolean descontado
        timestamp created_at
        timestamp updated_at
    }
```

![Figura 2. Diagrama Entidad-Relación base de las 7 tablas iniciales de lat_compacto_db](imagenes/diagramas/02_modelo_er.png)

#### Diccionario de datos consolidado (7 tablas núcleo + extensiones de cierre)

| Tabla | Campo | Tipo y longitud | Restricciones / Claves | Descripción funcional |
| :--- | :--- | :--- | :--- | :--- |
| **`plantas`** | `id` | `BIGINT UNSIGNED` | `PK`, `AUTO_INCREMENT` | Identificador único de la planta láctea. |
| | `codigo` | `VARCHAR(20)` | `UNIQUE`, `NOT NULL` | Código identificador (ej. `PLT-01`). |
| | `nombre` | `VARCHAR(140)` | `NOT NULL` | Razón social o nombre comunitario de la planta. |
| | `ubicacion` | `VARCHAR(140)` | `DEFAULT 'Cusco Provincia'` | Sector, distrito o provincia de operación. |
| | `precio_litro` | `DECIMAL(6,2)` | `DEFAULT 1.65` | Precio base vigente por litro de leche (S/). |
| | `activa` | `BOOLEAN` | `INDEX (idx_plantas_activa)` | Indica si la planta se encuentra operativa. |
| **`users`** | `id` | `BIGINT UNSIGNED` | `PK`, `AUTO_INCREMENT` | Identificador único del usuario. |
| | `planta_id` | `BIGINT UNSIGNED` | `FK -> plantas.id` (`NULL ON DELETE`) | Planta a la que pertenece el operador. |
| | `name` | `VARCHAR(120)` | `NOT NULL` | Nombre completo del trabajador o responsable. |
| | `usuario` | `VARCHAR(60)` | `UNIQUE`, `NOT NULL` | Credencial simple de acceso (sin exigir correo). |
| | `email` | `VARCHAR(191)` | `NULLABLE`, `UNIQUE` | Correo electrónico opcional (vía `Schema::defaultStringLength(191)`; validación `max:255`). |
| | `password` | `VARCHAR(255)` | `NOT NULL` | Contraseña cifrada con Bcrypt (`hashed`). |
| | `cargo` | `ENUM` | 6 valores (`administracion` ... `personalizado`) | Plantilla de rol operativo asignada. |
| | `rango` | `VARCHAR(40)` | `DEFAULT 'operador'` | Nivel jerárquico (`administrador`, `supervisor`, `operador`, `personalizado`). |
| | `permisos` | `JSON` | `NULLABLE` | Arreglo de claves granulares activas (hasta 18 controles). |
| | `activo` | `BOOLEAN` | `DEFAULT true` | Habilita o revoca el inicio de sesión. |
| | `creado_por` | `BIGINT UNSIGNED` | `FK -> users.id` (`NULL ON DELETE`) | Auditoría de quién creó la cuenta. |
| **`proveedores`** | `id` | `BIGINT UNSIGNED` | `PK`, `AUTO_INCREMENT` | Identificador único del productor ganadero. |
| | `planta_id` | `BIGINT UNSIGNED` | `FK -> plantas.id` (`CASCADE`) | Planta a la que entrega leche. |
| | `codigo` | `VARCHAR(20)` | `UNIQUE`, `NOT NULL` | Código comunitario (ej. `PRV-01`, sin requerir DNI). |
| | `nombre` | `VARCHAR(120)` | `NOT NULL` | Nombre del productor ganadero. |
| | `sector` | `VARCHAR(120)` | `NULLABLE` | Comunidad o anexo (ej. Markjo, Compone, Zurite). |
| | `latitud` / `longitud` | `DECIMAL(10,7)` | `NULLABLE` | Coordenadas geográficas preparadas para ruteo futuro. |
| | `activo` | `BOOLEAN` | `INDEX` simple y compuesto con `planta_id` | Filtra ganaderos vigentes en la ruta. |
| **`tinas`** | `id` | `BIGINT UNSIGNED` | `PK`, `AUTO_INCREMENT` | Identificador único de la tina. |
| | `planta_id` | `BIGINT UNSIGNED` | `FK -> plantas.id` (`CASCADE`) | Planta propietaria de la tina. |
| | `codigo` | `VARCHAR(30)` | `UNIQUE (planta_id, codigo)` | Código interno de tina (ej. `TINA-01`). |
| | `nombre` | `VARCHAR(100)` | `NOT NULL` | Nombre descriptivo (ej. *Tina Quesillo Fresco 4 Días*). |
| | `capacidad_litros` | `DECIMAL(8,2)` | `DEFAULT 500.00` | Capacidad máxima en litros (validación en carga de lotes). |
| | `tipo` | `ENUM` | `principal_queso`, `quesillo`, `yogur`, `multiproposito` | Línea de transformación asignada. |
| | `activa` | `BOOLEAN` | `INDEX (planta_id, activa)` | Estado operativo o en mantenimiento. |
| **`acopios`** | `id` | `BIGINT UNSIGNED` | `PK`, `AUTO_INCREMENT` | Identificador único de la entrega diaria. |
| | `planta_id` | `BIGINT UNSIGNED` | `FK -> plantas.id` (`CASCADE`) | Planta receptora (`idx_acopios_planta_fecha`). |
| | `proveedor_id` | `BIGINT UNSIGNED` | `FK -> proveedores.id` (`CASCADE`) | Ganadero que entregó la leche (`idx_acopios_proveedor_fecha`). |
| | `registrado_por` | `BIGINT UNSIGNED` | `FK -> users.id` (`NULL ON DELETE`) | Operario que registró el pesaje. |
| | `uuid_offline` | `CHAR(36)` | `UNIQUE`, `NOT NULL` | UUID v4 idempotente por entrega. |
| | `fecha` / `hora` | `DATE` / `TIME` | `NOT NULL` | Día y hora exacta de recepción (`idx_acopios_fecha_calidad`). |
| | `litros` / `precio_litro` | `DECIMAL(8,2)` / `(6,2)` | `NOT NULL` | Volumen entregado y precio pactado en esa fecha. |
| | `metodo` | `ENUM` | `voz`, `manual` | Medio de captura utilizado. |
| | `frase_dictada` | `VARCHAR(255)` | `NULLABLE` | Transcripción capturada por Web Speech API. |
| | `calidad` | `ENUM` | `pendiente` (default BD), `buena`, `acida` | Resultado de la prueba de alcohol a 74° (`guardarAcopio` registra inicialmente `buena`). |
| | `destino` | `ENUM` | `pendiente` (default BD), `queso_yogur`, `quesillo_4_dias` | Ruta productiva (`queso_yogur` inicial; cambia a `quesillo_4_dias` si es ácida). |
| **`lotes`** | `id` | `BIGINT UNSIGNED` | `PK`, `AUTO_INCREMENT` | Identificador único del lote térmico. |
| | `planta_id` / `tina_id` | `BIGINT UNSIGNED` | `FK -> plantas.id`, `tinas.id` | Planta y tina donde se procesa el lote. |
| | `codigo` | `VARCHAR(40)` | `UNIQUE`, `NOT NULL` | Código de lote (ej. `LOTE-TINA-01-20261118-143000`). |
| | `fecha` / `litros` | `DATE` / `DECIMAL(8,2)` | `NOT NULL` | Fecha de proceso y volumen cargado en tina. |
| | `temperatura_actual` | `DECIMAL(5,1)` | `DEFAULT 72.0` | Lectura térmica actual del lote (°C). |
| | `cuajo_ml`, `sal_kg`, `cultivo_g` | `DECIMAL(8,2)` | `NOT NULL` | Insumos calculados automáticamente por `LacteoService`. |
| | `moldes_producidos` | `INT UNSIGNED` | `DEFAULT 0` | Unidades reales obtenidas al finalizar el lote. |
| | `estado` | `ENUM` | `pasteurizando`, `enfriando`, `lista_para_cuajo`, `terminado` | Fase actual del ciclo (`idx_lotes_fecha_estado`, `idx_lotes_planta_estado`). |
| **`liquidaciones`** | `id` | `BIGINT UNSIGNED` | `PK`, `AUTO_INCREMENT` | Identificador único del movimiento financiero. |
| | `planta_id` | `BIGINT UNSIGNED` | `FK -> plantas.id` (`CASCADE`) | Planta a la que corresponde el asiento. |
| | `proveedor_id` | `BIGINT UNSIGNED` | `FK -> proveedores.id` (`NULL ON DELETE`) | Ganadero asociado (`NULL` en ventas o *Ayuda del Día*). |
| | `fecha` / `tipo` | `DATE` / `ENUM` | 6 tipos (`quincena`, `adelanto_efectivo`, `adelanto_queso`, `ayuda_del_dia`, `gasto_operativo`, `venta_producto`) | Clasificación contable del movimiento. |
| | `concepto` | `VARCHAR(160)` | `NOT NULL` | Descripción del ingreso, gasto o adelanto. |
| | `litros` / `cantidad_unidades` | `DECIMAL(9,2)` / `INT` | `DEFAULT 0` | Litros liquidados o unidades/moldes vendidos o entregados. |
| | `monto` / `comision` | `DECIMAL(10,2)` / `(6,2)` | `comision DEFAULT 0.00` | Importe en soles y garantía de comisión S/ 0.00. |
| | `descontado` | `BOOLEAN` | `INDEX (proveedor_id, descontado)` | Marca si un adelanto ya fue descontado en quincena. |

*(Todas las tablas incluyen además marcas de auditoría `created_at` y `updated_at`. En las últimas migraciones se añadieron `softDeletes` y `recetas_base` en `plantas`; `lote_id`, `evaluado_en`, `motivo_descarte`, `evaluado_por` y `calidad/destino` ampliados a `'descartada'` en `acopios`; `tinas.tipo` como `VARCHAR(80)`; `receta_nombre`, `duracion_minutos`, `iniciado_en` y `alerta_fin_enviada` en `lotes`; `producto_categoria`, `canal_referencia`, `precio_unitario` y `estado_pago` en `liquidaciones`; y las cinco tablas operativas nuevas: `adelanto_aplicaciones`, `reportes_excel`, `notificaciones_panel`, `actividades_planta` e `informes_actividad_planta`, además de `qr_accesos` en desuso tras retirar el QR interno.)*

### 5.4 Diseño de interfaz

El diseño visual se documenta formalmente en `DESIGN.md` y se implementa en `public/css/lat-design-system.css` (portal público y tokens base) y `public/css/lat-dashboard.css` (panel operativo modular con barra lateral). Su identidad se denomina **Editorial Swiss-Grid y Acuarela Andina**:

- **Paleta cromática y contraste WCAG 2.1 AA:** Fondo blanco papel (`#ffffff`), niebla andina (`#f2f7fb`), azul marino profundo para texto (`#0a2f4d`), azul celeste (`#0284c7`), azul oceánico (`#0d4068`) y naranja ember accesible (`#c2410c` sobre blanco con contraste `>= 5.2:1`; `#ff5900` reservado para acentos y nodos).
- **Geometría limpia y ergonómica:** Líneas divisorias expuestas de `1px` (*hairlines*), bordes rectos de `0px` a `2px` en botones y tablas, tarjetas multimedia con radio de `8px`, tipografía `Inter` variable con cifras tabulares ligeras (`font-weight: 300`, `tabular-nums`), botonera táctil de `>= 54 px` y botón de dictado de `>= 64 px`.
- **Dashboard operativo moderno:** Estructura `.dash-shell` con sidebar lateral fijo de `268 px` (menú *off-canvas* en `<= 992 px`) donde reside de forma única el conmutador trilingüe `.dash-lang-row` (`ES | QU | EN`), topbar con selector de rango, selector multi-planta, campana de notificaciones segmentadas (con sombreado verde `.is-color-verde` para avisos contables), botón `#btn-toggle-modo-operativo` para Administración Total y exportación `.xlsx`. En la portada pública (`/`), el anuncio QR de registro (`#anuncio-qr-registro`, `lat-qr.js`) y las fotografías reales (`public/brand/hero_*.jpg`) completan la experiencia inicial.

#### Wireframes y mockups ya generados en el repositorio (`docs/mockups/`)

Mediante los scripts reproducibles `scripts/generate_wireframes.cjs` y `scripts/generate_mockups.cjs` (con Playwright), el equipo cuenta con **10 archivos gráficos** versionados en la carpeta `docs/mockups/`:

| N.° | Pantalla representada | Wireframe esquemático (B/N) | Mockup de alta fidelidad (monocromo) |
| :-: | :--- | :--- | :--- |
| 1 | Portada pública Swiss-Grid y barra trilingüe | `docs/mockups/mockup_1_portada_wireframe.png` | `docs/mockups/mockup_1_portada_desktop_bw.png` |
| 2 | Panel operativo — Módulo 1: Acopio por voz y botonera | `docs/mockups/mockup_2_panel_acopio_wireframe.png` | `docs/mockups/mockup_2_panel_acopio_desktop_bw.png` |
| 3 | Panel operativo — Módulo 2: Control sanitario 74° | `docs/mockups/mockup_3_control_sanitario_wireframe.png` | `docs/mockups/mockup_3_control_sanitario_desktop_bw.png` |
| 4 | Panel operativo — Módulo 3: Monitoreo térmico y receta | `docs/mockups/mockup_4_tina_receta_wireframe.png` | `docs/mockups/mockup_4_tina_receta_desktop_bw.png` |
| 5 | Vista móvil smartphone (390 × 844 px) y menú táctil | `docs/mockups/mockup_5_movil_wireframe.png` | `docs/mockups/mockup_5_movil_smartphone_bw.png` |

> **[INSERTAR EVIDENCIA VISUAL AQUÍ]**
> - **Qué mostrar:** Composición visual con los mockups y wireframes de la carpeta `docs/mockups/` junto con capturas a color actuales del nuevo Dashboard con sidebar lateral, gráficas Chart.js y matriz de usuarios personalizados.
> - **Herramienta recomendada:** Navegador Chrome/Edge + DevTools (`F12`, vistas de escritorio `1280×800` y móvil `390×844`) y archivos PNG de `docs/mockups/`.
> - **Archivo/Ruta de referencia en el repo:** `docs/mockups/*.png`, `DESIGN.md`, `resources/views/panel.blade.php` y `resources/views/panel/sections/dashboard.blade.php`.

### 5.5 Diseño de seguridad web

El diseño de seguridad de LAT Compacto se estructuró tomando como referencia la guía **OWASP Top 10 (2021)** [5] para prevenir los riesgos más frecuentes en aplicaciones web multi-usuario:

- **Amenazas identificadas:** Control de acceso roto o escalamiento entre plantas/rangos (A01), exposición de credenciales o sesiones inseguras (A02/A07), inyección SQL y Cross-Site Scripting —XSS— (A03), falsificación de peticiones entre sitios —CSRF— (A01/A05) y errores de configuración del servidor o dependencias desactualizadas (A05/A06).
- **Controles previstos en el diseño:**
  - *Autenticación y gestión de sesiones:* Inicio de sesión híbrido (correo o usuario) verificando `activo = true`, contraseñas con hash Bcrypt [8], regeneración del ID de sesión tras autenticarse, cierre con invalidación completa y cookies `HttpOnly` + `SameSite=Lax`.
  - *Autorización por roles, rangos y permisos:* Middleware `CheckLatPermission` (`permiso:*`) en cada ruta operativa protegida, ocultamiento condicional de controles en Blade, filtrado estricto por `planta_id`, bloqueo operativo por defecto de M1–M3 en `administracion` (conmutable en sesión), aislamiento estricto de `encargado_planta` y validación jerárquica para impedir escalamiento de privilegios.
  - *Prevención de SQL Injection:* Uso obligatorio de Eloquent ORM y *Query Builder* con consultas parametrizadas PDO [7], [8], más validación estricta con `Rule::exists(...)->where('planta_id', ...)` en identificadores externos.
  - *Prevención de XSS e inyección en Excel:* Escape automático de Blade (`{{ }}`), escritura DOM mediante `.textContent` en `lat-i18n.js`, saneamiento XML (`ENT_XML1 | ENT_QUOTES`) y neutralización de prefijos de fórmula (`=`, `+`, `-`, `@`) en `ExcelExportService::sanitizeFormulaInjection()`.
  - *Integridad, cabeceras y configuración segura:* Tokens `@csrf`, cabeceras HTTP defensivas vía `SecurityHeadersMiddleware` (CSP, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Permissions-Policy`), limitación `throttle:5,1` en registro/login, whitelists `$fillable`, bloqueo de listado de directorios (`Options -Indexes`) y HTTPS en producción.

> **Nota de trazabilidad:** La implementación detallada de cada control, las mejoras cerradas y las evidencias de pruebas de seguridad se desarrollan de forma explícita en la **sección 7.8 (Seguridad web implementada y evidencias)** y en el **Anexo H**.

### 5.6 Escalabilidad y sostenibilidad

- **Escalabilidad de datos:** El aislamiento lógico por `planta_id` y los índices compuestos ampliados (`2026_11_25_153707` y siguientes) mantienen consultas ágiles conforme crecen las plantas, tinas y entregas diarias.
- **Escalabilidad funcional:** Los **19 permisos** en formato JSON (`PERMISOS_CATALOGO`), las recetas base por planta en JSON (`recetas_base`) y los cuatro servicios especializados permiten sumar módulos sin reescribir el núcleo.
- **Sostenibilidad operativa:** El despliegue en contenedor (`Dockerfile` + `docker/start.sh` con `config:cache`, `route:cache`, `view:cache` y `opcache`) [11], las tareas programadas diarias (`lat:purgar-plantas`, `lat:generar-informes-planta`) y el soporte PWA offline conviven con el uso local en XAMPP a costo S/ 0.00.

---

## 6. INFORME DE FUNCIONALIDAD DE LA PÁGINA WEB

### 6.1 Módulos del sistema

La aplicación se divide en una **Zona Pública** (portada, login, registro y cambio de idioma) y una **Zona Privada (`/panel`)** organizada en el **Dashboard general** más **6 módulos operativos**:

| Módulo | Ruta / Sección en UI | Propósito y funciones principales |
| :--- | :--- | :--- |
| **Portal Público y Registro** | `/`, `/login`, `/registrarse`, `/idioma/{lang}` | Presenta la propuesta de valor sin métricas falsas, fotos reales con pausa WCAG, anuncio QR público hacia `/registrarse` (`lat-qr.js`), selector trilingüe, acceso con 6 perfiles demo y alta pública de nueva planta con su `TINA-01`/`VAT-01` inicial. |
| **Dashboard General** | `/panel` (`#sec-dashboard`) | Muestra 4 KPIs con variación temporal (`hoy`, `7d`, `quincena`, `mes`), 3 gráficas Chart.js [10], ocupación de tinas, ranking multi-planta, notificaciones en vivo segmentadas por rol y el interruptor operativo de sesión para Administración Total. |
| **M1: Acopio por voz y tacto** | `#sec-acopio` (`/panel/acopio`, `/panel/acopio/sync`, `/panel/proveedor`) | Registra entregas en estado inicial `pendiente` mediante dictado automático (`Web Speech API`) o botonera táctil de `54 px` (`Registrar entrega`). Opera offline vía `lat-offline.js` (bloques `<= 50`) e impide editar o borrar entregas ya asignadas a un lote (`lote_id !== null`). |
| **M2: Calidad sanitaria (74°)** | `#sec-calidad` (`/panel/calidad/{id}`) | Incorpora filtros independientes por fecha/estado para ubicar pendientes de días previos y 3 decisiones con confirmación coloreada (`.is-verde`, `.is-amarillo`, `.is-rojo`): *Aprobar*, *Desviar a quesillo* (4 días) o *Descartar* (conserva `motivo_descarte` y `evaluado_por` fuera de liquidación). Emite notificación hacia Tinas. |
| **M3: Múltiples tinas y receta** | `#sec-tina` (`/panel/tinas*`, `/panel/tinas/recetas-base`, `/panel/tinas/{tinaId}/lote`, `/panel/tina/{id}`) | Gestiona tinas (`tinas.tipo` libre con sugerencias, prefijo `TINA-`/`VAT-`), selección múltiple de acopios filtrables al iniciar lote (`#modal-iniciar-tina`), recetas base por planta y cronómetro editables, guardado independiente de temperatura (`step="0.1"`) y cierre con moldes reales. |
| **M4: Liquidación y ventas** | `#sec-quincena` (`/panel/finanzas/liquidacion-grid`, `/panel/movimiento*`, `/panel/reportes-excel*`) | Interfaz estilo Excel (`lat-finanzas-grid.js`) en **3 tablas separadas** con barra `fx`, filtros propios y descargas `.xlsx` exclusivas (`?solo=liquidacion|ventas|sueldos`): Liquidación editable inline con descuento FIFO idempotente (`adelanto_aplicaciones`, comisión S/ 0.00), Ventas y Sueldos/Jornales (sin DNI). Incluye bandeja Excel ↔ Contador con avisos verdes. |
| **M5: Estadísticas, Excel e Informes 24h** | `#sec-estadisticas` (`/panel/exportar-excel`, `/panel/informes-planta/enviar`, `/panel/estadisticas/informes/{id}/descargar`) | Presenta ranking comparativo (*"¿Quién vende más?"*), dona sanitaria, top 6 ganaderos, proporción voz vs manual, libro `.xlsx` de 4 hojas y bitácora de informes de actividad por planta (automático 24h y manual) con historial y descarga `.txt` exclusiva para Administración Total. |
| **M6: Plantas y usuarios** | `#sec-admin` (`/panel/admin/*`) | Gestiona plantas lácteas con papelera de 30 días (restauración y purga definitiva) y cuentas con cargo, rango y **19 permisos granulares**, manteniendo el aislamiento estricto de `encargado_planta` dentro de su planta. |

### 6.2 Flujo de navegación

La navegación separa claramente el acceso público del entorno operativo. Al iniciar sesión, `PanelController@index` calcula automáticamente `primeraSeccionPermitida()` para llevar al usuario directamente a la primera sección que su perfil puede ver (por ejemplo, un recolector entra directo a *1. Acopio* y no ve menús ni datos financieros). La sección activa se recuerda en `sessionStorage` por ID de usuario.

```mermaid
flowchart TD
    Inicio["/ (Portada Publica - Selector ES / QU / EN)"] --> Login["/login (Iniciar Sesion)"]
    Inicio --> Registro["/registrarse (Nueva Planta + Admin)"]
    Login -->|Credenciales validas| Panel["/panel (Mi Panel Operativo)"]
    Registro -->|DB::transaction: Planta + TINA-01 + Admin| Panel
    Login -->|Error o inactivo| Login

    Panel --> Topbar["Barra Superior: Rango Temporal, Selector Planta, Editar Datos, Exportar .xlsx, Idioma"]
    Panel --> Sidebar["Menu Lateral Filtrado por Permisos"]

    Sidebar -->|dashboard.ver| S0["Dashboard (4 KPIs + 3 Chart.js + Timeline)"]
    Sidebar -->|acopio.*| S1["1. Acopio por Voz y Tacto + Ganaderos"]
    Sidebar -->|calidad.*| S2["2. Calidad Sanitaria 74° (Modal Confirm JS)"]
    Sidebar -->|tinas.*| S3["3. Multiples Tinas, Curva Termica y Receta"]
    Sidebar -->|finanzas.*| S4["4. Liquidacion Quincenal, Ventas y Ayuda del Dia"]
    Sidebar -->|estadisticas.ver| S5["5. Estadisticas Comparativas y Ranking"]
    Sidebar -->|admin.*| S6["6. Administracion de Plantas y Usuarios"]

    S4 -->|finanzas.exportar| Excel["GET /panel/exportar-excel (Archivo .xlsx 4 hojas)"]
    Sidebar --> Logout["POST /logout -> Redirige a /"]
```

![Figura 3. Flujo de navegación entre portal público y módulos del panel operativo](imagenes/diagramas/03_flujo_navegacion.png)

### 6.3 Roles y permisos

En `app/Models/User.php` el sistema define **6 cargos** (5 plantillas predefinidas + `personalizado`), **4 rangos jerárquicos** (`administrador`, `supervisor`, `operador`, `personalizado`) y **19 permisos granulares** agrupados en 7 secciones (retirado el antiguo permiso `admin.qr` e incorporado `finanzas.enviar_excel`). La matriz vigente es la siguiente:

| Sección | Permiso granular (`permiso:*`) | Administración (`donjusto`) | Encargado Planta (`carlos.anta`) | Recolector Ruta (`mateo.ruta`) | Control Tina (`sabina.tina`) | Contador Externo (`contador.marco`) | Personalizado (ej. `lucia.personal`) |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Dashboard** | `dashboard.ver` | Sí | Sí | No | No | Sí | Configurable (No) |
| | `dashboard.ver_red` | Sí | No | No | No | No | Configurable (No) |
| **1. Acopio** | `acopio.ver` | Sí | Sí | Sí | No | No | Configurable (No) |
| | `acopio.crear` | Modo operativo* | Sí | Sí | No | No | Configurable (No) |
| | `acopio.editar` | Modo operativo* | Sí | **Sí** | No | No | Configurable (No) |
| | `acopio.proveedores` | Modo operativo* | Sí | Sí | No | No | Configurable (No) |
| **2. Calidad** | `calidad.ver` | Sí | Sí | No | Sí | No | Configurable (**Sí**) |
| | `calidad.evaluar` | Modo operativo* | Sí | No | Sí | No | Configurable (**Sí**) |
| **3. Tinas** | `tinas.ver` | Sí | Sí | No | Sí | No | Configurable (**Sí**) |
| | `tinas.operar` | Modo operativo* | Sí | No | Sí | No | Configurable (**Sí**) |
| | `tinas.gestionar` | Modo operativo* | Sí | No | No | No | Configurable (No) |
| **4. Finanzas** | `finanzas.ver` | Sí | Sí | No | No | Sí | Configurable (No) |
| | `finanzas.registrar` | Sí | Sí | No | No | No | Configurable (No) |
| | `finanzas.editar` | Sí | Sí | No | No | No | Configurable (No) |
| | `finanzas.exportar` | Sí | Sí | No | No | Sí | Configurable (No) |
| | `finanzas.enviar_excel` | Sí | Sí | No | No | No | Configurable (No) |
| **5. Estadísticas** | `estadisticas.ver` | Sí (incluye `.txt` 24h) | Sí (su planta) | No | No | Sí | Configurable (No) |
| **6. Admin** | `admin.usuarios` | Sí | Sí (solo su planta) | No | No | No | Configurable (según rango) |
| | `admin.plantas` | Sí | No | No | No | No | Configurable (No) |

*(Nota: \*En `administracion`, los 6 permisos de escritura operativa de M1–M3 están deshabilitados por defecto y se habilitan temporalmente en sesión mediante `#btn-toggle-modo-operativo`. Por su parte, `encargado_planta` está aislado a su propia planta y un supervisor con `admin.usuarios` solo delega un subconjunto de sus permisos dentro de su misma planta.)*

### 6.4 Casos de uso principales

```plantuml
@startuml
left to right direction
skinparam packageStyle rectangle

actor "Visitante" as VIS
actor "Recolector en Ruta" as REC
actor "Control de Tina / Calidad" as TIN
actor "Encargado de Planta" as ENC
actor "Contador Externo" as CON
actor "Administracion" as ADM

rectangle "Plataforma Lactea Comunitaria LAT Compacto" {
    usecase "CU-01: Consultar portada y cambiar idioma (ES/QU/EN)" as UC1
    usecase "CU-02: Registrar nueva planta y cuenta admin" as UC2
    usecase "CU-03: Iniciar y cerrar sesion (usuario o correo)" as UC3
    usecase "CU-04: Visualizar Dashboard con graficas y filtros" as UC4
    usecase "CU-05: Registrar acopio por voz o botonera tactil" as UC5
    usecase "CU-06: Gestionar ganaderos y corregir acopios (segun permiso)" as UC6
    usecase "CU-07: Evaluar calidad sanitaria 74° (tina / quesillo)" as UC7
    usecase "CU-08: Gestionar multiples tinas, lotes termicos y receta" as UC8
    usecase "CU-09: Consultar liquidacion quincenal y registrar ventas/adelantos" as UC9
    usecase "CU-10: Exportar balance contable Excel (.xlsx 4 hojas)" as UC10
    usecase "CU-11: Consultar estadisticas y ranking de plantas" as UC11
    usecase "CU-12: Administrar plantas y usuarios personalizados" as UC12
}

VIS --> UC1
VIS --> UC2
VIS --> UC3

REC --> UC3
REC --> UC5
REC --> UC6

TIN --> UC3
TIN --> UC7
TIN --> UC8

ENC --> UC3
ENC --> UC4
ENC --> UC5
ENC --> UC6
ENC --> UC7
ENC --> UC8
ENC --> UC9
ENC --> UC11

CON --> UC3
CON --> UC4
CON --> UC9
CON --> UC10
CON --> UC11

ADM --> UC3
ADM --> UC4
ADM --> UC5
ADM --> UC6
ADM --> UC7
ADM --> UC8
ADM --> UC9
ADM --> UC10
ADM --> UC11
ADM --> UC12
@enduml
```

![Figura 4. Diagrama de casos de uso principales por rol operativo](imagenes/diagramas/04_casos_uso.png)

#### Especificación resumida de los casos de uso clave

| Código | Caso de uso | Actor principal | Flujo básico y resultado esperado |
| :--- | :--- | :--- | :--- |
| **CU-02** | Registrar planta y administrador | Visitante | Completa formulario en `/registrarse`; el sistema valida unicidad y crea en `DB::transaction()` la planta (`PLT-XX`), `TINA-01` y el usuario `administracion`, iniciando sesión automáticamente. |
| **CU-05** | Registrar acopio por voz o offline | Recolector / Encargado | Selecciona ganadero, dicta la frase por micrófono (envío automático al validar productor y litros) o usa la botonera `+10` a `+50` (`Registrar entrega`). Si no hay red, `lat-offline.js` encola en `localStorage` y sincroniza en bloques `<= 50`. |
| **CU-07** | Evaluar calidad sanitaria 74° | Control de Tina / Encargado | Elige *Aprobar*, *Desviar a quesillo* o *Descartar* (con confirmación modal coloreada), bloqueando cambios si el acopio ya está en un lote (`lote_id !== null`). Emite alerta dirigida solo a `encargado_planta` y `control_tina`. |
| **CU-08** | Operar tina trazable y receta editable | Control de Tina / Encargado | Selecciona acopios evaluados libres (`#modal-iniciar-tina`), ajusta receta/cronómetro y valida capacidad máxima; guarda temperatura de forma independiente (`step=0.1`), recalcula insumos y cierra el lote con moldes reales. |
| **CU-10** | Liquidar y enviar Excel al Contador | Contador / Encargado / Admin | Opera las 3 tablas estilo Excel (`Liquidación`, `Ventas`, `Sueldos`), aplica adelantos por FIFO (`adelanto_aplicaciones`), exporta `.xlsx` completo o por tabla (`?solo=...`) y remite/revisa reportes en `reportes_excel` con alerta verde. |
| **CU-12** | Administrar plantas, usuarios e informes 24h | Administración / Encargado | Administración gestiona plantas (con papelera 30d), alterna su modo operativo y descarga informes `.txt` de 24h; Encargado gestiona únicamente usuarios dentro de su planta y envía informes manuales de actividad. |

### 6.5 Funcionalidades CRUD completas

A continuación se detallan las operaciones **Create, Read, Update y Delete** verificadas en `routes/web.php` y los cuatro controladores del repositorio:

| Entidad / Recurso | Create (Crear) | Read (Consultar) | Update (Actualizar) | Delete (Eliminar / Inactivar) | Controlador y permiso |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Acopios (`acopios`)** | `POST /panel/acopio` y `POST /panel/acopio/sync` (`guardarAcopio` / offline) | `GET /panel` (`index` — entregas y filtros) | `PUT /panel/acopio/{id}` (`actualizarAcopio`) y `POST /panel/calidad/{id}` (`evaluarCalidad`) | `DELETE /panel/acopio/{id}` (bloqueado si `lote_id !== null`) | `PanelController` (`acopio.*`, `calidad.evaluar`) |
| **Movimientos, Liquidación y Reportes (`liquidaciones`, `reportes_excel`)** | `POST /panel/movimiento` y `POST /panel/reportes-excel/enviar` | `GET /panel` (3 grillas estilo Excel), `GET /panel/exportar-excel` (`?solo=...`) | `PUT /panel/finanzas/liquidacion-grid`, `PUT /panel/movimiento/{id}`, `POST /panel/reportes-excel/{id}/revisar` | `DELETE /panel/movimiento/{id}` (con modal JS) | `PanelController` (`finanzas.*`) |
| **Ganaderos (`proveedores`)** | `POST /panel/proveedor` (`guardarProveedor`) | `GET /panel` (`index` — lista activa por planta) | `PUT /panel/proveedor/{id}` (`actualizarProveedor`) | `DELETE /panel/proveedor/{id}` (`eliminarProveedor` / baja lógica) | `PanelController` (`acopio.proveedores`) |
| **Tinas y Recetas (`tinas`, `plantas.recetas_base`)** | `POST /panel/tinas` (`crearTina`) | `GET /panel` (`index` — grilla de tinas y ocupación) | `PUT /panel/tinas/{id}` (`editarTina`), `PUT /panel/tinas/recetas-base` | `DELETE /panel/tinas/{id}` / inactivación segura (`activa = 0`) | `PanelController` (`tinas.ver`, `tinas.gestionar`) |
| **Lotes térmicos (`lotes`)** | `POST /panel/tinas/{tinaId}/lote` (`iniciarLoteEnTina`) | `GET /panel` (`index` — lote activo, cronómetro y moldes) | `POST /panel/tina/{id}` (`actualizarTina` — °C independiente, insumos, cronómetro y cierre) | Cierre definitivo a estado `terminado` (trazabilidad intacta) | `PanelController` + `TinaWorkflowService` (`tinas.operar`) |
| **Plantas (`plantas`)** | `POST /registrarse` y `POST /panel/admin/plantas` | `GET /panel` (`index` — activas, papelera y ranking) | `PUT /panel/admin/plantas/{id}`, `POST /panel/cambiar-planta` y `POST .../restaurar` | `DELETE /panel/admin/plantas/{id}` (papelera 30d) y `.../definitivo` (`lat:purgar-plantas`) | `AuthController` / `AdminController` (`admin.plantas`) |
| **Usuarios (`users`)** | `POST /registrarse` y `POST /panel/admin/usuarios` | `GET /panel` (`index` — listado por planta/rango) | `PUT /panel/admin/usuarios/{id}` (cargo, rango, clave y 19 permisos) | `DELETE /panel/admin/usuarios/{id}` / desactivación lógica (`activo = 0`) | `AuthController` / `AdminController` (`admin.usuarios`) |
| **Notificaciones e Informes (`notificaciones_panel`, `informes_actividad_planta`)** | `POST /panel/notificaciones/contable` y `POST /panel/informes-planta/enviar` | `GET /panel/notificaciones/poll` y `GET /panel/estadisticas/informes/{id}/descargar` | `POST /panel/notificaciones/leer` | Historial auditable inmutable (Admin Total descarga `.txt`) | `PanelController` + `InformePlantaService` |

### 6.6 Métricas proyectadas de impacto social

Rescatando los indicadores base levantados en el diagnóstico inicial de Anta/Zurite y actualizándolos al alcance final del sistema:

| Indicador de impacto | Línea base (cuaderno manual) | Meta con LAT Compacto | Cómo se mide / verifica |
| :--- | :--- | :--- | :--- |
| **Pérdida o borrado de apuntes de acopio** | ~12% de registros dañados por agua/suero | **0%** de extravío de registros | Auditoría de entregas en tabla `acopios` y respaldo diario. |
| **Tiempo de registro por entrega** | ~50 segundos por proveedor (papel y lapicero) | **< 10 segundos** por entrega | Cronometraje en recepción usando dictado vocal o botonera `54 px`. |
| **Trazabilidad de leche ácida aprovechada** | Desordenado / rechazo frecuente | **100%** clasificada hacia `quesillo_4_dias` | Gráfica de distribución sanitaria y hoja *Detalle Acopios* del `.xlsx`. |
| **Tiempo de cierre de liquidación quincenal** | 6 a 8 horas sumando cuadernos y adelantos | **Instantáneo (< 200 ms)** + Excel en 1 clic | Respuesta del servidor en `LacteoService::liquidarQuincena()`. |
| **Costo por uso de software para la quesería** | S/ 150–450/mes (ERP) o comisión por litro | **S/ 0.00 garantizado** | Parámetro `LAT_COMISION=0.00` y licencia abierta en GitHub. |
| **Inclusión de operarios quechuahablantes** | 0% (interfaces solo en español) | **100% de la interfaz** (**838 claves JS + 179 PHP** en *Runasimi* oficial) | Conmutador trilingüe en sidebar verificado en PHPUnit y Playwright. |

---

## 7. DESARROLLO INICIAL Y OPTIMIZACIÓN TÉCNICA

En esta sección se presentan los dos apartados técnicos obligatorios de cierre sobre el código implementado en el repositorio: la optimización de consultas SQL y rendimiento (`7.7`) y la seguridad web aplicada con sus pruebas (`7.8`).

### 7.7 Optimización de consultas y rendimiento

#### 7.7.1 Diagnóstico inicial

Durante el desarrollo de las liquidaciones quincenales y del panel analítico identificamos tres problemas frecuentes de rendimiento en aplicaciones Laravel con Eloquent:

1. **Problema N+1 en cierres quincenales y listados:** Consultar primero los proveedores y, dentro de un bucle `foreach`, sumar en base de datos los litros de `acopios` y los adelantos de `liquidaciones` por cada ganadero generaría `1 + 2N` consultas SQL (por ejemplo, 101 consultas para 50 proveedores), haciendo lento el panel en una PC local con XAMPP.
2. **Escaneos completos de tabla (*Full Table Scan*):** Filtrar acopios por fecha y planta, lotes activos por estado o adelantos pendientes sin índices compuestos obliga a MariaDB a revisar toda la tabla cuando el historial crece a miles de filas.
3. **Consultas innecesarias según el rol y cálculos repetidos:** Cargar datos de tinas, liquidaciones, estadísticas y usuarios para un recolector que solo usa *Acopio* desperdicia memoria y consultas. Además, en nuestra revisión de `PanelController@index` (línea 243) y `LacteoService::obtenerDatosDashboard()` (línea 407) vimos que para cuentas con acceso a Estadísticas y Dashboard se llama dos veces a `obtenerEstadisticas()` (una para la quincena y otra para el rango del dashboard, lo que repite el cálculo cuando `rango=quincena`), y que el uso de `whereDate('fecha', ...)` aplica la función `DATE(fecha)` en MySQL/MariaDB, reduciendo el aprovechamiento del índice B-Tree.

#### 7.7.2 Técnicas aplicadas

En el código actual del repositorio ya se implementaron las siguientes mejoras:

| Técnica de optimización | Ubicación exacta en el repositorio | Efecto técnico logrado |
| :--- | :--- | :--- |
| **Agregación SQL agrupada Anti-N+1 + FIFO idempotente** | `app/Services/LacteoService.php` (`liquidarQuincena`) | Cambia `1 + 2N` consultas por consultas agrupadas fijas (`Proveedor`, `Acopio::liquidables()` y `Liquidacion` unida a `adelanto_aplicaciones`), aplicando adelantos en orden FIFO sin doble descuento ni alteración del historial. |
| **Índices compuestos ampliados y restricciones por planta** | Migraciones `000001`, `000002`, `2026_11_25_153707`, `2026_11_26_184041`, `210000` y `221223` | Acelera búsquedas por planta, fechas, lote/calidad, notificaciones globales/planta, bitácora, informes y `uq_adelanto_aplicacion_quincena`. |
| **Rangos de fecha sin funciones SQL (`DATE()`)** | `app/Services/LacteoService.php` (`aplicarRangoFecha`, `aplicarHastaFecha`) | Reemplaza `whereDate()` por `whereBetween()` / comparaciones directas sobre columna `DATE` en MariaDB/MySQL, preservando los índices B-Tree. |
| **Eager Loading, selección puntual y presupuesto `<= 42` SQL** | `app/Http/Controllers/PanelController.php` | Reutiliza colecciones ya cargadas (como `$plantas`), usa `with()` y `withCount()` y mantiene `GET /panel` bajo el techo verificado de `<= 42` consultas. |
| **Carga condicional y límites por sección** | `app/Http/Controllers/PanelController.php`, `public/js/lat-offline.js` | Solo consulta las tablas autorizadas al rol activo; limita consultas recientes y sincroniza la cola offline por bloques de hasta `50` entregas. |
| **Caché de producción, OPcache y Service Worker `v8`** | `Dockerfile`, `docker/start.sh`, `public/sw.js` | Activa `opcache`, cachea rutas/vistas/configuración y sirve estáticos *Cache-First* excluyendo descargas Excel, informes `.txt`, `/idioma` y polling de notificaciones. |

*Estado tras la auditoría:* Se cerraron el reemplazo de `whereDate()` por `aplicarRangoFecha()`, el presupuesto `<= 42` consultas en `GET /panel` (verificado en `LatAuditoriaFinalTest`) y el registro trazable de aplicaciones FIFO en `adelanto_aplicaciones`. Queda como mejora opcional futura la paginación (`paginate()`) en historiales históricos extensos.

#### 7.7.3 Evidencias antes/después

Comparativa técnica del impacto de las optimizaciones implementadas en el núcleo de liquidación y panel:

| Escenario evaluado | Antes (enfoque ingenuo / sin índices) | Después (implementación actual en LAT) | Mejora obtenida |
| :--- | :--- | :--- | :--- |
| **Liquidación quincenal de 50 productores** | `1 + 2(50) = 101` consultas SQL (N+1 en bucle) | **3 consultas SQL fijas** (`SELECT` + 2 `GROUP BY SUM`) | **-97.0%** consultas a BD |
| **Listado de entregas diarias + productor** | `1 + N` consultas por cada entrega en tabla | **2 consultas** (`acopios` + `WHERE id IN (...)` vía `with`) | Eliminación total de N+1 |
| **Listado de plantas con conteo de tinas y usuarios** | `1 + 3P` consultas para `P` plantas | **1 consulta** con subconsultas `withCount()` | Eliminación de N+1 en Admin |
| **Carga de `/panel` para `mateo.ruta` (solo Acopio)** | Ejecutaba consultas de dashboard, tinas y finanzas | **Omite** consultas de 5 módulos no autorizados | Menor carga de BD y HTML más liviano |
| **Escaneo de tabla `acopios` por planta y fecha** | `type = ALL` (*Full Table Scan*) | Uso de `idx_acopios_planta_fecha` / `idx_acopios_proveedor_fecha` (óptimo con `BETWEEN`) | Reducción drástica de filas inspeccionadas |

> **[INSERTAR EVIDENCIA VISUAL AQUÍ]**
> - **Qué mostrar:** Captura de consola MariaDB/MySQL ejecutando `EXPLAIN` sobre las consultas de liquidación quincenal (comparando consulta sin índice / con `DATE(fecha)` frente a consulta con índice compuesto y `BETWEEN`) y captura de la pestaña `Network` de DevTools mostrando el tiempo de respuesta (`TTFB`) y peso de `/panel`.
> - **Herramienta recomendada:** Consola MySQL de XAMPP (`C:\xampp\mysql\bin\mysql.exe`) o phpMyAdmin + Chrome/Edge DevTools (`F12 -> Network`).
> - **Archivo/Ruta de referencia en el repo:** `app/Services/LacteoService.php` (líneas 84–139 y 144–169), `database/migrations/2026_01_01_000001_crear_tablas_lat_compacto.php` y `Anexo G`.

---

### 7.8 Seguridad web implementada y evidencias

#### 7.8.1 Amenazas consideradas

En correspondencia con el diseño planteado en la sección 5.5 y el estándar **OWASP Top 10 (2021)** [5], evaluamos las siguientes amenazas sobre el código real del repositorio:

| Código OWASP | Amenaza evaluada | Riesgo concreto en LAT Compacto |
| :--- | :--- | :--- |
| **A01:2021** | Control de acceso roto (*Broken Access Control* / IDOR) | Que un recolector acceda a liquidaciones/exportación Excel, que un usuario edite acopios de otra planta manipulando `{id}`, o que un supervisor escale privilegios creando un administrador. |
| **A02:2021** | Fallas criptográficas | Almacenamiento inseguro de contraseñas o tráfico sin HTTPS en despliegue en la nube. |
| **A03:2021** | Inyección (SQL Injection y XSS) | Inyección SQL en el campo `login` o filtros, o ejecución de `<script>` malicioso en `concepto`, `nombre` o `frase_dictada` al mostrarse en tablas o exportarse a Excel. |
| **A05:2021** | Configuración de seguridad incorrecta | Subida accidental de `.env` o bases de datos a Git, listado público de carpetas en Apache o falta de cabeceras defensivas. |
| **A07:2021** | Fallas de identificación y autenticación | Secuestro/fijación de sesión, acceso de cuentas desactivadas o fuerza bruta sobre `/login`. |

#### 7.8.2 Controles implementados

La auditoría directa sobre controladores, middlewares, configuración y vistas confirmó los siguientes controles activos:

| Amenaza | Control implementado y verificado en código | Archivo de referencia en el repo |
| :--- | :--- | :--- |
| **Control de acceso roto y escalamiento** | Middleware `auth` y `CheckLatPermission` (`permiso:*` con respuesta **HTTP 403**); aislamiento por `planta_id` en todas las rutas; bloqueo por defecto de escritura M1–M3 en `administracion` (`admin_modo_operativo`); aislamiento estricto de `encargado_planta` dentro de su planta; bloqueo de edición/borrado en acopios con `lote_id !== null`; `permisosAsignables()` y `puedeGestionarUsuario()`. | `routes/web.php`, `CheckLatPermission.php`, `User.php`, `AdminController.php`, `PanelController.php` |
| **SQL Injection** | 100% de las lecturas y escrituras pasan por Eloquent ORM / PDO con *bindings* parametrizados; las agregaciones `selectRaw()` usan expresiones fijas o parámetros enlazados sin interpolar entrada del usuario. | `LacteoService.php`, `InformePlantaService.php`, `PanelController.php` |
| **XSS y Excel Formula Injection** | Escape automático `{{ }}` en Blade (los `{!! !!}` corresponden únicamente a SVG estáticos internos), escritura con `.textContent` en `lat-i18n.js`, saneamiento XML (`ENT_XML1 \| ENT_QUOTES`) y neutralización de prefijos peligrosos (`=`, `+`, `-`, `@`) en `ExcelExportService::sanitizeFormulaInjection()`. | `resources/views/**/*.blade.php`, `public/js/lat-i18n.js`, `ExcelExportService.php` |
| **CSRF y Fuerza bruta** | Directiva `@csrf` en formularios mutables, `SESSION_SAME_SITE=lax`, limpieza de caché `/panel` en Service Worker al cerrar sesión (`LAT_CLEAR_AUTH_CACHE`) y limitación `throttle:5,1` en registro y control de intentos en login. | `routes/web.php`, `AuthController.php`, `public/sw.js`, `config/session.php` |
| **Autenticación, contraseñas y sesiones** | Hash Bcrypt (`hashed`), verificación `'activo' => true`, `session()->regenerate()` al ingresar, `invalidate()` + `regenerateToken()` al salir y cookies `http_only => true`. | `AuthController.php`, `User.php`, `config/session.php` |
| **Cabeceras HTTP y configuración segura** | `SecurityHeadersMiddleware` global (`X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy` con `microphone=(self)` y `Content-Security-Policy`), whitelists `$fillable`/`$hidden`, `.gitignore` endurecido y `URL::forceScheme('https')` en producción. | `SecurityHeadersMiddleware.php`, `bootstrap/app.php`, `AppServiceProvider.php`, `public/.htaccess` |

*Estado de las mejoras de endurecimiento:* Las tres medidas recomendadas en el informe previo quedaron implementadas y cubiertas por pruebas (`throttle:5,1`, validación explícita en `evaluarCalidad`/`actualizarTina` y `SecurityHeadersMiddleware` global), además de suprimir accesos demo inseguros en producción (`APP_ENV=production`).

#### 7.8.3 Evidencias de pruebas

El repositorio cuenta ahora con una batería automatizada ampliada a **103 pruebas en PHPUnit 12 (1,114 aserciones — 100% PASS)** ejecutadas con SQLite en memoria (`RefreshDatabase`), complementadas por verificación E2E en Playwright (`tests/e2e/visual-standards.cjs`, **294/294 checks** en escritorio `1280×800`, móvil `390×844` y móvil compacto `320×568`):

| N.° | Suite de pruebas (`tests/`) | Casos / Qué verifica | Enfoque |
| :-: | :--- | :--- | :--- |
| 1 | `Unit/LacteoServiceTest.php` | 4 pruebas unitarias de dosificación estequiométrica, curva térmica, liquidación quincenal y estadísticas. | Lógica Core |
| 2 | `Feature/LatFuncionalidadesTest.php` | 25 casos (con *data providers*) sobre registro, RBAC, multi-planta, múltiples tinas, edición/borrado, Excel `.xlsx` e i18n trilingüe. | Funcional / RBAC |
| 3 | `Feature/LatSeguridadOwaspTest.php` | 6 casos OWASP Top 10: IDOR cruzado entre plantas (A01), SQLi/XSS/Excel Formula Injection (A03), rate limiting (A07), cabeceras y CSRF (A05). | Seguridad OWASP |
| 4 | `Feature/LatAuditoriaFinalTest.php` | 8 casos sobre papelera de plantas 30d, bloqueo de login tras intentos fallidos, cola offline PWA, envío Excel al contador y presupuesto `<= 42` SQL en `/panel`. | Auditoría / N+1 |
| 5 | `Feature/LatFlujoTinasNotificacionesTest.php` | 17 casos del flujo obligatorio `Acopio → Calidad → Tinas`, recetas base, cronómetro editable, prefijo `TINA-`/`VAT-` y notificaciones en vivo. | Flujo Trazable |
| 6 | `Feature/LatObservacionesPanelTest.php` | 11 casos sobre validación de código `PLT-`, selector único de idioma en sidebar, estado `pendiente` en acopios y filtros en panel. | Reglas Panel / UI |
| 7 | `Feature/LatRolesFinanzasInformesTest.php` | 6 casos sobre gobierno del Administrador Total, aislamiento de `encargado_planta`, notificaciones verdes contables, 3 tablas Excel e informes 24h. | Gobierno / Finanzas |
| 8 | `Feature/LatCorreccionesAuditoriaTest.php` | 11 casos sobre aplicaciones FIFO idempotentes (`adelanto_aplicaciones`), conservación de descartados, bloqueo por `lote_id`, temperatura y chunks offline. | Integridad / Pagos |

*(Nota: Se completó la instalación de `vendor/` y `node_modules/`, quedando validada la suite PHPUnit con `103 tests / 1,114 assertions` en verde y el script E2E Playwright `tests/e2e/visual-standards.cjs` actualizado al sidebar actual.)*

> **[INSERTAR EVIDENCIA VISUAL AQUÍ]**
> - **Qué mostrar:** Capturas en vivo de: (1) salida en verde de PHPUnit con las **103 pruebas / 1,114 aserciones** pasando, (2) intento de SQLi (`' OR '1'='1' --`) rechazado en `/login`, (3) payload XSS (`<script>alert('XSS')</script>`) en concepto financiero mostrado como texto escapado sin ejecutarse, (4) petición `POST` sin token CSRF bloqueada con **HTTP 419**, y (5) reporte de `composer audit` y `npm audit`.
> - **Herramienta recomendada:** Terminal PowerShell + Navegador con DevTools (`F12 -> Network / Console / Elements`) + `curl`.
> - **Archivo/Ruta de referencia en el repo:** `tests/Feature/LatSeguridadOwaspTest.php`, `app/Http/Middleware/SecurityHeadersMiddleware.php`, `resources/views/login.blade.php` y `Anexo H`.

---

## 8. GESTIÓN DEL PROYECTO

### 8.1 Cronograma con hitos y responsables

El desarrollo del proyecto se organizó a lo largo de las **16 semanas** del semestre académico, consolidando el código en el repositorio GitHub durante noviembre de 2026 (`01/11/2026` al `26/11/2026`) y cerrando con la auditoría y entrega final:

| Semanas | Fase / Hito principal | Entregables concretos | Responsable principal | Estado |
| :---: | :--- | :--- | :--- | :---: |
| **1 – 2** | Diagnóstico social y levantamiento en campo | Identificación del caso lácteo en Zurite/Anta, entrevistas en campo, revisión de DRA Cusco/INEI y `PRODUCT.md`. | Todo el equipo | Completado |
| **3 – 4** | Especificación y diseño de arquitectura | Requerimientos RF/RNF, modelo ER inicial, diseño visual `DESIGN.md` y estructura del repositorio GitHub. | Nasdig / Arantxa / Kelmer | Completado |
| **5 – 6** | Persistencia base y modelos Eloquent | Instalación Laravel, migración base con índices compuestos, modelos iniciales (`User`, `Proveedor`, `Acopio`, `Lote`, `Liquidacion`) y `DatabaseSeeder`. | Nasdig Ortiz | Completado |
| **7 – 8** | Lógica core y vistas iniciales | `LacteoService` (receta, curva térmica, liquidación sin N+1), `AuthController`, `PanelController`, layout trilingüe y vistas iniciales. | Arantxa Lovatón / Kelmer Obregón | Completado |
| **9 – 10** | Rediseño Swiss-Grid y prototipado visual | Evolución a sistema editorial Swiss-Grid, líneas discontinuas en panel y generación de 10 archivos en `docs/mockups/`. | Kelmer Obregón | Completado |
| **11 – 12** | Evolución multi-planta, multi-tina y exportación `.xlsx` | Migración `000002` (`plantas`, `tinas`, ventas), `ExcelExportService.php`, ranking de plantas y modales de edición/confirmación JS. | Nasdig / Arantxa / Kelmer | Completado |
| **13 – 14** | Dashboard analítico, permisos granulares, i18n total y Docker | Migración `permisos`/`rango`, `CheckLatPermission`, `SetLocaleMiddleware`, Dashboard Chart.js, i18n ES/QU/EN oficial, `LatFuncionalidadesTest.php` y `Dockerfile` para Render. | Todo el equipo (integración: Kelmer) | Completado |
| **15 – 16** | Auditoría técnica, endurecimiento, informe final y sustentación | Revisión integral de rendimiento/seguridad (OWASP + `<= 42` SQL), flujo trazable Acopio–Calidad–Tinas, finanzas Excel, informes 24h, `103` pruebas PHPUnit, E2E Playwright (`294/294`) y actualización de `docs/INFORME_FINAL_LAT.md`/`.docx`. | Todo el equipo | Completado / En sustentación |

### 8.2 Distribución de responsabilidades

El equipo mantuvo una división equilibrada de roles técnicos (33.3% / 33.3% / 33.4%) combinando desarrollo de código, diseño de base de datos, validación de campo, pruebas y documentación. En el historial acumulado de Git (**42 commits** hasta el `26/11/2026`) se refleja tanto el trabajo por módulos como las correcciones de auditoría e integración final:

| Integrante | Rol en el equipo | Responsabilidades técnicas y académicas cubiertas | Aporte trazado en GitHub |
| :--- | :--- | :--- | :--- |
| **Ortiz Gutierrez, Nasdig Jordan** | Arquitecto de Base de Datos y Persistencia (33.3%) | Migraciones e índices compuestos anti *Full Table Scan*, modelo `AdelantoAplicacion` (FIFO idempotente), descarte trazable de acopios, modelos Eloquent y relaciones, `DatabaseSeeder`, documentación en `docs/` y auditoría de pagos/lotes/calidad. | 12 commits (`75120565-arch`) |
| **Lovatón Casas, Arantxa Alejandra** | Ingeniero Backend y Lógica Core (33.3%) | Servicios de negocio (`LacteoService`: cálculo de insumos, control térmico, liquidación sin N+1), controladores core (`AuthController`, `PanelController`), rutas protegidas, diseño de casos de prueba y documentación de seguridad. | 3 commits (`Arantxa` / `Lovaton`) |
| **Obregon Choque, Kelmer Wilder** | Diseñador Frontend UI/UX y Accesibilidad (33.4%) | Design System Swiss-Grid (`DESIGN.md`, CSS), vistas Blade, Web Speech API, PWA/offline, `TinaWorkflowService`, `InformePlantaService`, tablas Excel (`lat-finanzas-grid.js`), notificaciones segmentadas, i18n trilingüe oficial (838 JS / 179 PHP), seguridad OWASP, suites PHPUnit/Playwright y Docker/Render. | 27 commits (`KelmerOC`) |

### 8.3 Registro de reuniones y acuerdos

| N.° | Fecha | Participantes | Tema tratado | Acuerdos principales |
| :-: | :--- | :--- | :--- | :--- |
| 1 | 11 oct 2026 | Nasdig, Arantxa, Kelmer | Salida de campo y definición del caso social (Zurite / Anta) | Priorizar dictado por voz + botonera de `54 px`, desvío a quesillos de 4 días y comisión S/ 0.00 sin pedir DNI en jornales eventuales. |
| 2 | 01 nov 2026 | Nasdig, Arantxa, Kelmer | Arranque de repositorio y plan de commits (`GUIA_GITHUB_ESTUDIANTES.md`) | Establecer el orden de dependencias por capas (Migración -> Modelos -> Servicios -> Controladores -> Vistas) para evitar conflictos de fusión. |
| 3 | 03 nov 2026 | Nasdig, Arantxa, Kelmer | Revisión de diseño UI/UX y accesibilidad | Adoptar la guía Swiss-Grid (`DESIGN.md`), validar contraste AA y generar los 10 wireframes y mockups en `docs/mockups/`. |
| 4 | 18 nov 2026 | Nasdig, Arantxa, Kelmer | Cierre técnico: Dashboard, multi-planta, permisos granulares e i18n | Integrar múltiples tinas, exportación nativa `.xlsx`, gráficas Chart.js, permisos con jerarquía de rangos, i18n ES/QU/EN, pruebas PHPUnit y Docker para Render. |
| 5 | 24 nov 2026 | Nasdig, Arantxa, Kelmer | Entrega base del informe técnico en `docs/` | Elaborar `docs/INFORME_FINAL_LAT.md` y `docs/INFORME_FINAL_LAT.docx` con diagramas e inventariado del código real. |
| 6 | 25–26 nov 2026 | Nasdig, Arantxa, Kelmer | Auditoría integral OWASP, trazabilidad, finanzas Excel y actualización del informe | Implementar hardening OWASP, PWA offline, flujo trazable Acopio–Calidad–Tinas, gobierno Admin Total, finanzas en 3 tablas Excel, informes 24h, correcciones FIFO/lote/descarte (`103` tests PASS) y actualizar el informe final. |

### 8.4 Acuerdos de trabajo y compromisos

- **Buenas prácticas en Git:** Usar mensajes de commit semánticos (`feat`, `fix`, `style`, `perf`, `test`, `chore`, `docs`) y mantener `.env` y archivos binarios de base de datos fuera del repositorio.
- **Verificación previa a la sustentación:** Levantar el entorno limpio con `composer install`, `php artisan migrate:fresh --seed` y `php artisan test --compact` en la máquina de presentación y comprobar el contenedor Docker.
- **Compromiso de cierre visual y técnico:** Completar las evidencias marcadas con `[INSERTAR EVIDENCIA VISUAL AQUÍ]` (fichas firmadas de entrevistas, capturas a color del dashboard, `EXPLAIN` en MariaDB y pruebas de seguridad) en Google Docs antes de la sustentación final.

---

## 9. REFERENCIAS Y PROPIEDAD INTELECTUAL

### 9.1 Referencias bibliográficas

*(Referencias redactadas siguiendo el manual institucional de la Universidad Continental: ARIAS CHÁVEZ, Dennis. Manual para citar y referenciar fuentes en textos de ingeniería según la norma ISO 690-2, 2019 [6].)*

1. PERÚ. CONGRESO DE LA REPÚBLICA. *Ley N.° 29733: Ley de Protección de Datos Personales* [en línea]. Diario Oficial El Peruano, 3 de julio de 2011 [fecha de consulta: 24 de noviembre de 2026]. Disponible en: <https://www.gob.pe/institucion/congreso-de-la-republica/normas-legales/243470-29733>
2. DIRECCIÓN REGIONAL DE AGRICULTURA CUSCO (DRA CUSCO). *Compendio Estadístico Agropecuario de la Región Cusco 2023*. Cusco: Gobierno Regional de Cusco, 2024.
3. INSTITUTO NACIONAL DE ESTADÍSTICA E INFORMÁTICA (INEI). *IV Censo Nacional Agropecuario: Resultados de la actividad pecuaria en el departamento de Cusco* [en línea]. Lima: INEI, 2022 [fecha de consulta: 24 de noviembre de 2026]. Disponible en: <https://www.gob.pe/inei>
4. WORLD WIDE WEB CONSORTIUM (W3C). *Web Content Accessibility Guidelines (WCAG) 2.1* [en línea]. W3C Recommendation, 5 de junio de 2018 [fecha de consulta: 24 de noviembre de 2026]. Disponible en: <https://www.w3.org/TR/WCAG21/>
5. OWASP FOUNDATION. *OWASP Top Ten: The Ten Most Critical Web Application Security Risks* [en línea]. OWASP, 2021 [fecha de consulta: 24 de noviembre de 2026]. Disponible en: <https://owasp.org/www-project-top-ten/>
6. ARIAS CHÁVEZ, Dennis. *Manual para citar y referenciar fuentes en textos de ingeniería según la norma ISO 690-2* [en línea]. Huancayo: Universidad Continental, Fondo Editorial, 2019 [fecha de consulta: 24 de noviembre de 2026]. ISBN electrónico: 978-612-4443-04-6. Disponible en: <https://repositorio.continental.edu.pe/>
7. LARAVEL LLC. *Laravel Documentation: Eloquent ORM, Blade Templates, Validation and Security* [en línea]. Laravel Framework, 2026 [fecha de consulta: 24 de noviembre de 2026]. Disponible en: <https://laravel.com/docs>
8. THE PHP GROUP. *PHP Manual: PDO, Prepared Statements and Password Hashing* [en línea]. PHP Documentation Group, 2026 [fecha de consulta: 24 de noviembre de 2026]. Disponible en: <https://www.php.net/manual/es/>
9. MOZILLA DEVELOPER NETWORK (MDN). *Web Speech API* [en línea]. Mozilla Foundation, 2026 [fecha de consulta: 24 de noviembre de 2026]. Disponible en: <https://developer.mozilla.org/en-US/docs/Web/API/Web_Speech_API>
10. CHART.JS CONTRIBUTORS. *Chart.js Documentation (v4.4)* [en línea]. Chart.js Open Source Project, 2025 [fecha de consulta: 24 de noviembre de 2026]. Disponible en: <https://www.chartjs.org/docs/latest/>
11. DOCKER INC. *Docker Documentation: Dockerfile Reference and Containerization* [en línea]. Docker, 2026 [fecha de consulta: 24 de noviembre de 2026]. Disponible en: <https://docs.docker.com/>

### 9.2 Licencias de software utilizadas

Listado exacto verificado directamente en `composer.json`, `composer.lock`, `package.json`, `package-lock.json` y archivos cliente en `public/`:

| Componente / Paquete | Fuente en el repo | Versión exacta / rango | Uso / Ámbito | Licencia |
| :--- | :--- | :---: | :--- | :--- |
| **PHP** | `composer.json` / `Dockerfile` | `^8.3` (`8.4` en Docker) | Runtime de servidor | PHP License 3.01 |
| `laravel/framework` | `composer.json` / `composer.lock` | `v13.39.0` (`^13.17`) | Producción (Framework MVC) | MIT |
| `laravel/tinker` | `composer.json` / `composer.lock` | `v3.0.2` (`^3.0`) | Producción (Consola REPL) | MIT |
| `phpunit/phpunit` | `composer.json` / `composer.lock` | `12.5.43` (`^12.5.12`) | Desarrollo / Pruebas | BSD-3-Clause |
| `mockery/mockery` | `composer.json` / `composer.lock` | `1.6.15` (`^1.6`) | Desarrollo / Mocks | BSD-3-Clause |
| `fakerphp/faker` | `composer.json` / `composer.lock` | `v1.24.1` (`^1.23`) | Desarrollo / Datos prueba | MIT |
| `laravel/pint` | `composer.json` / `composer.lock` | `v1.32.1` (`^1.27`) | Desarrollo / Estilo PSR-12 | MIT |
| `nunomaduro/collision` | `composer.json` / `composer.lock` | `v8.9.5` (`^8.6`) | Desarrollo / Reporte errores | MIT |
| `laravel/boost` | `composer.json` / `composer.lock` | `v2.10.4` (`^2.10`) | Desarrollo / Asistencia | MIT |
| `laravel/pail` | `composer.json` / `composer.lock` | `v1.2.7` (`^1.2.5`) | Desarrollo / Logs CLI | MIT |
| `laravel/pao` | `composer.json` / `composer.lock` | `v1.1.5` (`^1.0.6`) | Desarrollo / Utilidades | MIT |
| `playwright` | `package.json` / `package-lock.json` | `1.64.1` (`^1.64.1`) | Desarrollo / E2E y mockups | Apache-2.0 |
| **Chart.js** (`chart.umd.min.js`) | `public/js/chart.umd.min.js` | `4.4.7` | Producción (Gráficas UI) | MIT |
| **Fuentes Inter y Gloock** | `public/fonts/*.woff2` | Variable / 400 | Tipografía (`Inter` activa en CSS; `Gloock` en carpeta de recursos) | SIL OFL 1.1 |
| **LAT Compacto** | `composer.json` / `package.json` | `1.0.0` | Proyecto comunitario | MIT / ISC |

### 9.3 Declaración de originalidad

Nosotros, **Ortiz Gutierrez, Nasdig Jordan**, **Lovatón Casas, Arantxa Alejandra** y **Obregon Choque, Kelmer Wilder**, estudiantes de la Escuela Profesional de Ingeniería de Sistemas e Informática de la Universidad Continental (Asignatura Programación Web, NRC 24589), declaramos bajo responsabilidad académica que el desarrollo de la **Plataforma Láctea Comunitaria LAT Compacto** y el presente informe son de autoría propia de nuestro equipo. Las librerías de código abierto, frameworks, estadísticas institucionales y referencias normativas empleadas han sido debidamente citadas y licenciadas, y lo descrito en este documento corresponde fielmente al código verificado en nuestro repositorio GitHub.

Cusco, 26 de noviembre de 2026.

- **Ortiz Gutierrez, Nasdig Jordan** — Firma: ___________________________
- **Lovatón Casas, Arantxa Alejandra** — Firma: ___________________________
- **Obregon Choque, Kelmer Wilder** — Firma: ___________________________

---

## 10. ANEXOS

### Anexo A: Manual de usuario

**1. Ingreso al sistema y selección de idioma**
1. Abrir en el navegador `http://localhost/lat-compacto/` (XAMPP) o la URL pública desplegada en Render. En la portada (`/`) se muestra además el anuncio QR público **"Puedes registrarte ahora"** para acceder directamente a `/registrarse`.
2. Elegir el idioma en la portada (`ES`, `QU`, `EN`) o, dentro del panel, desde el selector único de la barra lateral (`.dash-lang-row`).
3. Pulsar **Iniciar Sesión** (`/login`) e ingresar el usuario o correo y la contraseña. En entorno local de demostración se muestran los **6 perfiles de acceso rápido** (`donjusto@sanborreal.pe`, `carlos.anta`, `sabina.tina`, `contador.marco`, `mateo.ruta`, `lucia.personal`, contraseña `secreto123`).

**2. Uso del Dashboard, notificaciones y modo operativo**
1. Al ingresar en `/panel`, quienes tengan permiso `dashboard.ver` ven los 4 KPIs, las 3 gráficas interactivas y la campana de notificaciones en vivo (alertas de tina para encargados/control y mensajes sombreados en verde para contabilidad).
2. Usar los botones superiores (`Hoy`, `7 días`, `Quincena`, `Mes`) para cambiar el período analizado.
3. El **Administrador Total** puede cambiar de planta y activar temporalmente la escritura en M1–M3 mediante el botón `#btn-toggle-modo-operativo`; los encargados de planta operan estrictamente en su planta asignada.

**3. Operación diaria en planta (Módulos 1 al 5)**
- **1. Acopio:** Elegir el ganadero, dictar por voz (envío automático al reconocer productor y litros válidos) o usar la botonera `+10` a `+50` y pulsar **Registrar entrega** (funciona también offline y sincroniza al volver la conexión). Las entregas ya asignadas a un lote no pueden modificarse ni eliminarse.
- **2. Calidad sanitaria (74°):** Filtrar pendientes por fecha/estado y elegir **Aprobar**, **Desviar a quesillo** o **Descartar** (confirmación modal coloreada); las entregas descartadas guardan motivo y responsable sin entrar a liquidación.
- **3. Tinas y receta:** En **Iniciar lote**, seleccionar las entregas evaluadas disponibles en `#modal-iniciar-tina`, revisar la receta y el cronómetro; durante el lote, actualizar temperatura (`step=0.1`) de forma independiente y finalizar registrando los moldes obtenidos.
- **4. Liquidación y ventas:** Operar las **3 tablas estilo Excel** (*Liquidación quincenal* editable inline con descuento FIFO de adelantos, *Ventas* y *Sueldos/Jornales sin DNI*), descargar `.xlsx` completo o por tabla (`?solo=...`) y enviar/revisar reportes en la bandeja del Contador.
- **5. Estadísticas e informes 24h:** Consultar rankings y dona sanitaria; los encargados pueden remitir informes manuales y el Administrador Total revisa y descarga el historial `.txt` de cada planta.

**4. Plantas, papelera y usuarios personalizados (Módulo 6)**
1. Abrir **6. Plantas y usuarios**: el Administrador Total gestiona plantas (incluyendo papelera de 30 días y restauración), mientras que cada encargado gestiona únicamente las cuentas de su planta.
2. Pulsar **+ Crear usuario personalizado**, definir credenciales/cargo/rango y marcar únicamente los **19 permisos** deseados por sección y control.

---

### Anexo B: Evidencias de entrevistas

Respaldo documental y fotográfico del trabajo de campo realizado en Zurite / Anta (entrevistas con Don Eloy Condori, Elvis y tercer actor de ruta/planta). Las dos fotografías de campo tomadas en la visita (cuaderno físico de acopio y tina quesera artesanal) se rescataron de `docs/INFORME_PROYECTO_LAT_CUSCO.docx.md` (sección 12.2) y se incluyen en la versión Word (`docs/INFORME_FINAL_LAT.docx`):

> **[INSERTAR EVIDENCIA VISUAL AQUÍ]**
> - **Qué mostrar:** Escaneos o fotografías legibles de las 3 fichas de entrevista firmadas (con fecha, lugar, rol, preguntas y respuestas textuales, completando los datos del tercer entrevistado).
> - **Herramienta recomendada:** Cámara fotográfica o escáner de fichas físicas de entrevista.
> - **Archivo/Ruta de referencia en el repo:** `docs/INFORME_PROYECTO_LAT_CUSCO.docx.md` (secciones 2.6.2 y 12.1–12.2) y sección 3.2 de `docs/INFORME_FINAL_LAT.md`.

---

### Anexo C: Capturas de pantalla del sistema

**Mockups y wireframes ya versionados en el repositorio (`docs/mockups/`):**
- `docs/mockups/mockup_1_portada_wireframe.png` y `docs/mockups/mockup_1_portada_desktop_bw.png`
- `docs/mockups/mockup_2_panel_acopio_wireframe.png` y `docs/mockups/mockup_2_panel_acopio_desktop_bw.png`
- `docs/mockups/mockup_3_control_sanitario_wireframe.png` y `docs/mockups/mockup_3_control_sanitario_desktop_bw.png`
- `docs/mockups/mockup_4_tina_receta_wireframe.png` y `docs/mockups/mockup_4_tina_receta_desktop_bw.png`
- `docs/mockups/mockup_5_movil_wireframe.png` y `docs/mockups/mockup_5_movil_smartphone_bw.png`

**Capturas a color en vivo del sistema actual:**

> **[INSERTAR EVIDENCIA VISUAL AQUÍ]**
> - **Qué mostrar:** Capturas a color en ejecución real de: (1) Portada pública en Español y en Quechua (*Runasimi*), (2) Pantalla de Login y Registro de nueva planta, (3) Dashboard completo con los 4 KPIs y las 3 gráficas Chart.js, (4) Módulo de Acopio por voz + modal *Editar datos*, (5) Módulo de Múltiples Tinas con curva térmica y receta, (6) Liquidación quincenal y libro `.xlsx` abierto en Microsoft Excel / LibreOffice Calc, (7) Matriz de permisos de usuario personalizado (`lucia.personal` viendo solo Calidad y Tinas) y (8) Vista móvil responsiva (`390×844`).
> - **Herramienta recomendada:** Navegador Chrome/Edge + DevTools (`F12`) + Microsoft Excel o LibreOffice Calc.
> - **Archivo/Ruta de referencia en el repo:** `resources/views/inicio.blade.php`, `resources/views/panel.blade.php`, `resources/views/panel/sections/*.blade.php` y `app/Services/ExcelExportService.php`.

---

### Anexo D: Diagramas adicionales

**D.1 Diagrama de secuencia: Ciclo completo del litro de leche (Acopio -> Calidad 74° -> Tina -> Excel `.xlsx`)**

```mermaid
sequenceDiagram
    actor Op as Operario / Recolector
    participant UI as Vista Blade + JS
    participant Ctrl as PanelController
    participant Svc as LacteoService / ExcelExportService
    participant DB as MariaDB / MySQL

    Op->>UI: Dicta por voz o usa botonera (+litros)
    UI->>Ctrl: POST /panel/acopio (@csrf)
    Ctrl->>DB: INSERT acopios (planta_id, uuid_offline)
    DB-->>Ctrl: Registro creado
    Ctrl-->>UI: Redirect con mensaje de exito

    Op->>UI: Clasifica calidad 74° (confirma modal JS)
    UI->>Ctrl: POST /panel/calidad/{id}
    Ctrl->>DB: UPDATE calidad y destino (queso_yogur o quesillo_4_dias)

    Op->>UI: Actualiza temperatura y litros en tina
    UI->>Ctrl: POST /panel/tina/{id}
    Ctrl->>Svc: evaluarTermica() + dosificarReceta()
    Svc-->>Ctrl: Estado termico + cuajo, sal y cultivo
    Ctrl->>DB: UPDATE lotes

    actor Cont as Contador / Administracion
    Cont->>UI: Solicita exportar balance Excel
    UI->>Ctrl: GET /panel/exportar-excel (permiso:finanzas.exportar)
    Ctrl->>Svc: liquidarQuincena() [3 queries] + generarBalanceXlsx()
    Svc->>DB: 3 consultas SQL agrupadas sin N+1
    DB-->>Svc: Totales por proveedor y planta
    Svc-->>Cont: Descarga balance_lat_*.xlsx (4 hojas)
```

![Figura 5. Diagrama de secuencia del ciclo completo del litro de leche y exportación Excel](imagenes/diagramas/05_secuencia_leche.png)

**D.2 Flujo de control de acceso granular (`CheckLatPermission` + aislamiento multi-planta)**

```mermaid
flowchart LR
    Req["Peticion HTTP a /panel/*"] --> Auth{"Usuario autenticado? (middleware auth)"}
    Auth -->|No| Redir["Redirect 302 a /login"]
    Auth -->|Si| Activo{"Cuenta activa? (activo == true)"}
    Activo -->|No| Deny1["HTTP 403 Forbidden"]
    Activo -->|Si| Perm{"Tiene permiso requerido? (CheckLatPermission)"}
    Perm -->|No| Deny2["HTTP 403 Forbidden"]
    Perm -->|Si| Scope["Aisla por planta_id y valida rango"]
    Scope --> Exec["Ejecuta accion en Controlador"]
```

![Figura 6. Flujo de control de acceso granular (CheckLatPermission) y aislamiento por planta](imagenes/diagramas/06_flujo_permisos.png)

---

### Anexo E: Enlace al repositorio Git

- **Repositorio oficial en GitHub:** https://github.com/KelmerOC/LAT
- **Rama principal auditada:** `main` (**42 commits** registrados entre `01/11/2026` y `26/11/2026`; último commit verificado: `d8fa4c3` — *Merge branch 'fix/auditoria-pagos-lotes-calidad' into main*).
- **Comandos de instalación local (XAMPP / Windows):**
  ```powershell
  git clone https://github.com/KelmerOC/LAT.git
  cd LAT
  composer install
  copy .env.example .env
  php artisan key:generate
  php artisan migrate:fresh --seed
  php artisan serve
  ```
- **Despliegue en contenedor Docker (Render):** Basado en `Dockerfile` (`php:8.4-apache`), `docker/000-default.conf` y `docker/start.sh`.

---

### Anexo F: Declaración de originalidad firmada

> **[INSERTAR EVIDENCIA VISUAL AQUÍ]**
> - **Qué mostrar:** Documento escaneado o fotografía nítida de la Declaración de Originalidad (sección 9.3) con las firmas manuscritas o digitales de los tres integrantes del equipo (Ortiz Gutierrez, Lovatón Casas y Obregon Choque).
> - **Herramienta recomendada:** Escáner de documentos o firma digital en PDF.
> - **Archivo/Ruta de referencia en el repo:** `docs/INFORME_FINAL_LAT.md` (sección 9.3).

---

### Anexo G: Evidencias de optimización de consultas

Sentencias SQL preparadas sobre `lat_compacto_db` para ejecutar en MariaDB/MySQL y documentar el plan de ejecución `EXPLAIN` (antes y después):

```sql
-- 1. Consulta optimizada con rango directo BETWEEN usando idx_acopios_planta_fecha
EXPLAIN SELECT proveedor_id, SUM(litros) AS total_litros
FROM acopios
WHERE planta_id = 1 AND fecha BETWEEN '2026-11-01' AND '2026-11-30'
GROUP BY proveedor_id;

-- 2. Comparativa con DATE(fecha) (evidencia por que whereDate degrada el uso del indice)
EXPLAIN SELECT proveedor_id, SUM(litros) AS total_litros
FROM acopios
WHERE planta_id = 1 AND DATE(fecha) >= '2026-11-01' AND DATE(fecha) <= '2026-11-30'
GROUP BY proveedor_id;

-- 3. Consulta agrupada de adelantos pendientes usando idx_liquidaciones_prov_descontado
EXPLAIN SELECT proveedor_id, SUM(monto) AS total_adelantos
FROM liquidaciones
WHERE planta_id = 1 AND descontado = 0 AND tipo IN ('adelanto_efectivo', 'adelanto_queso')
GROUP BY proveedor_id;
```

> **[INSERTAR EVIDENCIA VISUAL AQUÍ]**
> - **Qué mostrar:** Capturas de pantalla con la salida real de los 3 comandos `EXPLAIN` en MariaDB/MySQL (mostrando `type`, `possible_keys`, `key`, `rows` y `Extra`) y captura de tiempos de respuesta en la pestaña `Network` de DevTools al cargar `/panel` y descargar `/panel/exportar-excel`.
> - **Herramienta recomendada:** Consola MySQL de XAMPP (`C:\xampp\mysql\bin\mysql.exe`) o phpMyAdmin + Chrome/Edge DevTools (`F12 -> Network`).
> - **Archivo/Ruta de referencia en el repo:** `app/Services/LacteoService.php` y `database/migrations/2026_01_01_000001_crear_tablas_lat_compacto.php`.

---

### Anexo H: Evidencias de pruebas de seguridad web

Comandos de validación para ejecutar en terminal local tras `composer install`:

```powershell
# 1. Suite automatizada PHPUnit (103 tests / 1,114 aserciones: RBAC, OWASP, flujo, FIFO, Excel e i18n)
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit

# 2. Auditoria de vulnerabilidades conocidas en dependencias PHP y Node
composer audit
npm audit

# 3. Prueba de proteccion CSRF sin token (debe responder HTTP 419 Page Expired)
curl -i -X POST http://127.0.0.1:8000/login -d "login=donjusto&password=secreto123"
```

> **[INSERTAR EVIDENCIA VISUAL AQUÍ]**
> - **Qué mostrar:** Capturas de pantalla de: (1) `vendor/bin/phpunit` con los **103 tests / 1,114 aserciones** en verde y `tests/e2e/visual-standards.cjs`, (2) salida de `composer audit` y `npm audit`, (3) respuesta **HTTP 419** al enviar `POST /login` sin token CSRF, (4) rechazo del payload SQLi (`' OR '1'='1' --`) en el login, y (5) escape del payload XSS (`<script>alert('XSS')</script>`) mostrado como texto inofensivo en las tablas financieras.
> - **Herramienta recomendada:** Terminal PowerShell + `curl` + Navegador con DevTools (`F12 -> Network / Elements`).
> - **Archivo/Ruta de referencia en el repo:** `tests/Feature/LatFuncionalidadesTest.php`, `app/Http/Middleware/CheckLatPermission.php`, `app/Http/Controllers/AuthController.php` y `resources/views/panel/sections/finanzas.blade.php`.
