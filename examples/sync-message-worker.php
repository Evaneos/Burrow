#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php sync-message-worker-v2.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$handler = new \Burrow\RabbitMQ\AmqpSyncHandler('default', 5672, 'guest', 'guest', $argv[1]);
$handler->registerConsumer(new \Burrow\Examples\ReturnConsumer());
$worker = new \Burrow\Worker($handler);
$worker->run();
