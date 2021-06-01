FROM composer:latest as composer
FROM php:7.4.5-fpm-alpine3.11 as php

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php

ARG UID
ARG GID
ARG STUDENT

ENV APP_TARGET_UID ${UID:-1000}
ENV APP_TARGET_GID ${GID:-1000}
ENV APP_STUDENT ${STUDENT:-Suleymanova Silina Radikovna}

RUN apk --no-cache add shadow && \
    usermod -u ${APP_TARGET_UID} www-data

USER www-data:www-data

COPY --chown=www-data . /var/www/app
WORKDIR /var/www/app
