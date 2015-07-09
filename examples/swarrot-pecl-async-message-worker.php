#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php swarrot-pecl-async-message-worker-v2.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/base-info.php';

$credentials = array('host' => $host, 'port' => $port, 'vhost' => '/', 'login' => $user, 'password' => $pass);
$connection = new \AMQPConnection($credentials); $connection->connect();
$channel = new \AMQPChannel($connection);
$queue = new \AMQPQueue($channel); $queue->setName($argv[1]);
$messageProvider = new \Swarrot\Broker\MessageProvider\PeclPackageMessageProvider($queue);

$handler = new \Burrow\Swarrot\SwarrotAsyncHandler($messageProvider);

$handler->registerConsumer(new \Burrow\Examples\EchoConsumer());
$worker = new \Burrow\Worker($handler);
$worker->run();
