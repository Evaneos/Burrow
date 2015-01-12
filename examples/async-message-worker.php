#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php 05-async-message-worker.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$queueService = new \Burrow\RabbitMQ\AmqpSimpleService('127.0.0.1', 5672, 'guest', 'guest', $argv[1]);
$worker = new \Burrow\EchoWorker($queueService);
$worker->daemonize();
