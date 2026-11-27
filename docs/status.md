# Estado del Proyecto · LAT Compacto

**Proyecto:** Plataforma Web de Gestión Láctea Comunitaria (LAT)  
**Institución:** Universidad Continental · Asignatura: Programación Web (Cusco)  
**Stack:** PHP 8.3+ (`^8.3`, runtime local PHP 8.5, Docker `php:8.4-apache`) · Laravel 13 (`^13.17`) · MariaDB / MySQL (local/producción) · SQLite (pruebas)  
**Última actualización:** 2026-11-26

---

## 1. Resumen del Sistema

LAT es una plataforma web comunitaria sin comisiones (`S/ 0.00`) orientada a plantas queseras familiares, asociaciones ganaderas y centros de acopio rural de Cusco Provincia (Anta, Chinchero y Cachimayo). Digitaliza el flujo completo **`1. Acopio → 2. Calidad Sanitaria → 3. Tinas y Receta → 4. Liquidación y Ventas estilo Excel → 5. Estadísticas e Informes de Planta`** con gobierno multi-planta, notificaciones segmentadas, modo offline y soporte trilingüe (`es`, `qu`, `en`).

### Capacidades Operativas Clave
- **Gobierno de Roles y Aislamiento Multi-planta:**
  - El **Administrador Total** (`administracion`) visualiza toda la red, opera en Liquidación, Estadísticas y Plantas/Usuarios, y mantiene **deshabilitadas por defecto** las escrituras en Acopio, Calidad y Tinas; puede activarlas/desactivarlas temporalmente en sesión desde el topbar (`#btn-toggle-modo-operativo`) con confirmación modal.
  - Cada **Administrador / Encargado de planta** (`encargado_planta`) está aislado estrictamente a su planta (`planta_id`), gestiona solo usuarios propios y reporta su actividad mediante bitácora e informes en texto plano (automático cada 24h + envío manual) visibles/descargables exclusivamente por el Administrador Total en `5. Estadísticas`.
- **Notificaciones Segmentadas y Sombreado Verde:**
  - Alertas de **Tinas / Calidad → Tinas** llegan únicamente a `encargado_planta` y `control_tina` (o cuentas personalizadas con `tinas.operar`).
  - Comunicaciones entre **Contador y Administradores** (envío/revisión de reportes Excel y avisos directos) llegan en **color sombreado verde** (`.is-color-verde`).
  - Alertas de **Informes de planta** llegan exclusivamente al Administrador Total en alcance global.
- **Área de Liquidación estilo Excel con Tablas Separadas (`#sec-quincena`):**
  - **Liquidación quincenal de proveedores:** grilla interactiva con barra `fx`, fórmulas vivas (`=D×E`, `=MAX(0, F-G-H)`, `=SUM(...)`), edición inline con opciones preestablecidas (`PUT /panel/finanzas/liquidacion-grid`), filtros de fecha/proveedor/sector/estado y exportación `.xlsx` exclusiva (`?solo=liquidacion`).
  - **Ventas de productos** y **Sueldos, jornales y pagos operativos (sin DNI · Ley N.° 29733):** tablas independientes estilo Excel (eliminando la tabla combinada de movimientos recientes), con criterios de columnas propios, fórmulas vivas, filtros y descargas `.xlsx` exclusivas (`?solo=ventas`, `?solo=sueldos`).
  - **Bandeja Excel ↔ Contador:** confirmación modal en letras negritas, tipos alineados a las tablas separadas y notificación sombreada en verde.
- **Anuncio QR Público en Landing Page (`/`):** eliminado el generador QR interno del panel (`admin.qr`) y adaptado a la portada pública como anuncio **"Puedes registrarte ahora"** con QR SVG offline hacia `/registrarse`.

---

## 2. Arquitectura y Stack Tecnológico

