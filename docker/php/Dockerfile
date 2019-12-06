FROM php:5.5-alpine

# PHP extensions
RUN docker-php-ext-install \
        pcntl \
        sockets \
        bcmath

# AMQP
RUN printf "\n%s\n%s" "@edge http://dl-cdn.alpinelinux.org/alpine/edge/main" "@testing http://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories \
    && apk --update upgrade \
RUN apk add rabbitmq-c@edge rabbitmq-c-dev@edge \
        && pecl install amqp


# Xdebug
RUN pecl channel-update pecl.php.net

RUN apk --update upgrade \
    && apk add  \
        autoconf \
        automake \
        make \
        gcc \
        g++

RUN pecl install xdebug-2.5.5 \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

# Php timezone
RUN echo date.timezone="UTC" > /usr/local/etc/php/conf.d/timezone.ini

# Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

#Dockerize
RUN apk add --no-cache openssl
ENV DOCKERIZESHA256SUM="dddbf178ecfd55fa6670b01ac08fef63ef9490212426b9fab8a602345409da8f"
RUN wget -O dockerize.tar.gz https://github.com/jwilder/dockerize/releases/download/v0.6.1/dockerize-alpine-linux-amd64-v0.6.1.tar.gz \
    && echo "$DOCKERIZESHA256SUM *dockerize.tar.gz" | sha256sum -c - \
    && tar -C /usr/local/bin -xzvf dockerize.tar.gz \
    && rm dockerize.tar.gz

WORKDIR /app