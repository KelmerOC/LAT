FROM php:8.4-apache

# Dependencias del sistema y extensiones PHP requeridas por Laravel 12, MySQL SSL y exportación Excel
RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        mbstring \
        mysqli \
        opcache \
        pdo_mysql \
        xml \
        zip \
    && a2enmod rewrite headers \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

# Composer oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Instala dependencias PHP primero para aprovechar la caché de Docker
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copia el código de la aplicación
COPY . .

# Configuración de Apache apuntando a /public y script de arranque (limpia CRLF por compatibilidad Windows)
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/start.sh /usr/local/bin/start-render.sh
RUN sed -i 's/\r$//' /usr/local/bin/start-render.sh \
    && chmod +x /usr/local/bin/start-render.sh \
    && composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD ["/usr/local/bin/start-render.sh"]
