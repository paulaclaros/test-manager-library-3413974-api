#!/bin/sh
set -e

echo "Esperando a que la base de datos PostgreSQL esté lista..."
until php -r "
    try {
        new PDO('pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
"; do
    echo "PostgreSQL no responde aún, reintentando en 2 segundos..."
    sleep 2
done

echo "Ejecutando migraciones de Laravel en el esquema 'library'..."
php artisan migrate --force

echo "Asegurando administrador inicial..."
php artisan library:init-admin || true

echo "Creando directorios y enlace de almacenamiento si no existen..."
mkdir -p storage/app/public/covers storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chmod -R 777 storage bootstrap/cache

echo "Iniciando servicio de Manager Library API en el puerto 8080..."
exec php artisan serve --host=0.0.0.0 --port=8080
