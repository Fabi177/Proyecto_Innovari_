# Etapa de build (composer)
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Etapa opcional de assets (si usas npm/vite)
FROM node:18 AS node_builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build || true

# Etapa runtime (php-fpm)
FROM php:8.1-fpm
WORKDIR /var/www/html

# Dependencias del sistema
RUN apt-get update && apt-get install -y libzip-dev zip unzip git libpng-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Copiar código y vendor
COPY --from=vendor /app /var/www/html
COPY . /var/www/html

# Copiar assets si se compilaron
COPY --from=node_builder /app/public/build /var/www/html/public/build || true

# Permisos
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

EXPOSE 9000
CMD ["php-fpm"]
