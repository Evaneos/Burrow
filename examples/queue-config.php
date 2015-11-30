#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php queue-config.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/base-info.php';

$queueName = $argv[1];

$admin = new \Burrow\RabbitMQ\AmqpAdministrator($host, $port, $user, $pass);
$admin->declareExchange($exchange);
$admin->declareAndBindQueue($exchange, $queueName);

echo 'Added queue "'.$queueName.'" to exchange'."\n";
