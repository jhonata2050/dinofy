#!/bin/sh
set -e

cd /var/www/html

mkdir -p storage/framework/{cache/data,sessions,views} bootstrap/cache
chmod -R 777 storage bootstrap/cache

DB_PORT="${DB_PORT:-3306}"

echo "Aguardando MySQL em ${DB_HOST}:${DB_PORT}..."
until php -r "new PDO('mysql:host='.getenv('DB_HOST').';port=${DB_PORT}', getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
    sleep 2
done
echo "MySQL conectado."

php artisan migrate --force --no-interaction 2>&1 || true
php artisan db:seed --force --no-interaction 2>&1 || true

php artisan config:cache 2>&1 || true
php artisan view:cache 2>&1 || true

exec /usr/bin/supervisord -n -c /etc/supervisord.conf
