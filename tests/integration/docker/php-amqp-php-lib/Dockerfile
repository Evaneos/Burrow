FROM php:5.6

RUN docker-php-ext-install -j$(nproc) bcmath pcntl
RUN apt-get update && apt-get install -y \
        procps
RUN mv /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
RUN sed -i 's/;date.timezone =/date.timezone = "Europe\/Paris"/g' /usr/local/etc/php/php.ini