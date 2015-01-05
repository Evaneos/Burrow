#!/usr/bin/php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$amqpService = new \Burrow\RabbitMQ\AmqpService('127.0.0.1', 5672, 'guest', 'guest');
$worker = new \Burrow\RabbitMQ\Worker($amqpService, 'image.upload', 'archive');

$i=0;
$worker->registerListener(function(\Burrow\Event $event) use (&$i) {
    echo 'event ' . $event->getCategory() . ' ' . ++$i . PHP_EOL;
});

$worker->daemonize();
