FROM php:7.4-fpm

ARG USER_USERNAME
ARG USER_UID
ARG USER_GID

# Create user with home to work with permissions on files
RUN addgroup --gid $USER_GID $USER_USERNAME
RUN adduser --gecos "" --disabled-password --gid $USER_GID --uid $USER_UID $USER_USERNAME

RUN apt-get update \
    && apt-get install -y libicu-dev git \
    && pecl install redis \
    && docker-php-ext-enable redis

ADD https://getcomposer.org/composer.phar /usr/local/bin/composer
RUN chown $USER_USERNAME:$USER_USERNAME /usr/local/bin/composer && chmod 775 /usr/local/bin/composer

# Set password and add sudo group to new user
RUN echo "$USER_USERNAME:$USER_USERNAME" | chpasswd
RUN echo "root:root" | chpasswd
RUN usermod -a -G sudo $USER_USERNAME
RUN echo "$USER_USERNAME ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers

USER $USER_USERNAME

WORKDIR /usr/share/bothelp