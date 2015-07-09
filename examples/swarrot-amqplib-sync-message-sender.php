#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php swarrot-amqplib-sync-message-sender-v2.php <nbevents:int>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/base-info.php';

$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection($host, $port, $user, $pass);
$messagePublisher = new \Burrow\Swarrot\MessagePublisher\AmqplibRpcMessagePublisher($connection->channel(), $exchange);

$publisher = new \Burrow\Swarrot\SwarrotSyncPublisher($messagePublisher);

for ($i = 0; $i < $argv[1]; ++$i) {
    echo $publisher->publish('event #'.$i)."\n";
}
