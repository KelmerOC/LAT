# Estado del Proyecto · LAT Compacto

**Proyecto:** Plataforma Web de Gestión Láctea Comunitaria (LAT)  
**Institución:** Universidad Continental · Asignatura: Programación Web (Cusco)  
**Stack:** PHP 8.3+ (`^8.3`, Docker `php:8.4-apache`) · Laravel 13 (`^13.17`) · MariaDB / MySQL (local/producción) · SQLite (pruebas)

---

## 1. Resumen del Sistema

LAT es una plataforma web sin comisiones (`S/ 0.00`) orientada a plantas queseras familiares, asociaciones ganaderas y centros de acopio rural de Cusco Provincia (Anta, Chinchero y Cachimayo). Centraliza el registro diario de acopio, el control sanitario en recepción, el seguimiento térmico de múltiples tinas y la liquidación quincenal con exportación a Excel.

### Problemas operativos que resuelve
- **Pérdida de registros en campo:** Reemplaza los cuadernos físicos expuestos a humedad y errores de transcripción por captura digital con botón de dictado por voz (Web Speech API) y botonera táctil.
- **Trazabilidad sanitaria sin merma innecesaria:** Clasifica la leche en recepción mediante prueba de alcohol a 74°; la leche con acidez se deriva a tinas de quesillo fresco de 4 días en lugar de desecharse.
- **Control térmico y dosificación uniforme:** Calcula insumos por volumen en tina (cuajo `2.5 ml`, sal `1.8 kg`, cultivo `1.0 g` por cada `100 L`) y monitorea el enfriamiento desde pasteurización (`72 °C`) hasta inoculación (`36–40 °C`).
- **Cumplimiento de minimización de datos (Ley N.° 29733):** Permite registrar jornales de ayuda diaria en planta sin exigir DNI.

---

## 2. Arquitectura y Stack Tecnológico

| Capa | Tecnología / Archivo | Implementación actual |
| :--- | :--- | :--- |
| **Backend Core** | PHP `^8.3` / Laravel `^13.17` | Arquitectura MVC con capa de servicios (`App\Services\LacteoService`, `App\Services\ExcelExportService`). |
| **Persistencia** | MariaDB / MySQL (`lat_compacto_db`) | 7 tablas principales (`plantas`, `tinas`, `users`, `proveedores`, `acopios`, `lotes`, `liquidaciones`) con índices compuestos. |
| **Control de Acceso** | RBAC granular (`App\Models\User`, `CheckLatPermission`) | 6 cargos predefinidos (`administracion`, `encargado_planta`, `recolector_ruta`, `control_tina`, `contador_externo`, `personalizado`), rangos y matriz de permisos por sección/acción (`User::PERMISOS_CATALOGO`). |
| **Interfaz del Panel** | Blade + Sidebar + Topbar + Chart.js | Layout operativo con menú lateral (`resources/views/panel/partials/sidebar.blade.php`), barra superior (`topbar.blade.php`), modales de edición y 4 gráficas interactivas con `public/js/chart.umd.min.js`. |
| **Exportación Contable** | `App\Services\ExcelExportService` | Generador OOXML nativo (`.xlsx`) con 4 hojas: *Liquidación Quincenal* (con fórmulas `SUM`), *Detalle Acopios*, *Movimientos y Ventas* y *Comparativa Plantas*. |
| **Interacción e i18n** | Web Speech API + `public/js/lat-i18n.js` | Dictado por voz (`es-PE`), síntesis confirmatoria y selector trilingüe dinámico: Español (`es`), Quechua (`qu`) e Inglés (`en`). |

---

## 3. Matriz de Rutas y Módulos

