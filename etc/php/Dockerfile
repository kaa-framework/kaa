FROM php:8.2.12-bullseye

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    opcache \
    @composer \
    pdo_mysql \
    xdebug-3.2.2

WORKDIR /app
