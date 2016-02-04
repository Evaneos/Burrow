#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php amqplib-sync-message-sender-v2.php <nbevents:int>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/base-info.php';

$publisher = new \Burrow\RabbitMQ\AmqpSyncPublisher($host, $port, $user, $pass, $exchange);

for ($i = 0; $i < $argv[1]; ++$i) {
    echo $publisher->publish('event #'.$i)."\n";
}
