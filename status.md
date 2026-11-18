# ESTADO DEL PROYECTO · LAT COMPACTO
**Fecha de actualización:** 02 de noviembre de 2026  
**Proyecto:** Plataforma Web Comunitaria de Gestión Láctea Rural (LAT - Versión Compacta Universitaria)  
**Institución:** Universidad Continental · Asignatura: Programación Web (Cusco, 2026)  
**Entorno de ejecución:** PHP 8.2+ / Laravel 12 · MariaDB/MySQL (XAMPP) · Apache  

---

## 1. Resumen Ejecutivo y Propósito Social

LAT es una plataforma web comunitaria, abierta y sin comisiones (S/ 0.00), diseñada para microempresas familiares, asociaciones ganaderas y queserías rurales de la cuenca de Anta, Cusco.

### Problemática Resuelta
- **Pérdida y deterioro de registros:** Sustitución de cuadernos escolares que se mojaban con agua, leche o suero, eliminando disputas en la liquidación quincenal.
- **Brecha digital y ergonomía de campo:** Interfaz táctil adaptada a una sola mano (botones $\ge$ 54px) y dictado por voz para operarios con guantes o adultos mayores.
- **Cero desperdicio lácteo:** Triage sanitario rápido con alcohol a 74°; la leche con acidez se desvía éticamente a quesillos frescos de 4 días, preservando el valor económico del productor.
- **Protección normativa (Ley N.° 29733):** Minimización de datos para jornales de apoyo en planta (lavado/volteo) sin exigir DNI.

---

## 2. Arquitectura de Software y Stack Tecnológico

| Capa | Tecnología / Herramienta | Detalles de Implementación |
| :--- | :--- | :--- |
| **Backend Core** | PHP 8.2.12 / Laravel 12 | Arquitectura MVC con Capa de Servicio desacoplada (`LacteoService`). |
| **Persistencia** | MariaDB / MySQL (`lat_compacto_db`) | 5 tablas relacionales consolidadas con índices compuestos anti *Full Table Scan*. |
| **Lógica de Negocio** | `App\Services\LacteoService` | Cálculos estequiométricos de tina y liquidación sin N+1 (3 consultas agrupadas). |
| **Control de Acceso** | RBAC nativo en modelo `User` | 5 roles operativos (`administracion`, `encargado_planta`, `recolector_ruta`, `control_tina`, `contador_externo`). |
| **Frontend / UI** | Blade + CSS Flat Fill Contrast | Sistema visual *Brex-style* (sin sombras, sin bordes artificiales), paleta acuarela, tipografía tabular (`font-weight: 300`). |
| **Interacción Rural** | Web Speech API + JS | Reconocimiento de voz para pesaje, síntesis de voz confirmatoria y cola en `localStorage`. |
| **Internacionalización** | Multi-idioma en cliente | Selector trilingüe dinámico: Español (ES), Quechua (QU) e Inglés (EN). |

---

## 3. Matriz de Módulos y Estado de Implementación

| Módulo | Ruta Web | Controlador / Servicio | Estado | Características Clave |
| :--- | :--- | :--- | :---: | :--- |
| **Portal Público** | `/` | `HomeController@index` | ✅ Completo | Portada institucional, 4 herramientas de campo y los 7 pilares éticos. |
| **Autenticación** | `/login`, `/logout` | `AuthController` | ✅ Completo | Inicio de sesión por usuario/correo, credenciales rápidas de demostración. |
| **M1: Acopio por Voz** | `/panel/acopio` (POST) | `PanelController@guardarAcopio` | ✅ Completo | Dictado por voz (*"Don Eloy Condori, 35 litros"*), teclado táctil de apoyo, UUID offline. |
| **M2: Calidad Sanitaria** | `/panel/calidad/{id}` | `PanelController@evaluarCalidad` | ✅ Completo | Triage de alcohol 74°: aprobación para tina principal o desvío a quesillo fresco de 4 días. |
| **M3: Tina y Receta** | `/panel/tina/{id}` | `PanelController@actualizarTina` | ✅ Completo | Curva térmica (72 °C $\rightarrow$ 36–40 °C), dosificación de cuajo (2.5 ml), sal (1.8 kg) y cultivo (1.0 g) por 100 L. |
| **M4: Liquidaciones** | `/panel/movimiento` | `PanelController@registrarMovimiento` | ✅ Completo | Descuento de adelantos (efectivo/queso), registro de ayuda eventual sin DNI, comisión S/ 0.00. |
| **M5: Exportación CSV** | `/panel/exportar-csv` | `PanelController@exportarCsv` | ✅ Completo | Descarga de balance quincenal para SUNAT y bancos con formato formal. |

