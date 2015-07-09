#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php async-message-worker-v2.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$host = '127.0.0.1';
$port = 5672;
$user = 'guest';
$pass = 'guest';

// $handler = new \Burrow\RabbitMQ\AmqpAsyncHandler($host, $port, $user, $pass, $argv[1]);

// $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection($host, $port, $user, $pass);
// $channel = $connection->channel();
// $messageProvider = new \Swarrot\Broker\MessageProvider\PhpAmqpLibMessageProvider($channel, $argv[1]);

$credentials = array('host' => $host, 'port' => $port, 'vhost' => '/', 'login' => $user, 'password' => $pass);
$connection = new \AMQPConnection($credentials);
$connection->connect();
$channel = new \AMQPChannel($connection);
$queue = new \AMQPQueue($channel); $queue->setName($argv[1]);
$messageProvider = new \Swarrot\Broker\MessageProvider\PeclPackageMessageProvider($queue);

$handler = new \Burrow\Swarrot\SwarrotAsyncHandler($messageProvider);

$handler->registerConsumer(new \Burrow\Examples\EchoConsumer());
$worker = new \Burrow\Worker($handler);
$worker->run();
