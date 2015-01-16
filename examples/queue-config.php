#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php queue-config.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$exchangeName = 'xchange';
$queueName = $argv[1];

$admin = new \Burrow\RabbitMQ\AmqpAdministrator('127.0.0.1', 5672, 'guest', 'guest');
$admin->declareExchange($exchangeName);
$admin->declareAndBindQueue($exchangeName, $queueName);

echo 'Added queue "'.$queueName.'" to exchange'."\n";
