#!/bin/sh
set -e

cd /var/www/html

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache
chmod -R 777 storage bootstrap/cache

DB_PORT="${DB_PORT:-3306}"
MAX_RETRIES=60
RETRY=0

echo "Aguardando MySQL em ${DB_HOST}:${DB_PORT}..."
until php -r "new PDO('mysql:host='.getenv('DB_HOST').';port=${DB_PORT}', getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
    RETRY=$((RETRY + 1))
    if [ "$RETRY" -ge "$MAX_RETRIES" ]; then
        echo "ERRO: MySQL nao respondeu apos ${MAX_RETRIES} tentativas."
        exit 1
    fi
    sleep 2
done
echo "MySQL conectado."

php artisan migrate --force --no-interaction 2>&1
php artisan db:seed --force --no-interaction 2>&1

php artisan config:cache 2>&1 || true
php artisan view:cache 2>&1 || true

if [ -d /etc/traefik/dynamic ] && [ ! -f /etc/traefik/dynamic/master.yml ]; then
    cat > /etc/traefik/dynamic/master.yml <<'TRAEFIKEOF'
http:
  routers:
    master:
      rule: "PathPrefix(`/`)"
      service: master
      entryPoints:
        - web
      priority: 1
  services:
    master:
      loadBalancer:
        servers:
          - url: "http://master:80"
TRAEFIKEOF
    echo "Traefik master config criado."
fi

exec /usr/bin/supervisord -n -c /etc/supervisord.conf
