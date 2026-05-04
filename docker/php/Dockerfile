FROM composer:2 AS composer

FROM php:8.3-fpm-bookworm

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        zip \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libpq-dev \
        libsqlite3-dev \
        libssl-dev \
        pkg-config \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_sqlite \
        xml \
        zip

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

CMD ["php-fpm"]
