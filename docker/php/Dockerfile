FROM php:7.4-cli

RUN apt-get update \
    && apt-get install -y libicu-dev \
    && pecl install redis \
    && docker-php-ext-enable redis

WORKDIR /usr/share/bothelp