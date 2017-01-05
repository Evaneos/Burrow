#!/usr/bin/php
<?php

date_default_timezone_set('Europe/Paris');

use Burrow\Driver\DriverFactory;
use Burrow\Examples\EchoConsumer;
use Burrow\Handler\UniversalHandler;
use Burrow\Worker;
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
$handler = new UniversalHandler($driver, $argv[1]);
$handler->registerConsumer(new EchoConsumer());
$handler->setLogger($logger);

$worker = new Worker($handler);
$worker->run();
