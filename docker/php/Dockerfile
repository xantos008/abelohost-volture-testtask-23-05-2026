FROM php:8.5.6-fpm-alpine

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

# ── Node.js 24.16.0 ───────────────────────────────────────────────────────────
# Скачиваем конкретный бинарник с nodejs.org — не зависим от пакетных
# репозиториев и получаем точную версию.
ENV NODE_VERSION=24.16.0

RUN curl -fsSL "https://nodejs.org/dist/v${NODE_VERSION}/node-v${NODE_VERSION}-linux-x64.tar.gz" \
    | tar -xz -C /usr/local --strip-components=1 --no-same-owner \
    && node --version \
    && npm --version

# ── Dart Sass ─────────────────────────────────────────────────────────────────
RUN npm install -g sass \
    && sass --version

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Права на storage
RUN mkdir -p storage/compiled storage/cache \
    && chown -R www-data:www-data storage/

COPY . .

RUN composer install --no-dev --optimize-autoloader

# ── Компиляция SCSS ───────────────────────────────────────────────────────────
# Выполняется при каждой сборке образа.
# --style=compressed   — минификация
# --no-source-map      — не генерируем .map
RUN sass public/assets/scss/app.scss:public/assets/css/app.css \
        --style=compressed \
        --no-source-map

USER www-data