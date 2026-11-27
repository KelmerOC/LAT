# Estado del Proyecto · LAT Compacto

**Proyecto:** Plataforma Web de Gestión Láctea Comunitaria (LAT)  
**Institución:** Universidad Continental · Asignatura: Programación Web (Cusco)  
**Stack:** PHP 8.3+ (`^8.3`, runtime local PHP 8.5, Docker `php:8.4-apache`) · Laravel 13 (`^13.17`) · MariaDB / MySQL (local/producción) · SQLite (pruebas)  
**Última actualización:** 2026-11-26

---

## 1. Resumen del Sistema

LAT es una plataforma web comunitaria sin comisiones (`S/ 0.00`) orientada a plantas queseras familiares, asociaciones ganaderas y centros de acopio rural de Cusco Provincia (Anta, Chinchero y Cachimayo). Digitaliza el flujo completo **`1. Acopio → 2. Calidad Sanitaria → 3. Tinas y Receta → 4. Liquidación y Excel`** con trazabilidad por entrega, alertas en vivo, modo offline y soporte trilingüe (`es`, `qu`, `en`).

### Capacidades Operativas Clave
- **Acopio por voz automático y editable (`recolector_ruta` habilitado):** Dictado por voz (Web Speech API) con envío automático al detectar productor y litros válidos (`1..5000`), aviso inline confirmatorio (`#aviso-acopio-voz`), edición (incluyendo `frase_dictada`) y eliminación con confirmación modal (`#modal-confirmacion-lat`), más cola offline idempotente en `localStorage`. Todo acopio nuevo nace como `pendiente` hasta pasar por Control Sanitario.
- **Control Sanitario con 3 decisiones confirmadas y alertas a Tinas:** Prueba de alcohol 74° para **Aprobar** (`buena` → queso/yogur), **Desviar a quesillo** (`acida` → quesillo 4 días) o **Descartar / Eliminar por completo** (`descartada`), cada una con confirmación en negrita coloreada (verde / ámbar / rojo), timestamp `evaluado_en` y notificación directa hacia Tinas.
- **Tinas trazables con selección múltiple de leche, recetas editables y cronómetro en vivo:** El modal compartido `#modal-iniciar-tina` permite filtrar por calidad (`aprobada`/`quesillo`/`todas`), día/fecha y orden, y seleccionar múltiples entregas disponibles (`lote_id IS NULL`) sumando litros reales contra la capacidad de la tina. Incluye recetas base editables por planta (`plantas.recetas_base`), proporciones (`cuajo_ml`, `sal_kg`, `cultivo_g`) y cronómetro (`duracion_minutos`) editables al iniciar y durante el lote, `tipo` de uso libre con sugerencias (`<datalist>`) y prefijo de código según idioma (`TINA-` en `es`/`qu`, `VAT-` en `en`).
- **Bandeja de notificaciones + alertas en tiempo real:** Notificaciones persistentes (`notificaciones_panel`) al clasificar leche, cumplirse el cronómetro o finalizar una tina, entregadas al abrir el panel y en vivo mediante polling cada 12 s (`public/js/lat-notificaciones.js`).

---

## 2. Arquitectura y Stack Tecnológico

| Capa | Tecnología / Archivo | Implementación actual |
| :--- | :--- | :--- |
| **Backend Core** | PHP `^8.3` / Laravel `^13.17` | MVC con servicios dedicados (`LacteoService`, `TinaWorkflowService`, `ExcelExportService`) y controladores (`PanelController`, `AdminController`, `AuthController`, `QrAccesoController`). |
| **Persistencia** | MariaDB / MySQL (`lat_compacto_db`) | 10 tablas (`plantas`, `tinas`, `users`, `proveedores`, `acopios`, `lotes`, `liquidaciones`, `reportes_excel`, `qr_accesos`, `notificaciones_panel`) con índices compuestos (`40` queries en `GET /panel`, techo `<= 42`). |
| **Control de Acceso** | RBAC granular (`App\Models\User`, `CheckLatPermission`) | 6 cargos (`administracion`, `encargado_planta`, `recolector_ruta` con `acopio.editar`, `control_tina`, `contador_externo`, `personalizado`) y matriz de permisos por sección/acción. |
| **Interfaz del Panel** | Blade + Sidebar + Topbar + Chart.js | Sistema visual **Swiss-Grid (`DESIGN.md`)** servido desde `public/css/` y `public/js/` (sin `@vite`). Selector de idioma único en barra lateral (`.dash-lang-row`), bandeja de notificaciones y alertas vivas en topbar, modales apilables (`#modal-confirmacion-lat`, `#modal-iniciar-tina`, `#modal-recetas-base`) e `.input-prefix-group` (`TINA-`/`VAT-`/`PLT-`). |
| **PWA / Offline** | `public/sw.js` (`v5`) + `public/js/lat-offline.js` + `public/js/lat-notificaciones.js` | Network-First para vistas HTML, Cache-First para assets estáticos (excluyendo descargas Excel, `/acceso-qr`, `/idioma` y `/panel/notificaciones`), cola offline por `uuid_offline` y polling de alertas vivas + cronómetros. |
| **Exportación Contable** | `App\Services\ExcelExportService` | Generador OOXML nativo (`.xlsx`) de 4 hojas con fórmulas `SUM`/`SUMIF`, `autoFilter` y bandeja interna para contadores (`reportes_excel`). |
| **i18n Trilingüe** | `public/js/lat-i18n.js` (673 claves) + `lang/{es,qu,en}/lat.php` (167 claves) | Paridad completa en Español (`es`), Quechua Cusqueño (`qu`) e Inglés (`en`) con persistencia en sesión y cookie `lat_locale`. |

---

## 3. Matriz de Rutas y Módulos

