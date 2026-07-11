FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    icu-libs icu-data-en libzip libpng oniguruma \
    nginx supervisor curl git unzip

RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS libzip-dev libpng-dev oniguruma-dev icu-dev \
    && docker-php-ext-install pdo_mysql zip intl opcache bcmath \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json ./

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi 2>/dev/null || true \
    && mkdir -p storage/framework/{cache/data,sessions,views} bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
