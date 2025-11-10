FROM php:8.2-apache

# Instalar dependencias básicas
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP básicas
RUN docker-php-ext-install pdo pdo_mysql

# Copiar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar código
WORKDIR /var/www/html
COPY . .

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader

# Configurar Apache para Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Permisos básicos
RUN chown -R www-data:www-data /var/www/html

# Permisos específicos para Laravel
RUN chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/bootstrap/cache

# Puerto
EXPOSE 80

# Iniciar Apache
CMD ["apache2-foreground"]
