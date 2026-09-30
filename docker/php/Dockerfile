FROM php:8.4-fpm

ARG UID=1000
ARG GID=1000

# Системные зависимости
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        zip \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libicu-dev \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# PHP расширения, нужные Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Пользователь с UID/GID хоста, чтобы не ловить проблемы с правами на файлы
RUN groupadd -g ${GID} app \
    && useradd -u ${UID} -g app -m -s /bin/bash app

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

USER app
