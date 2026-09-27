#!/usr/bin/env sh
set -eu

cd /var/www/html

PORT="${PORT:-10000}"
export PORT

mkdir -p \
    storage/app/public \
    storage/app/private \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache
fi

if [ ! -L public/storage ]; then
    rm -rf public/storage
    ln -s /var/www/html/storage/app/public public/storage
fi

if [ "${MIGRATE_ON_START:-true}" = "true" ]; then
    attempt=1
    until php artisan migrate --force; do
        if [ "$attempt" -ge 30 ]; then
            echo "Database migrations failed after 30 attempts." >&2
            exit 1
        fi

        attempt=$((attempt + 1))
        sleep 2
    done
fi

envsubst '${PORT}' < /etc/nginx/templates/matngotbear.conf.template \
    > /etc/nginx/conf.d/default.conf

php-fpm -D
exec nginx -g 'daemon off;'
