# ==========================================
# ETAPA 1: Dependencias de PHP (Composer)
# ==========================================
FROM composer:2 AS composer_builder
WORKDIR /app
COPY composer.json composer.lock ./
# Descargamos dependencias ignorando alertas de extensiones
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# ==========================================
# ETAPA 2: Compilar Assets del Frontend (Vite/Node)
# ==========================================
FROM node:20-alpine AS frontend_builder
WORKDIR /app
COPY package*.json vite.config.js ./
COPY resources/ ./resources
COPY public/ ./public
# Compilamos CSS y JS
RUN npm ci && npm run build

# ==========================================
# ETAPA 3: Imagen Fija de Producción (Apache + PHP 8.4)
# ==========================================
FROM php:8.4-apache-bookworm

# 1. Instalar extensiones (PostgreSQL, MySQL, Zip, libicu e intl para Filament)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql zip intl mbstring gd exif bcmath
# 2. Configurar Apache para apuntar a la carpeta /public de Laravel
ENV APACHE_DOCUMENT_ROOT="/var/www/html/public"
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

WORKDIR /var/www/html

# 3. Copiar el código fuente completo del proyecto
COPY . .

# Limpiamos vistas compiladas arrastradas desde el host para evitar Blade cache obsoleto
RUN find storage/framework/views -type f -delete || true

# 4. Traer dependencias ya compiladas de las etapas anteriores
COPY --from=composer_builder /app/vendor ./vendor
COPY --from=frontend_builder /app/public/build ./public/build

# 5. COPIAR EL PROGRAMA COMPOSER DESDE LA ETAPA 1
COPY --from=composer_builder /usr/bin/composer /usr/bin/composer


# 6. Optimizar el autoloader final de Composer en PHP 8.4
RUN composer dump-autoload --no-dev --classmap-authoritative

# 6.5 Crear symlink de storage público (para servir PDFs)
RUN php artisan storage:link

# 7. Permisos de escritura para que Laravel pueda guardar sesiones y caché
RUN chown -R www-data:www-data storage bootstrap/cache

# 7. Permisos de escritura para que Laravel pueda guardar sesiones y caché
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

# Arrancar Apache en primer plano
CMD ["apache2-foreground"]