| Módulo | Rutas Web | Controlador / Middleware | Estado | Funcionalidad |
| :--- | :--- | :--- | :---: | :--- |
| **Portal Público** | `/`, `/idioma/{lang}` | `HomeController@index`, `AuthController@cambiarIdioma` | Implementado | Portada institucional, flujo operativo, selector trilingüe persistente y acceso al panel. |
| **Autenticación y Registro** | `/login`, `/registrarse`, `/logout` | `AuthController` (`throttle:5,1`) | Implementado | Acceso por usuario/correo y registro público con planta `PLT-XX` y tina inicial (`TINA-01`/`VAT-01`). |
| **Dashboard y Notificaciones** | `/panel`, `/panel/notificaciones/poll`, `/panel/notificaciones/leer` | `PanelController` (`auth`) | Implementado | KPIs, 3 gráficas Chart.js, ranking multi-planta, bandeja de notificaciones y polling en vivo + cronómetros. |
| **M1: Acopio y Productores** | `/panel/acopio`, `/panel/acopio/sync`, `/panel/acopio/{id}`, `/panel/proveedor` | `PanelController` (`permiso:acopio.*`) | Implementado | Dictado por voz automático con aviso inline, estado inicial `pendiente`, edición (`frase_dictada`) y eliminación con confirmación (`recolector_ruta` habilitado), filtros y cola offline. |
| **M2: Control Sanitario** | `/panel/calidad/{id}` | `PanelController@evaluarCalidad` (`permiso:calidad.evaluar`) | Implementado | 3 decisiones confirmadas con aviso en negrita coloreado (Aprobar verde, Desviar ámbar, Descartar/Eliminar rojo), bloqueo si ya está en lote y notificación hacia Tinas. |
| **M3: Múltiples Tinas y Lotes** | `/panel/tinas*`, `/panel/tinas/recetas-base`, `/panel/tinas/{tinaId}/lote`, `/panel/tina/{id}` | `PanelController` + `TinaWorkflowService` (`permiso:tinas.*`) | Implementado | Aviso inline de leche disponible, modal `#modal-iniciar-tina` con selección múltiple de acopios filtrables, recetas base y proporciones/cronómetro editables, tipo libre, prefijo `TINA-`/`VAT-` y notificación al finalizar lote. |
| **M4: Liquidaciones y Ventas** | `/panel/movimiento*`, `/panel/reportes-excel*` | `PanelController` (`permiso:finanzas.*`) | Implementado | Liquidación quincenal sin N+1, movimientos/ventas/jornal sin DNI (Ley 29733) y bandeja de reportes Excel. |
| **M5: Estadísticas y Excel** | `/panel` (`sec-estadisticas`), `/panel/exportar-excel` | `PanelController@exportarExcel` (`permiso:finanzas.exportar`) | Implementado | Analítica de calidad/métodos, comparativa de plantas y descarga `.xlsx` de 4 hojas. |
| **M6: Administración y QR** | `/panel/admin/usuarios*`, `/panel/admin/plantas*`, `/panel/admin/qr*`, `/acceso-qr/{token}` | `AdminController`, `QrAccesoController` (`permiso:admin.*`) | Implementado | CRUD de plantas con prefijo fijo `PLT-` y papelera 30d, usuarios con matriz granular y acceso móvil QR. |

---

## 4. Base de Datos y Datos Sembrados

- **Migraciones (`database/migrations/`):**
  1. `2026_01_01_000001_crear_tablas_lat_compacto.php`
  2. `2026_01_01_000002_agregar_plantas_tinas_y_ventas.php`
  3. `2026_11_18_205518_agregar_permisos_personalizados_usuarios.php`
  4. `2026_11_25_153704_agregar_papelera_plantas.php`
  5. `2026_11_25_153706_crear_reportes_excel_y_qr_accesos.php`
  6. `2026_11_25_153707_optimizar_indices_lat_compacto.php`
  7. `2026_11_26_184041_actualizar_flujo_tinas_recetas_y_notificaciones.php` (`acopios.lote_id`/`evaluado_en`, `tinas.tipo` `string(80)`, `lotes.receta_nombre`/`duracion_minutos`/`iniciado_en`/`alerta_fin_enviada`, `plantas.recetas_base`, `notificaciones_panel` y permiso `acopio.editar` para `recolector_ruta`).
- **Semillero (`database/seeders/DatabaseSeeder.php`):** 3 plantas (`PLT-01..03`), usuarios operativos/personalizados (`secreto123`), tinas, productores, entregas con `evaluado_en`, lotes activos con cronómetro e historial de 6 días.

---

## 5. Estado de Calidad, Pruebas y Despliegue

- **PHPUnit (`tests/Unit` + `tests/Feature`):** **86 tests / 821 aserciones — 100% PASS** (`LacteoServiceTest`, `LatFuncionalidadesTest`, `LatAuditoriaFinalTest`, `LatSeguridadOwaspTest`, `LatObservacionesPanelTest`, `LatFlujoTinasNotificacionesTest`). Presupuesto N+1: **40 consultas** en `GET /panel` (`<= 42`).
- **Formato de código (Laravel Pint):** `php vendor/bin/pint --dirty --format agent` — **PASS**.
- **Playwright E2E (`tests/e2e/visual-standards.cjs`):** **266/266 checks PASS (`TODO OK`)** en `desktop` (`1280×800`), `movil` (`390×844`) y `movil-compacto` (`320×568`), con 0 desbordes y 0 errores JS/consola.
- **Despliegue y VCS:** Remoto `origin` en `https://github.com/KelmerOC/LAT.git`; Docker configurado en `Dockerfile`, `docker/000-default.conf` y `docker/start.sh` (`php:8.4-apache`, puerto `10000`).
