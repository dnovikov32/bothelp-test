FROM php:7.4-fpm

RUN apt-get update \
    && apt-get install -y procps libicu-dev git htop \
    && pecl install redis \
    && docker-php-ext-enable redis

ADD https://getcomposer.org/composer.phar /usr/bin/composer
RUN chmod 775 /usr/bin/composer

WORKDIR /usr/share/bothelp