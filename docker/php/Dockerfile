FROM php:8.5.6-fpm

# Системные зависимости
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip

# PHP-расширения
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        gd \
        opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Права на storage
RUN mkdir -p storage/compiled storage/cache \
    && chown -R www-data:www-data storage/

COPY . .

RUN composer install --no-dev --optimize-autoloader

USER www-data