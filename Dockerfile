# syntax=docker/dockerfile:1.7

FROM php:8.4-fpm-bookworm AS php-base

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        pdo_mysql \
        pcntl \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
COPY docker/php/99-matngotbear.ini /usr/local/etc/php/conf.d/99-matngotbear.ini

# PHP-FPM must receive the environment variables supplied by Compose.
RUN sed -i 's/^;\?clear_env = .*/clear_env = no/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^listen = .*/listen = 9000/' /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

FROM php-base AS composer-deps

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts \
    --optimize-autoloader

FROM node:22-bookworm-slim AS frontend-build

WORKDIR /var/www/html
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts
COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build

FROM php-base AS app

COPY --from=composer-deps /var/www/html/vendor ./vendor
COPY . .
COPY --from=frontend-build /var/www/html/public/build ./public/build
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# The repository may contain a Windows junction at public/storage. It is
# excluded from the build context and recreated against the Docker volume.
# Strip CRLF in case the shell script was checked out on Windows.
RUN rm -rf public/storage \
    && mkdir -p storage/app/public storage/app/private \
        storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache \
    && ln -s /var/www/html/storage/app/public public/storage \
    && sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && php artisan package:discover --ansi \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm", "-F"]

FROM nginx:1.27-alpine AS web

COPY --from=app /var/www/html/public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html
