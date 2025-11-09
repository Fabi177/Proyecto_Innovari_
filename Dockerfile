# ============================================
# DOCKERFILE HÍBRIDO: Simple + Robusto para AWS
# ============================================

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
    netcat-traditional \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . /var/www/html/

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configurar Apache Document Root para Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/bootstrap/cache

# ✅ CREAR .ENV CON VARIABLES DINÁMICAS
RUN if [ ! -f .env ]; then cp .env.example .env; fi && \
    sed -i 's/DB_HOST=.*/DB_HOST=${DB_HOST:-127.0.0.1}/' .env && \
    sed -i 's/DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE:-laravel}/' .env && \
    sed -i 's/DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME:-root}/' .env && \
    sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD:-}/' .env

# ✅ GENERAR APP_KEY
RUN php artisan key:generate --force

# ✅ SCRIPT DE INICIO QUE ESPERA MYSQL
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "🚀 Iniciando Laravel..."\n\
\n\
# Esperar MySQL si está configurado\n\
if [ ! -z "${DB_HOST}" ] && [ "${DB_HOST}" != "127.0.0.1" ]; then\n\
  echo "⏳ Esperando MySQL en ${DB_HOST}..."\n\
  until nc -z ${DB_HOST} ${DB_PORT:-3306} 2>/dev/null; do\n\
    sleep 2\n\
  done\n\
  echo "✅ MySQL disponible!"\n\
  \n\
  echo "📦 Ejecutando migraciones..."\n\
  php artisan migrate --force || echo "⚠️ Migraciones no ejecutadas"\n\
fi\n\
\n\
# Optimizar Laravel\n\
echo "⚡ Optimizando..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
echo "✅ Laravel listo en puerto 80"\n\
exec apache2-foreground' > /docker-entrypoint.sh && chmod +x /docker-entrypoint.sh

# ✅ HEALTH CHECK
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
  CMD curl -f http://localhost/health || curl -f http://localhost/ || exit 1

# Al final, reemplaza todo el script por:
EXPOSE 80
CMD ["apache2-foreground"]
