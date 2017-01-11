#!/usr/bin/php
<?php

date_default_timezone_set('Europe/Paris');

use Burrow\Driver\DriverFactory;

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php queue-config.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$exchangeName = 'xchange';
$queueName = $argv[1];

$admin = DriverFactory::getDriver([
    'host' => 'default',
    'port' => '5672',
    'user' => 'guest',
    'pwd' => 'guest'
]);
$admin->declareExchange($exchangeName);
$admin->declareAndBindQueue($exchangeName, $queueName);

echo 'Added queue "'.$queueName.'" to exchange'."\n";
