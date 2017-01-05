#!/usr/bin/php
<?php

date_default_timezone_set('Europe/Paris');

use Burrow\Driver\PeclAmqpDriver;
use Burrow\Publisher\SyncPublisher;

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php sync-message-sender-pecl.php <nbevents:int>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$connection = new AMQPConnection();
$connection->setHost('default');
$connection->setLogin('guest');
$connection->setPassword('guest');

$driver = new PeclAmqpDriver($connection);
$publisher = new SyncPublisher($driver, 'xchange');

for ($i = 0; $i < $argv[1]; ++$i) {
    echo $publisher->publish('event #'.$i, '', ['test' => 'testValue'])."\n";
}