---

## 4. Estado de Infraestructura y Base de Datos

- **Archivo de configuración (`.env`):**
  - Base de datos configurada: `lat_compacto_db` en `127.0.0.1:3306`.
  - Usuario: `root`, contraseña vacía (estándar XAMPP).
  - Parámetros operativos cargados: comisiones al 0%, temperaturas de pasteurización (72 °C) e inoculación (36–40 °C).
- **Migración (`database/migrations/2026_01_01_000001_crear_tablas_lat_compacto.php`):**
  - Tablas: `users`, `proveedores`, `acopios`, `lotes`, `liquidaciones`.
  - Índices de rendimiento incorporados: `idx_proveedores_activo`, `idx_acopios_fecha_calidad`, `idx_acopios_proveedor_fecha`, `idx_lotes_fecha_estado`.
- **Semillero (`database/seeders/DatabaseSeeder.php`):**
  - 5 usuarios de prueba con perfiles operativos reales y contraseña común `secreto123`.
  - 5 productores ganaderos georreferenciados en la pampa de Anta.
  - Registros precargados de acopio diario, lote de enfriamiento activo y movimientos financieros.
- **Diagnóstico del Servicio MySQL:**
  - ⚠️ *Actualmente el servicio MySQL en XAMPP se encuentra detenido* (`Connection refused: 10061`). Requiere iniciarse desde el panel de control de XAMPP para ejecutar migraciones y semillero.

---

## 5. Estado de Control de Versiones (Git) y Trabajo Grupal

- **Repositorio Local:**
  - En el directorio actual `lat-compacto`, el repositorio Git aún no ha sido inicializado (`.git` ausente).
- **Estrategia Colaborativa Universitaria (`GUIA_GITHUB_ESTUDIANTES.md`):**
  - Existe un plan estructurado de **12 commits equitativos** (4 por cada uno de los 3 estudiantes) con comandos Git listos:
    1. **Estudiante 1 (Persistencia y Datos):** Migración, Modelos User/Proveedor, Modelos Acopio/Lote/Liquidación, DatabaseSeeder.
    2. **Estudiante 2 (Lógica y Backend Core):** `LacteoService` (Receta/Térmica), `LacteoService` (Liquidación sin N+1 y CSV), `AuthController`, `PanelController`.
    3. **Estudiante 3 (Frontend y UX):** CSS Flat Fill, Layout Maestro Trilingüe, Vistas Inicio/Login, Vista Panel con Web Speech API.

---

## 6. Verificación de Código y Sintaxis

- **Sintaxis PHP:** Validada con `php -l` en `routes/web.php`, `PanelController.php` y `LacteoService.php` $\rightarrow$ Cero errores de sintaxis detectados.
- **Assets y Brand:**
  - Hojas de estilo presentes en `public/css/lat-design-system.css`.
  - Logotipos e isotipos SVG disponibles en `public/brand/` y `public/favicon.svg`.
  - Fuentes locales disponibles en `public/fonts/` (Inter y Gloock).

---

## 7. Próximos Pasos Operativos

1. **Servicio MySQL:** Iniciar el servicio MariaDB/MySQL en el panel de XAMPP.
2. **Creación de Base de Datos y Migración:**
   ```powershell
   & "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS lat_compacto_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   & "C:\xampp\php\php.exe" artisan migrate --seed
   ```
3. **Control de Versiones (Git):**
   - Inicializar el repositorio (`git init`).
   - Vincular con el repositorio remoto de GitHub del grupo universitario.
   - Ejecutar la distribución de los 12 commits siguiendo la secuencia definida en `GUIA_GITHUB_ESTUDIANTES.md`.
4. **Despliegue y Acceso Local:**
   - Servidor local: `http://localhost/lat-compacto/` o mediante `php artisan serve`.
   - Credenciales de prueba:
     - Administrador: `donjusto@sanborreal.pe` · Clave: `secreto123`
     - Encargado: `carlos.anta` · Clave: `secreto123`
     - Calidad: `sabina.tina` · Clave: `secreto123`
     - Contador: `contador.marco` · Clave: `secreto123`
