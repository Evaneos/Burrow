#!/usr/bin/env bash
docker run --label php-amqp --name php-amqp --volume=$(pwd):/app -w /app --link rabbitmq:default --rm -it plab/docker-php:5.6-fpm-xdebug-amqp /bin/bash -c "docker-php-ext-install bcmath && /bin/bash"
docker exec -it php-amqp /bin/bash