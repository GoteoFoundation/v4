FROM php:8.2-fpm

# System dependencies
RUN apt update && apt install -y \
    curl

# PHP zip extension
RUN apt install -y \
    libzip-dev zip unzip \
    && docker-php-ext-install \
    zip

# PHP intl extension
RUN apt install -y \
    libicu-dev \
    && docker-php-ext-install \
    intl

# PHP extensions, standalones
RUN docker-php-ext-install \
    opcache pdo pdo_mysql

RUN pecl install apcu xdebug \
    && docker-php-ext-enable apcu xdebug 

WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
