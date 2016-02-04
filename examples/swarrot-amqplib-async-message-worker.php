#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php swarrot-amqplib-async-message-worker-v2.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/base-info.php';

$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection($host, $port, $user, $pass);
$messageProvider = new \Swarrot\Broker\MessageProvider\PhpAmqpLibMessageProvider($connection->channel(), $argv[1]);

$handler = new \Burrow\Swarrot\SwarrotAsyncHandler($messageProvider);

$handler->registerConsumer(new \Burrow\Examples\EchoConsumer());
$worker = new \Burrow\Worker($handler);
$worker->run();
