#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php swarrot-pecl-sync-message-worker-v2.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/base-info.php';

$credentials = array('host' => $host, 'port' => $port, 'vhost' => '/', 'login' => $user, 'password' => $pass);
$connection = new \AMQPConnection($credentials); $connection->connect();
$queue = new \AMQPQueue(new \AMQPChannel($connection)); $queue->setName($argv[1]);
$messageProvider = new \Burrow\Swarrot\MessageProvider\PeclRpcMessageProvider($queue);

$handler = new \Burrow\Swarrot\SwarrotSyncHandler($messageProvider);

$handler->registerConsumer(new \Burrow\Examples\ReturnConsumer());
$worker = new \Burrow\Worker($handler);
$worker->run();
