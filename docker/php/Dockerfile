FROM php:8.2-fpm

# System dependencies
RUN apt update && apt install -y \
    curl

# PHP extensions

## OPcache
RUN docker-php-ext-install \
    opcache

# APCU
RUN pecl install \
    apcu \
&& docker-php-ext-enable \
    apcu

## XDebug
RUN pecl install \
    xdebug \
&& docker-php-ext-enable \
    xdebug

## PDO & mysql
RUN docker-php-ext-install \
    pdo pdo_mysql

# Zip
RUN apt install -y \
    libzip-dev zip unzip \
&& docker-php-ext-install \
    zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /app