#!/usr/bin/env sh
set -eu

cd /var/www/html

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

exec "$@"
