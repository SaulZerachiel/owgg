FROM php:8.3-cli-bookworm AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        libicu-dev \
        libonig-dev \
        libpq-dev \
        libsqlite3-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        intl \
        mbstring \
        pdo_pgsql \
        pdo_sqlite \
        zip \
    && rm -rf /var/lib/apt/lists/*

FROM php-base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

FROM php-base AS assets

WORKDIR /app
COPY --from=node:22-bookworm-slim /usr/local/ /usr/local/
COPY package.json package-lock.json ./
RUN npm ci
COPY --from=vendor /app/vendor ./vendor
COPY . .
RUN php artisan package:discover --ansi
RUN npm run build

FROM php-base AS runtime

RUN apt-get update \
    && apt-get install -y --no-install-recommends supervisor \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV PORT=10000
ENV DB_CONNECTION=pgsql
ENV QUEUE_CONNECTION=database
ENV DB_QUEUE_RETRY_AFTER=330

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build
COPY docker/supervisord.conf /etc/supervisor/conf.d/owgg.conf
RUN php artisan package:discover --ansi

RUN mkdir -p \
        database \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database

EXPOSE 10000

CMD ["sh", "-c", "php artisan package:discover --ansi && if [ \"${DB_CONNECTION:-}\" = \"pgsql\" ]; then : \"${DATABASE_URL_UNPOOLED:?DATABASE_URL_UNPOOLED is required for PostgreSQL migrations}\"; DATABASE_URL=\"$DATABASE_URL_UNPOOLED\" php artisan migrate --force; else php artisan migrate --force; fi && exec supervisord -n -c /etc/supervisor/conf.d/owgg.conf"]
