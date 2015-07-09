#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php swarrot-amqplib-async-message-sender-v2.php <nbevents:int>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/base-info.php';

$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection($host, $port, $user, $pass);
$channel = $connection->channel();
$messagePublisher = new \Swarrot\Broker\MessagePublisher\PhpAmqpLibMessagePublisher($channel, $exchange);

$publisher = new \Burrow\Swarrot\SwarrotAsyncPublisher($messagePublisher);

for ($i = 0; $i < $argv[1]; ++$i) {
    $publisher->publish('event #'.$i);
}
