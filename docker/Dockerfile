FROM php:8.3-fpm-alpine

# Install dev dependencies
RUN apk update \
    && apk upgrade --available \
    && apk add --virtual build-deps \
        autoconf \
        build-base \
        icu-dev \
        libevent-dev \
        openssl-dev \
        zlib-dev \
        libzip \
        libzip-dev \
        zlib \
        zlib-dev \
        bzip2 \
        git \
        libpng \
        libpng-dev \
        libjpeg \
        libjpeg-turbo-dev \
        libwebp-dev \
        freetype \
        freetype-dev \
        postgresql-dev \
        linux-headers \
        curl \
        wget \
        bash

#   Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

#   Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN docker-php-ext-configure intl && docker-php-ext-install intl
RUN docker-php-ext-install \
    intl \
    gd \
    bcmath \
    pdo_pgsql \
    sockets \
    zip

#   XDebug
RUN apk add --no-cache linux-headers  \
    && apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

#   Symfony CLI (optional)
COPY --link \
    --from=ghcr.io/symfony-cli/symfony-cli:latest \
    /usr/local/bin/symfony /usr/local/bin/symfony

#   Redis
#RUN pecl channel-update pecl.php.net \
#    && pecl install -o -f \
#        redis \
#        event \
#    && rm -rf /tmp/pear \
#    && echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini \
#    && echo "extension=event.so" > /usr/local/etc/php/conf.d/event.ini

RUN pecl clear-cache
