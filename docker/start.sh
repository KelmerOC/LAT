#!/usr/bin/env bash
set -e

PORT="${PORT:-10000}"

# Ajusta Apache al puerto asignado por Render
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Asegura permisos de escritura para Laravel
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Limpia y reconstruye cachés con las variables de entorno de producción
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecuta migraciones (y seed inicial opcional si la tabla users está vacía)
if [ -n "${DB_HOST:-}" ]; then
    php artisan migrate --force

    if [ "${SEED_IF_EMPTY:-false}" = "true" ]; then
        if php -r '
            require "vendor/autoload.php";
            $app = require "bootstrap/app.php";
            $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            exit(App\Models\User::count() === 0 ? 0 : 1);
        '; then
            php artisan db:seed --force
        fi
    fi
fi

exec apache2-foreground
