# Etapa 1: Build de assets con Node.js
FROM node:18-alpine AS node_build

WORKDIR /app

COPY package*.json ./
COPY vite.config.js ./
COPY postcss.config.js ./
COPY tailwind.config.js ./

RUN npm ci

COPY resources ./resources
COPY public ./public

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
    netcat-traditional \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache
RUN a2enmod rewrite
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Copiar assets compilados
COPY --from=node_build /app/public/build ./public/build

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ✅ CREAR .env CON APP_KEY GENERADA
RUN cp .env.example .env || echo "APP_NAME=Laravel" > .env
RUN echo "APP_ENV=production" >> .env
RUN echo "APP_DEBUG=false" >> .env
RUN echo "APP_URL=http://localhost" >> .env
RUN echo "" >> .env
RUN echo "DB_CONNECTION=mysql" >> .env
RUN echo "DB_HOST=\${DB_HOST:-mysql}" >> .env
RUN echo "DB_PORT=\${DB_PORT:-3306}" >> .env
RUN echo "DB_DATABASE=\${DB_DATABASE:-laravel}" >> .env
RUN echo "DB_USERNAME=\${DB_USERNAME:-laravel}" >> .env
RUN echo "DB_PASSWORD=\${DB_PASSWORD:-secret}" >> .env
RUN echo "" >> .env
RUN echo "CACHE_STORE=file" >> .env
RUN echo "SESSION_DRIVER=file" >> .env
RUN echo "QUEUE_CONNECTION=sync" >> .env

# ✅ GENERAR APP_KEY
RUN php artisan key:generate --force

# Permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# ✅ SCRIPT DE INICIO MEJORADO
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "🚀 Iniciando Laravel en AWS ECS..."\n\
\n\
# Esperar MySQL\n\
if [ ! -z "$DB_HOST" ]; then\n\
  echo "⏳ Esperando MySQL en $DB_HOST:$DB_PORT..."\n\
  until nc -z $DB_HOST $DB_PORT 2>/dev/null; do\n\
    echo "   MySQL no disponible, reintentando..."\n\
    sleep 2\n\
  done\n\
  echo "✅ MySQL conectado!"\n\
  \n\
  # Ejecutar migraciones\n\
  echo "📦 Ejecutando migraciones..."\n\
  php artisan migrate --force || echo "⚠️  Migraciones fallaron (puede ser normal en primer deploy)"\n\
fi\n\
\n\
# Limpiar caches viejos\n\
php artisan config:clear\n\
php artisan route:clear\n\
php artisan view:clear\n\
\n\
# Crear nuevos caches\n\
echo "⚡ Optimizando Laravel..."\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
echo "✅ Laravel listo!"\n\
echo "🌐 Escuchando en puerto 80..."\n\
\n\
# Iniciar Apache en foreground\n\
exec apache2-foreground' > /start.sh && chmod +x /start.sh

# Health check del contenedor
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
  CMD curl -f http://localhost/health || curl -f http://localhost/ || exit 1

EXPOSE 80

CMD ["/start.sh"]
