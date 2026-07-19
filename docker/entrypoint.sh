#!/bin/sh
set -e

cd /var/www/html

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache
chmod -R 777 storage bootstrap/cache

mkdir -p /srv/tenants
chmod 777 /srv/tenants 2>/dev/null || chown www-data:www-data /srv/tenants || true

if [ -d /etc/traefik/dynamic ]; then
    chmod 777 /etc/traefik/dynamic 2>/dev/null || chown www-data:www-data /etc/traefik/dynamic || true
fi

if [ -S /var/run/docker.sock ]; then
    chmod 666 /var/run/docker.sock 2>/dev/null || true
fi


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
