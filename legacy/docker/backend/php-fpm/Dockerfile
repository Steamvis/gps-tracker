FROM php:7.4.8-fpm

RUN apt-get update \
    && apt-get -y install wget \
    git \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql


# install redis
RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  echo "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini

WORKDIR /backend
