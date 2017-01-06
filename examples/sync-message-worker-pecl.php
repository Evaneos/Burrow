#!/usr/bin/php
<?php

date_default_timezone_set('Europe/Paris');

use Burrow\Daemon\QueueHandlingDaemon;
use Burrow\Driver\PeclAmqpDriver;
use Burrow\Examples\ReturnConsumer;
use Burrow\Handler\HandlerBuilder;
use Burrow\Monitor\MemoryMonitor;
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
$handlerBuilder = new HandlerBuilder($driver);
$handler = $handlerBuilder->sync(new ReturnConsumer())->log($logger)->build();
$daemon = new QueueHandlingDaemon($driver, $handler, $argv[1]);
$daemon->setLogger($logger);
$daemon->setMonitor(new MemoryMonitor($logger));

$worker = new Worker($daemon);
$worker->run();
