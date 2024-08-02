FROM php:8.3-fpm

# System dependencies
RUN apt-get update && apt install -y curl git

# PHP extensions
## OPcache
RUN docker-php-ext-install opcache

## XDebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

## PDO & mysql
RUN docker-php-ext-install pdo pdo_mysql

# Zip
RUN apt-get install -y libzip-dev zip unzip \
    && docker-php-ext-install zip

# Intl
RUN apt-get install -y libicu-dev \
    && docker-php-ext-install intl

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer


WORKDIR /app
COPY . /app

RUN chown -R www-data:www-data /app
USER www-data
RUN composer install --prefer-dist --no-scripts --no-dev
