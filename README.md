# LAT Compacto · Plataforma de Gestión Láctea Comunitaria

Sistema web desarrollado en **Laravel 13** y **PHP 8.3+** para la gestión operativa de plantas queseras familiares, asociaciones ganaderas y centros de acopio rural en Cusco Provincia.

---

## 1. Arquitectura y Módulos Principales

- **Stack Tecnológico:** Laravel 13 (`^13.17`), PHP `^8.3` (compatible con `php:8.4-apache` en Docker y XAMPP local), MariaDB/MySQL en producción y SQLite en memoria para pruebas con PHPUnit.
- **Arquitectura Modular:** Controladores MVC (`HomeController`, `AuthController`, `PanelController`, `AdminController`), capa de servicios (`LacteoService`, `ExcelExportService`), middleware de idioma (`SetLocaleMiddleware`) y control de permisos (`CheckLatPermission`).
- **Interfaz Operativa (Sidebar + Dashboard Chart.js):**
  - **Dashboard general (`/panel`):** KPIs comparativos contra el período anterior, 3 gráficas interactivas con Chart.js (`public/js/chart.umd.min.js`), actividad reciente y ranking multi-planta.
  - **Módulo 1 · Acopio por voz y tacto:** Dictado mediante Web Speech API (`es-PE`), síntesis de confirmación, botonera táctil rápida, edición/eliminación de entregas y gestión de ganaderos (`/panel/acopio`, `/panel/proveedor`).
  - **Módulo 2 · Control sanitario (74°):** Evaluación en recepción por prueba de alcohol a 74°; la leche óptima pasa a tina principal y la leche con acidez se deriva a quesillo fresco de 4 días (`/panel/calidad/{id}`).
  - **Módulo 3 · Múltiples tinas y receta:** Gestión de múltiples tinas por planta (`Planta`, `Tina`, `Lote`), control de curva térmica (`72 °C` hasta `36–40 °C`) y dosificación automática de insumos (`2.5 ml` de cuajo, `1.8 kg` de sal y `1.0 g` de cultivo por cada `100 L`).
  - **Módulo 4 · Liquidación quincenal y ventas:** Cálculo quincenal sin consultas N+1, registro de ventas, adelantos, gastos operativos y ayuda diaria sin DNI (Ley N.° 29733).
  - **Módulo 5 · Estadísticas y exportación Excel (`.xlsx`):** Analítica de calidad, comparativa entre plantas, top productores y descarga de libro `.xlsx` real de 4 hojas mediante `ExcelExportService` (`/panel/exportar-excel`).
  - **Módulo 6 · Administración multi-planta y RBAC granular:** Alta y edición de plantas, cambio de planta activa y gestión de usuarios por cargo predefinido, rango y matriz de permisos granulares por sección/control (`/panel/admin/usuarios`, `/panel/admin/plantas`).
- **Internacionalización Trilingüe:** Cambio dinámico entre Español (`es`), Quechua (`qu`) e Inglés (`en`) mediante `/idioma/{lang}` y `public/js/lat-i18n.js`.

---

## 2. Rutas Principales

- `/` — Portal público institucional
- `/login` — Inicio de sesión por usuario o correo electrónico
- `/registrarse` — Registro público de nueva planta, tina inicial y usuario administrador
- `/panel` — Panel operativo principal (requiere autenticación)
- `/panel/exportar-excel` — Descarga de balance quincenal y movimientos en formato `.xlsx`

---

## 3. Puesta en Marcha Local y Pruebas

1. Configurar `.env` a partir de `.env.example` con la base de datos `lat_compacto_db`.
2. Instalar dependencias y compilar migraciones con datos iniciales:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate --seed
   ```
3. Ejecutar el servidor local (`php artisan serve`) o acceder vía Apache/XAMPP en `http://localhost/lat-compacto/`.
4. Ejecutar la suite de pruebas PHPUnit (`Unit` y `Feature`):
   ```bash
   php artisan test
   ```

### Credenciales sembradas (`DatabaseSeeder`)
Contraseña común para todos los perfiles de demostración: `secreto123`

- **Administración:** `donjusto@sanborreal.pe` (o usuario `donjusto`)
- **Encargado de Planta:** `carlos.anta`
- **Recolector de Ruta:** `mateo.ruta`
- **Control de Tina y Calidad:** `sabina.tina`
- **Contador Externo:** `contador.marco`
- **Acceso Personalizado:** `lucia.personal`

---

## 4. Documentación y Mockups

- Estado detallado del proyecto y arquitectura: [docs/status.md](docs/status.md)
- Mockups y wireframes de referencia: [docs/mockups/](docs/mockups/)
