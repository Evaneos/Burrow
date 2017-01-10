#!/usr/bin/php
<?php

date_default_timezone_set('Europe/Paris');

use Burrow\Daemon\QueueHandlingDaemon;
use Burrow\Driver\PhpAmqpLibDriver;
use Burrow\Examples\ReturnConsumer;
use Burrow\Handler\HandlerBuilder;
use Evaneos\Daemon\Monitor\MemoryMonitor;
use Evaneos\Daemon\Worker;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PhpAmqpLib\Connection\AMQPLazyConnection;

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php sync-message-worker-amqplib.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$logger = new Logger('TEST');
$logger->pushHandler(new StreamHandler('php://output', 0));

$driver = new PhpAmqpLibDriver(new AMQPLazyConnection('default', 5672, 'guest', 'guest'));
$handlerBuilder = new HandlerBuilder($driver);
$handler = $handlerBuilder->sync()->log($logger)->build(new ReturnConsumer());
$daemon = new QueueHandlingDaemon($driver, $handler, $argv[1]);
$daemon->setLogger($logger);
$daemon->setMonitor(new MemoryMonitor($logger));

$worker = new Worker($daemon);
$worker->run();
