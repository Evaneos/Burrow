#!/usr/bin/php
<?php

date_default_timezone_set('Europe/Paris');

use Burrow\Daemon\QueueHandlingDaemon;
use Burrow\Driver\DriverFactory;
use Burrow\Examples\EchoConsumer;
use Burrow\Handler\HandlerBuilder;
use Evaneos\Daemon\Monitor\MemoryMonitor;
use Evaneos\Daemon\Worker;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php async-message-worker.php <queue-name:string>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';

$logger = new Logger('TEST');
$logger->pushHandler(new StreamHandler('php://output', 0));

$driver = DriverFactory::getDriver([
    'host' => 'default',
    'port' => '5672',
    'user' => 'guest',
    'pwd' => 'guest'
]);
$handlerBuilder = new HandlerBuilder($driver);
$handler = $handlerBuilder->async(new EchoConsumer())->log($logger)->build();
$daemon = new QueueHandlingDaemon($driver, $handler, $argv[1]);
$daemon->setLogger($logger);
$daemon->setMonitor(new MemoryMonitor($logger));

$worker = new Worker($daemon);
$worker->run();
