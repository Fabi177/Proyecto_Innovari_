@"
#!/bin/bash

# Esperar a que MySQL esté listo
echo "Esperando a que MySQL esté disponible..."
while ! nc -z mysql 3306; do
  sleep 1
done
echo "MySQL está listo!"

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force

# Limpiar y optimizar cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar el comando principal (Apache)
exec "`$@"
"@ | Out-File -FilePath "docker/entrypoint.sh" -Encoding UTF8