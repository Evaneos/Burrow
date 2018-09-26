FROM php:5.6

RUN apt-get update
RUN apt-get install -y \
        librabbitmq-dev \
        procps

RUN pecl install amqp
RUN docker-php-ext-enable amqp

RUN docker-php-ext-install -j$(nproc) bcmath pcntl

RUN mv /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
RUN sed -i 's/;date.timezone =/date.timezone = "Europe\/Paris"/g' /usr/local/etc/php/php.ini
