#!/usr/bin/php
<?php

if (!isset($argv[1]) || !isset($argv[2])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php 05-async-message-sender.php <nbevents:int> <queue_name:string> [<queue_name2> <queue_name3> ...]\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$queueService = new \Burrow\RabbitMQ\AmqpExchangeService('127.0.0.1', 5672, 'guest', 'guest', 'xchange');

for ($i=2; $i<10; $i++) {
    if (isset($argv[$i])) {
        $queueService->addQueue($argv[$i]);
        echo "Added queue: ".$argv[$i]."\n";
    } else {
        break;
    }
}

for ($i = 0; $i < $argv[1]; ++$i) {
    $queueService->publish('event #'.$i);
}
