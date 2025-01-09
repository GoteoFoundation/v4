FROM php:8.3-fpm-alpine AS base

RUN apk --no-cache add \
    curl \
    git \
    linux-headers \
    icu-dev \
    zip \
    libzip-dev \
    unzip

RUN docker-php-ext-install \
    pdo \
    pdo_mysql

RUN docker-php-ext-install \
    intl \
    zip

RUN apk --no-cache add $PHPIZE_DEPS
RUN pecl install apcu \
    && docker-php-ext-enable apcu

COPY --from=composer:lts /usr/bin/composer /usr/local/bin/composer


FROM base AS dev

RUN docker-php-ext-install opcache

RUN apk --no-cache add $PHPIZE_DEPS
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY . /app
RUN chown -R www-data:www-data /app

USER www-data
WORKDIR /app

RUN composer install --prefer-dist --no-scripts

FROM base AS prod

COPY . /app
RUN chown -R www-data:www-data /app

USER www-data
WORKDIR /app

RUN composer install --prefer-dist --no-scripts --no-dev --optimize-autoloader

EXPOSE 9000

CMD ["php-fpm"]
