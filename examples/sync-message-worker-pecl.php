#!/usr/bin/php
<?php

date_default_timezone_set('Europe/Paris');

use Burrow\Driver\PeclAmqpDriver;
use Burrow\Examples\ReturnConsumer;
use Burrow\Handler\UniversalHandler;
use Burrow\Worker;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php sync-message-worker-pecl.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$logger = new Logger('TEST');
$logger->pushHandler(new StreamHandler('php://output', 0));

$connection = new AMQPConnection();
$connection->setHost('default');
$connection->setLogin('guest');
$connection->setPassword('guest');

$driver = new PeclAmqpDriver($connection);
$handler = new UniversalHandler($driver, $argv[1]);
$handler->registerConsumer(new ReturnConsumer());
$handler->setLogger($logger);

$worker = new Worker($handler);
$worker->run();
