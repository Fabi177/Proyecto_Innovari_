# Etapa 1: Build de assets con Node.js
FROM node:18-alpine AS node_build

WORKDIR /app

# Copiar archivos de dependencias Node
COPY package*.json ./
COPY vite.config.js ./
COPY postcss.config.js ./
COPY tailwind.config.js ./

# Instalar dependencias Node
RUN npm ci

# Copiar archivos necesarios para build
COPY resources ./resources
COPY public ./public

# Compilar assets
RUN npm run build

# Etapa 2: Imagen PHP con Apache
FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache
RUN a2enmod rewrite
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Copiar assets compilados desde la etapa de Node
COPY --from=node_build /app/public/build ./public/build

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Crear archivo .env básico
RUN echo "APP_NAME=Laravel" > .env && \
    echo "APP_ENV=production" >> .env && \
    echo "APP_KEY=" >> .env && \
    echo "APP_DEBUG=false" >> .env && \
    echo "APP_URL=http://localhost" >> .env && \
    echo "DB_CONNECTION=mysql" >> .env && \
    echo "DB_HOST=mysql" >> .env && \
    echo "DB_PORT=3306" >> .env && \
    echo "DB_DATABASE=laravel" >> .env && \
    echo "DB_USERNAME=laravel" >> .env && \
    echo "DB_PASSWORD=secret" >> .env && \
    echo "CACHE_STORE=file" >> .env && \
    echo "SESSION_DRIVER=file" >> .env && \
    echo "QUEUE_CONNECTION=sync" >> .env

# Generar key de Laravel
RUN php artisan key:generate

# Permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Instalar netcat para health checks
RUN apt-get update && apt-get install -y netcat-traditional

# Crear script de inicio inline
RUN echo '#!/bin/bash\n\
set -e\n\
echo "Esperando MySQL..."\n\
until nc -z mysql 3306; do sleep 1; done\n\
echo "MySQL listo!"\n\
php artisan migrate --force || true\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
exec apache2-foreground' > /start.sh && chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]