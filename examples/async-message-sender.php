#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php async-message-sender-v2.php <nbevents:int>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$publisher = new \Burrow\RabbitMQ\AmqpAsyncPublisher('default', 5672, 'guest', 'guest', 'xchange');

for ($i = 0; $i < $argv[1]; ++$i) {
    $publisher->publish('event #'.$i, '', ['test' => 'testValue']);
}
