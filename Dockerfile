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
    opcache \
    pdo \
    pdo_mysql \
    intl \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

FROM base AS dev

RUN apk --no-cache add $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY . /app

RUN chown -R www-data:www-data /app

USER www-data

RUN composer install --prefer-dist --no-scripts

FROM base AS prod

COPY . /app
RUN chown -R www-data:www-data /app

USER www-data

RUN composer install --prefer-dist --no-scripts --no-dev --optimize-autoloader

EXPOSE 9000

CMD ["php-fpm"]
