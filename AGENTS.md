<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>

---

# LAT Compacto — Reglas Específicas del Proyecto

## Arquitectura Frontend y Diseño (`DESIGN.md`)
- **Activos estáticos en `public/`**: la aplicación carga directamente `public/css/lat-design-system.css`, `public/css/lat-dashboard.css`, `public/js/lat-i18n.js`, `public/js/lat-offline.js`, `public/js/lat-notificaciones.js`, `public/js/lat-qr.js` y `public/sw.js` (no usa `@vite` en las vistas activas).
- **Sistema visual Swiss-Grid (`DESIGN.md`)**: mantener esquinas rectas (`border-radius: 0px` o `2px`), `box-shadow: none`, hairlines `1px solid var(--color-hairline)` y contraste accesible (`#c2410c` para botones primarios).
- **Accesibilidad de formularios**: todo `<input>` (salvo `hidden`) y `<select>` visible DEBE tener un `<label for="...">` coincidente o envolvente (verificado por `tests/e2e/visual-standards.cjs`).
- **Selector de idioma en Dashboard**: el selector `ES | QU | EN` vive únicamente en la barra lateral (`resources/views/panel/partials/sidebar.blade.php`, `.dash-lang-row`); no duplicarlo en la barra superior (`topbar.blade.php`).
- **Paridad trilingüe (`es`, `qu`, `en`)**: toda etiqueta, aviso inline, mensaje de confirmación o flash debe existir simultáneamente en `public/js/lat-i18n.js` (`es`, `qu` Qusqu-Qullaw trivocálico, `en`) y en `lang/{es,qu,en}/lat.php`.

## Reglas Operativas del Panel (`/panel`)
- **Flujo obligatorio `1. Acopio → 2. Calidad Sanitaria → 3. Tinas`**:
  - Todo acopio nuevo (`POST /panel/acopio` y `POST /panel/acopio/sync`) nace con `calidad = 'pendiente'` y `destino = 'pendiente'`.
  - El rol `recolector_ruta` incluye `acopio.editar` para corregir o eliminar acopios con confirmación modal.
  - Cuando Web Speech API reconoce productor y litros válidos (`1..5000`), `#form-acopio` se envía automáticamente (online con flash `acopio_voz_reciente`, offline vía `lat-offline.js`) mostrando `#aviso-acopio-voz`.
  - Tanto editar (`#form-edit-acopio`, `data-confirm-kind="edit_acopio"`) como eliminar acopios requieren pasar por `#modal-confirmacion-lat` (`.modal-overlay-confirm` con `z-index: 1200`). Al pulsar `Escape` en modales apilados, primero se cierra únicamente el modal de confirmación.
- **Control Sanitario (3 acciones con confirmación coloreada)**:
  - `POST /panel/calidad/{id}` admite `buena` (aprobar), `acida` (desviar a quesillo) y `descartada` (eliminar por completo), guardando `evaluado_en` y emitiendo una notificación en `notificaciones_panel` hacia Tinas. Si `acopios.lote_id !== null`, se bloquea la reclasificación/descarte.
  - En `#modal-confirmacion-lat`, `#confirm-modal-badge` muestra siempre un aviso en negrita coloreado (`.is-verde`, `.is-amarillo`, `.is-rojo`) antes de confirmar.
- **Tinas trazables, recetas/cronómetro editables y notificaciones en vivo (`App\Services\TinaWorkflowService`)**:
  - `#sec-tina` muestra `#aviso-calidad-tinas` (aviso inline permanente sobre leche disponible sin asignar) y un único modal reutilizable `#modal-iniciar-tina` (`data-confirm-kind="start_batch"`) con selección múltiple de `acopio_ids[]` clasificados (`lote_id IS NULL`), filtros por calidad/día/fecha/orden, recetas base configurables en `plantas.recetas_base` (`PUT /panel/tinas/recetas-base`), proporciones (`cuajo_ml`, `sal_kg`, `cultivo_g`) y cronómetro (`duracion_minutos`) editables al iniciar y durante el lote.
  - `tinas.tipo` es `string(80)` (sugerencias en `<datalist>` + texto libre); el prefijo del código de tina sigue el idioma activo (`TINA-` en `es`/`qu`, `VAT-` en `en`) y el de plantas es siempre fijo `PLT-`.
  - Al clasificar calidad, cumplirse el cronómetro (`alerta_fin_enviada`) o finalizar un lote, se registra en `notificaciones_panel`; `public/js/lat-notificaciones.js` actualiza la bandeja del topbar y despliega alertas en vivo vía polling (`GET /panel/notificaciones/poll`, `POST /panel/notificaciones/leer`).
- **Service Worker (`public/sw.js`)**: nunca cachear endpoints dinámicos con efectos de sesión, descarga o polling (`exportar`, `reportes-excel`, `/acceso-qr`, `/idioma`, `/panel/notificaciones`). Al modificar assets estáticos o reglas de caché, incrementar `CACHE_VERSION`.
- **Presupuesto de consultas y entorno local Windows**: mantener el techo N+1 de `GET /panel` en `<= 42` consultas (reutilizar colecciones ya cargadas como `$plantas`). En Windows donde `php.ini` no habilita SQLite por defecto, ejecutar PHPUnit con `php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit`.