| Capa | Tecnología / Archivo | Implementación actual |
| :--- | :--- | :--- |
| **Backend Core** | PHP `^8.3` / Laravel `^13.17` | MVC con servicios dedicados (`LacteoService`, `TinaWorkflowService`, `InformePlantaService`, `ExcelExportService`) y controladores (`PanelController`, `AdminController`, `AuthController`, `HomeController`). |
| **Persistencia** | MariaDB / MySQL (`lat_compacto_db`) | 12 tablas (`plantas`, `tinas`, `users`, `proveedores`, `acopios`, `lotes`, `liquidaciones`, `reportes_excel`, `qr_accesos` legacy, `notificaciones_panel`, `actividades_planta`, `informes_actividad_planta`) con índices compuestos (techo N+1 `<= 42` queries en `GET /panel`). |
| **Control de Acceso** | RBAC granular (`App\Models\User`, `CheckLatPermission`) | 6 cargos con modo supervisión por defecto para `administracion`, interruptor de sesión `admin_modo_operativo` y aislamiento estricto de `encargado_planta` a su propia planta. |
| **Interfaz del Panel** | Blade + Sidebar + Topbar + Chart.js | Sistema visual **Swiss-Grid (`DESIGN.md`)** servido desde `public/css/` y `public/js/` (sin `@vite`). Selector de idioma único en barra lateral (`.dash-lang-row`), interruptor operativo en topbar, grillas Excel (`public/js/lat-finanzas-grid.js`) y alertas verdes (`public/js/lat-notificaciones.js`). |
| **PWA / Offline** | `public/sw.js` (`v6`) + `public/js/lat-offline.js` + `public/js/lat-notificaciones.js` | Network-First para HTML, Cache-First para assets estáticos (excluyendo Excel, `/idioma`, `/panel/notificaciones`, `/estadisticas/informes` y `/admin/modo-operativo`), cola offline por `uuid_offline` y polling cada 12 s. |
| **Exportación Contable** | `App\Services\ExcelExportService` | Generador OOXML nativo (`.xlsx`) con fórmulas Excel por fila y totales `SUM`/`SUMIF`: exportaciones individuales (`solo=liquidacion|ventas|sueldos`) y libro completo de 4 hojas. |
| **i18n Trilingüe** | `public/js/lat-i18n.js` (815 claves) + `lang/{es,qu,en}/lat.php` (170 claves) | Paridad completa `es === qu === en` (0 claves faltantes en Blade/JS) con persistencia en sesión y cookie `lat_locale`. |

---

## 3. Matriz de Rutas y Módulos

