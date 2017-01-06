#!/usr/bin/php
<?php

date_default_timezone_set('Europe/Paris');

use Burrow\Driver\PeclAmqpDriver;
use Burrow\Examples\EchoConsumer;
use Burrow\Daemon\QueueHandlingDaemon;
use Burrow\Handler\AckHandler;
use Burrow\Handler\AsyncConsumerHandler;
use Burrow\Handler\HandlerBuilder;
use Burrow\Handler\StopOnExceptionHandler;
use Burrow\Worker;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php async-message-worker-pecl.php <queue-name:string>\n");
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
$handler = $handlerBuilder->async(new EchoConsumer())->log($logger)->build();
$daemon = new QueueHandlingDaemon($driver, $handler, $argv[1]);
$daemon->setLogger($logger);

$worker = new Worker($daemon);
$worker->run();
