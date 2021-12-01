FROM php:8.0-fpm

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN apt-get update && apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN groupadd -r ghost -g 1000 && useradd -u 1000 -r -g ghost -m -d /opt/project -s /sbin/nologin -c "Ghost user" ghost
USER 1000:1000

WORKDIR /var/www/project