| Módulo | Rutas Web | Controlador / Middleware | Estado | Funcionalidad |
| :--- | :--- | :--- | :---: | :--- |
| **Portal Público** | `/`, `/idioma/{lang}` | `HomeController@index`, `AuthController@cambiarIdioma` | Implementado | Portada institucional, flujo operativo, selector trilingüe y acceso al panel. |
| **Autenticación y Registro** | `/login`, `/registrarse`, `/logout` | `AuthController` | Implementado | Inicio de sesión por usuario o correo, registro público que crea planta, tina inicial y cuenta administradora. |
| **Dashboard General** | `/panel` | `PanelController@index` (`auth`) | Implementado | KPIs con comparación de período, 3 gráficas Chart.js (acopio, ventas/gastos, moldes), ranking de red multi-planta y actividad reciente. |
| **Selector Multi-planta** | `/panel/cambiar-planta` | `PanelController@cambiarPlanta` (`permiso:admin.plantas`) | Implementado | Cambio de planta activa en sesión para supervisión consolidada. |
| **M1: Acopio y Productores** | `/panel/acopio`, `/panel/acopio/{id}`, `/panel/proveedor` | `PanelController` (`permiso:acopio.*`) | Implementado | Registro por voz o botonera táctil, edición/eliminación de entregas y alta/edición de ganaderos. |
| **M2: Control Sanitario** | `/panel/calidad/{id}` | `PanelController@evaluarCalidad` (`permiso:calidad.evaluar`) | Implementado | Prueba de alcohol 74° para aprobar hacia tina principal o derivar a quesillo fresco de 4 días. |
| **M3: Múltiples Tinas y Lotes** | `/panel/tinas`, `/panel/tinas/{id}`, `/panel/tinas/{tinaId}/lote`, `/panel/tina/{id}` | `PanelController` (`permiso:tinas.*`) | Implementado | Administración de múltiples tinas por planta, apertura de lotes, control térmico (`72 °C` → `36–40 °C`) y dosificación automática. |
| **M4: Liquidaciones y Ventas** | `/panel/movimiento`, `/panel/movimiento/{id}` | `PanelController` (`permiso:finanzas.*`) | Implementado | Liquidación quincenal sin N+1, registro/edición de ventas de producto, adelantos, ayuda diaria sin DNI y gastos operativos. |
| **M5: Estadísticas y Excel** | `/panel` (`sec-estadisticas`), `/panel/exportar-excel` | `PanelController@exportarExcel` (`permiso:finanzas.exportar`) | Implementado | Gráfica de calidad sanitaria, comparativa entre plantas, top productores y descarga de libro `.xlsx` de 4 hojas (incluye alias `/panel/exportar-csv`). |
| **M6: Administración** | `/panel/admin/usuarios`, `/panel/admin/plantas` | `AdminController` (`permiso:admin.*`) | Implementado | Gestión de plantas y creación/edición de usuarios con rango y permisos granulares personalizados. |

---

## 4. Base de Datos y Datos Sembrados

- **Migraciones (`database/migrations/`):**
  1. `2026_01_01_000001_crear_tablas_lat_compacto.php` (`users`, `proveedores`, `acopios`, `lotes`, `liquidaciones` e índices).
  2. `2026_01_01_000002_agregar_plantas_tinas_y_ventas.php` (`plantas`, `tinas`, relaciones `planta_id`/`tina_id`, `cantidad_unidades` y tipo `venta_producto`).
  3. `2026_11_18_205518_agregar_permisos_personalizados_usuarios.php` (`rango`, `permisos`, `creado_por`).
- **Semillero (`database/seeders/DatabaseSeeder.php`):**
  - 3 plantas (`PLT-01` San Borreal, `PLT-02` Valle de Chinchero, `PLT-03` Asociación Cachimayo).
  - Usuarios operativos y personalizados con contraseña `secreto123` (`donjusto@sanborreal.pe`, `carlos.anta`, `mateo.ruta`, `sabina.tina`, `contador.marco`, `lucia.personal`, entre otros).
  - Tinas, productores, entregas diarias, lotes térmicos y movimientos financieros/ventas iniciales.

---

## 5. Control de Versiones, Despliegue y Pruebas

- **Repositorio Git:** Inicializado y activo con remoto `origin` en `https://github.com/KelmerOC/LAT.git`.
- **Contenedor Docker:** Configurado en `Dockerfile`, `docker/000-default.conf` y `docker/start.sh` sobre `php:8.4-apache` (puerto `10000`) con exclusión de archivos internos en `.dockerignore`.
- **Pruebas automatizadas (`phpunit.xml`):** Suites `Unit` (`tests/Unit`) y `Feature` (`tests/Feature`, incluyendo `LatFuncionalidadesTest.php`) configuradas con SQLite en memoria, además de verificación visual E2E en `tests/e2e/visual-standards.cjs`.
- **Documentación visual:** Mockups y wireframes archivados en `docs/mockups/`.
