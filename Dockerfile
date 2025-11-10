# Stage: vendor - usar PHP 8.1 para composer (garantiza compatibilidad con composer.lock)
FROM php:8.1-cli AS vendor

WORKDIR /app

# Dependencias del sistema necesarias para extensiones comunes de Laravel y composer
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip intl mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer (última versión estable)
RUN php -r "copy('https://getcomposer.org/installer','composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm composer-setup.php

# Copiar sólo composer files primero (cache layer)
COPY composer.json composer.lock ./

# Instalar dependencias sin dev (esto se ejecuta con PHP 8.1)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Si tu proyecto necesita ejecutar scripts (post-install), los puedes ejecutar aquí:
# RUN composer run-script post-install-cmd

# Stage: node builder (assets frontend)
FROM node:18 AS node_builder

WORKDIR /app
# Copiar package files e instalar
COPY package.json package-lock.json ./
RUN npm ci --silent

# Copiar recursos y construir (ajusta a tu flujo: npm run build / vite / mix)
COPY . .
# Si usas Vite o Laravel Mix, asegúrate del script correcto:
RUN npm run build

# Stage final: runtime con php-fpm 8.1
FROM php:8.1-fpm

# Instalar extensiones necesarias en runtime (si no están ya)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip intl mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copiar vendor optimizado desde la build vendor
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/composer.lock ./composer.lock
COPY --from=vendor /usr/local/bin/composer /usr/local/bin/composer

# Copiar aplicación
COPY . .

# Copiar assets compilados desde node_builder (ajusta la ruta si tus build outputs van a public/)
COPY --from=node_builder /app/public ./public
# Si tus assets van a /dist o /build cambia la ruta correspondiente

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache || true

ENV APP_ENV=production
ENV APP_DEBUG=false

# Exponer puerto del FPM (si vas a poner nginx delante) o usar CMD para pruebas
EXPOSE 9000

# CMD por defecto para php-fpm (usado con un reverse proxy / nginx en prod)
CMD ["php-fpm"]
