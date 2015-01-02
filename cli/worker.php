#!/usr/bin/php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$amqpService = new \Burrow\RabbitMQ\AmqpService('127.0.0.1', 5672, 'guest', 'guest');
$worker = new \Burrow\RabbitMQ\Worker($amqpService);

$i=0;
$worker->registerListener('image.resize', function(\Burrow\Event $event) use (&$i) {
    echo 'toto ' . ++$i;
});

$worker->daemonize();
