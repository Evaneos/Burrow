#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage : php 03-async-sender.php nbevents\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$amqpService = new \Burrow\RabbitMQ\AmqpService('127.0.0.1', 5672, 'guest', 'guest');

$dispatcher = new \Burrow\RabbitMQ\EventDispatcher($amqpService);

$event = new \Burrow\Event('image.resize', array('name' => 'msg'));

for ($i = 0; $i < $argv[1]; ++$i) {
    $dispatcher->dispatch($event);
}
