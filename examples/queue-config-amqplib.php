#!/usr/bin/php
<?php

date_default_timezone_set('Europe/Paris');

use Burrow\Driver\PhpAmqpLibDriver;
use PhpAmqpLib\Connection\AMQPLazyConnection;

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php queue-config.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$exchangeName = 'xchange';
$queueName = $argv[1];

$admin = new PhpAmqpLibDriver(new AMQPLazyConnection('default', 5672, 'guest', 'guest'));
$admin->declareExchange($exchangeName);
$admin->declareAndBindQueue($exchangeName, $queueName);

echo 'Added queue "'.$queueName.'" to exchange'."\n";
