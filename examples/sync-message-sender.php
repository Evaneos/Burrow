#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php sync-message-sender-v2.php <nbevents:int>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$host = '127.0.0.1';
$port = 5672;
$user = 'guest';
$pass = 'guest';
$exchange = 'xchange';

// $publisher = new \Burrow\RabbitMQ\AmqpSyncPublisher('127.0.0.1', 5672, 'guest', 'guest', 'xchange');

$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection($host, $port, $user, $pass);
$channel = $connection->channel();
$messagePublisher = new \Burrow\Swarrot\MessagePublisher\AmqplibRpcMessagePublisher($channel, $exchange);
$publisher = new \Burrow\Swarrot\SwarrotSyncPublisher($messagePublisher);

for ($i = 0; $i < $argv[1]; ++$i) {
    echo $publisher->publish('event #'.$i)."\n";
}
