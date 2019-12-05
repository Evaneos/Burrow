FROM php:5.5-alpine

RUN docker-php-ext-install bcmath pcntl

RUN echo date.timezone="UTC" > /usr/local/etc/php/conf.d/timezone.ini

#Dockerize
RUN apk add --no-cache openssl
ENV DOCKERIZESHA256SUM="dddbf178ecfd55fa6670b01ac08fef63ef9490212426b9fab8a602345409da8f"
RUN wget -O dockerize.tar.gz https://github.com/jwilder/dockerize/releases/download/v0.6.1/dockerize-alpine-linux-amd64-v0.6.1.tar.gz \
    && echo "$DOCKERIZESHA256SUM *dockerize.tar.gz" | sha256sum -c - \
    && tar -C /usr/local/bin -xzvf dockerize.tar.gz \
    && rm dockerize.tar.gz