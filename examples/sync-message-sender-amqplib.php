#!/usr/bin/php
<?php

date_default_timezone_set('Europe/Paris');

use Burrow\Driver\PhpAmqpLibDriver;
use Burrow\Publisher\SyncPublisher;
use PhpAmqpLib\Connection\AMQPLazyConnection;

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php sync-message-sender-amqplib.php <nbevents:int>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$driver = new PhpAmqpLibDriver(new AMQPLazyConnection('default', 5672, 'guest', 'guest'));
$publisher = new SyncPublisher($driver, 'xchange');

for ($i = 0; $i < $argv[1]; ++$i) {
    echo $publisher->publish('event #'.$i, '', ['test' => 'testValue'])."\n";
}