| Módulo | Rutas Web | Controlador / Middleware | Estado | Funcionalidad |
| :--- | :--- | :--- | :---: | :--- |
| **Portal Público** | `/`, `/idioma/{lang}` | `HomeController@index`, `AuthController@cambiarIdioma` | Implementado | Portada institucional, anuncio QR público **"Puedes registrarte ahora"** hacia `/registrarse` y selector trilingüe. |
| **Autenticación y Registro** | `/login`, `/registrarse`, `/logout` | `AuthController` (`throttle:5,1`) | Implementado | Acceso por usuario/correo y registro público aislado por planta (`PLT-XX` + `TINA-01`/`VAT-01`). |
| **Dashboard y Notificaciones** | `/panel`, `/panel/notificaciones/poll`, `/panel/notificaciones/leer`, `/panel/notificaciones/contable`, `/panel/admin/modo-operativo` | `PanelController` (`auth`) | Implementado | KPIs, gráficas Chart.js, interruptor operativo del Admin Total, notificaciones segmentadas por rol y avisos verdes contables. |
| **M1: Acopio y Productores** | `/panel/acopio`, `/panel/acopio/sync`, `/panel/acopio/{id}`, `/panel/proveedor*` | `PanelController` (`permiso:acopio.*`) | Implementado | Dictado por voz automático, estado inicial `pendiente`, edición/eliminación con confirmación y cola offline. |
| **M2: Control Sanitario** | `/panel/calidad/{id}` | `PanelController@evaluarCalidad` (`permiso:calidad.evaluar`) | Implementado | 3 decisiones confirmadas coloreadas (Aprobar/Desviar/Descartar), bloqueo si `lote_id !== null` y alerta exclusiva hacia Encargado de Planta y Control de Tina. |
| **M3: Múltiples Tinas y Lotes** | `/panel/tinas*`, `/panel/tinas/recetas-base`, `/panel/tinas/{tinaId}/lote`, `/panel/tina/{id}` | `PanelController` + `TinaWorkflowService` (`permiso:tinas.*`) | Implementado | Selección múltiple de acopios filtrables, recetas/cronómetro editables, tipo libre, prefijo `TINA-`/`VAT-` y alertas segmentadas. |
| **M4: Liquidación, Ventas y Sueldos** | `/panel/finanzas/liquidacion-grid`, `/panel/movimiento*`, `/panel/exportar-excel`, `/panel/reportes-excel*` | `PanelController` (`permiso:finanzas.*`) | Implementado | 3 tablas estilo Excel independientes con fórmulas, filtros, descargas `.xlsx` exclusivas y bandeja Excel ↔ Contador con confirmación en negritas + notificación verde. |
| **M5: Estadísticas e Informes** | `/panel` (`sec-estadisticas`), `/panel/informes-planta/enviar`, `/panel/estadisticas/informes/{id}/descargar` | `PanelController` + `InformePlantaService` | Implementado | Rankings, analítica sanitaria, informes automáticos 24h (`lat:generar-informes-planta`) y manuales por planta, historial exclusivo Admin Total con filtros y descarga `.txt`. |
| **M6: Administración** | `/panel/admin/usuarios*`, `/panel/admin/plantas*` | `AdminController` (`permiso:admin.*`) | Implementado | CRUD de plantas con papelera 30d y gestión de usuarios estrictamente aislada por planta (sin generador QR interno). |

---

## 4. Base de Datos y Datos Sembrados

- **Migraciones (`database/migrations/`):**
  1. `2026_01_01_000001_crear_tablas_lat_compacto.php`
  2. `2026_01_01_000002_agregar_plantas_tinas_y_ventas.php`
  3. `2026_11_18_205518_agregar_permisos_personalizados_usuarios.php`
  4. `2026_11_25_153704_agregar_papelera_plantas.php`
  5. `2026_11_25_153706_crear_reportes_excel_y_qr_accesos.php`
  6. `2026_11_25_153707_optimizar_indices_lat_compacto.php`
  7. `2026_11_26_184041_actualizar_flujo_tinas_recetas_y_notificaciones.php`
  8. `2026_11_26_210000_actualizar_roles_finanzas_notificaciones_e_informes.php` (segmentación/tema verde en `notificaciones_panel`, `liquidaciones` ampliada para ventas/sueldos con fórmulas, tablas `actividades_planta` e `informes_actividad_planta`, limpieza de `admin.qr` y alcance aislado de `encargado_planta`).

---

## 5. Estado de Calidad, Pruebas y Despliegue

- **PHPUnit (`tests/Unit` + `tests/Feature`):** **92 tests / 967 aserciones — 100% PASS** (incluyendo `LatRolesFinanzasInformesTest`, `LatAuditoriaFinalTest`, `LatSeguridadOwaspTest`, `LatObservacionesPanelTest`, `LatFlujoTinasNotificacionesTest`, `LatFuncionalidadesTest`, `LacteoServiceTest`). Presupuesto N+1 `<= 42` consultas en `GET /panel` validado.
- **Formato de código (Laravel Pint):** `php vendor/bin/pint --dirty --format agent` — **PASS**.
- **Playwright E2E (`tests/e2e/visual-standards.cjs`):** **294/294 checks PASS (`TODO OK`)** en `desktop` (`1280×800`), `movil` (`390×844`) y `movil-compacto` (`320×568`), con 0 desbordes y 0 errores JS/consola.
- **Scheduler (`routes/console.php`):** `lat:purgar-plantas` y `lat:generar-informes-planta` programados diariamente (`daily()`).
