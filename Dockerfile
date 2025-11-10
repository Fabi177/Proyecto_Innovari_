 url=https://github.com/Fabi177/Proyecto_Innovari_/blob/main/Dockerfile
# --- Vendor stage: usar PHP 8.2 para composer (coincide con composer.lock / laravel v11) ---
FROM php:8.2-cli AS vendor

ENV DEBIAN_FRONTEND=noninteractive
WORKDIR /app

# Dependencias necesarias para compilar extensiones y usar composer
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    ca-certificates \
    pkg-config \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zlib1g-dev \
 && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN php -r "copy('https://getcomposer.org/installer','composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm composer-setup.php

# Copiar sólos los archivos de composer para usar layer cache
COPY composer.json composer.lock ./

# Ejecutar composer con PHP 8.2 (coincide con los requisitos del lockfile)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# --- Node builder (assets frontend) ---
FROM node:18 AS node_builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --silent
COPY . .
RUN npm run build

# --- Image final: php-fpm 8.2 runtime ---
FROM php:8.2-fpm

ENV DEBIAN_FRONTEND=noninteractive
WORKDIR /var/www/html

# Instalar librerías necesarias en runtime (y para extensiones)
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zlib1g-dev \
    unzip \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) pdo_mysql zip intl mbstring exif pcntl bcmath gd \
 && rm -rf /var/lib/apt/lists/*

# Copiar vendor (ya instalado en etapa vendor)
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/composer.lock ./composer.lock
COPY --from=vendor /usr/local/bin/composer /usr/local/bin/composer

# Copiar aplicación
COPY . .

# Copiar assets compilados desde node_builder (ajusta ruta según tu build)
COPY --from=node_builder /app/public ./public

# Permisos (ajusta según tu entorno)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache || true

ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 9000
CMD ["php-fpm"]
