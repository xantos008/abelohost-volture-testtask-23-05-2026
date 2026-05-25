# syntax=docker/dockerfile:1.7

# ============================================================
# Stage 1. Composer dependencies
# ============================================================

FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN --mount=type=cache,target=/tmp/cache/composer \
    composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-scripts \
        --optimize-autoloader

# ============================================================
# Stage 2. Runtime
# ============================================================

FROM php:8.4-fpm-alpine

# ---------- Linux packages ----------

RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    zip \
    icu-dev \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    nodejs \
    npm

# ---------- PHP extensions ----------

RUN docker-php-ext-configure gd \
 && docker-php-ext-install \
      pdo \
      pdo_mysql \
      mbstring \
      intl \
      gd \
      opcache

# ---------- Sass ----------

RUN npm install -g sass

# ---------- PHP config ----------

COPY docker/php/opcache.ini \
     /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# ---------- Vendor ----------

COPY --from=composer /app/vendor ./vendor

# ---------- Application ----------

COPY . .

# ---------- Runtime dirs ----------

RUN mkdir -p \
      storage/cache \
      storage/compiled \
 && chown -R www-data:www-data storage

USER www-data

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD pidof php-fpm > /dev/null || exit 1

CMD ["php-fpm", "-F"]