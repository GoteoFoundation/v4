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

COPY --from=composer:lts /usr/bin/composer /usr/local/bin/composer

RUN curl --remote-name https://curl.se/ca/cacert.pem \
    && mv cacert.pem /etc/ssl/certs/ca-certificates.crt

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
