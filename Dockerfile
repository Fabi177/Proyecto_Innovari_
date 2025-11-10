# --- Vendor stage: usar PHP 8.1 para composer (garantiza compatibilidad con composer.lock) ---
FROM php:8.1-cli AS vendor

ENV DEBIAN_FRONTEND=noninteractive

WORKDIR /app

# Dependencias del sistema necesarias para composer y para compilar extensiones PHP
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
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

# Copiar composer files primero (cache layer)
COPY composer.json composer.lock ./

# Instalar dependencias sin dev (esto se ejecuta con PHP 8.1 y con libicu disponible)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# --- Node builder (assets frontend) ---
FROM node:18 AS node_builder
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --silent

COPY . .
RUN npm run build

# --- Final runtime: php-fpm 8.1 ---
FROM php:8.1-fpm

ENV DEBIAN_FRONTEND=noninteractive
WORKDIR /var/www/html

# Instalar en la imagen final las mismas dependencias necesarias para docker-php-ext-install
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    pkg-config \
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

# Copiar vendor optimizado desde la etapa vendor
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/composer.lock ./composer.lock
COPY --from=vendor /usr/local/bin/composer /usr/local/bin/composer

# Copiar aplicación
COPY . .

# Copiar assets compilados desde node_builder (ajusta ruta si necesario)
COPY --from=node_builder /app/public ./public

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache || true

ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 9000
CMD ["php-fpm"]
