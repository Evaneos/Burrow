<?php

use Burrow\Daemon\QueueHandlingDaemon;
use Burrow\Driver\DriverFactory;
use Burrow\Handler\HandlerBuilder;
use Burrow\Test\Integration\SleepConsumer;
use Evaneos\Daemon\Worker;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/../../vendor/autoload.php';

$driver = DriverFactory::getDriver([
    'host' => 'rabbitmq',
    'port' => '5672',
    'user' => 'guest',
    'pwd' => 'guest'
]);

$handler = new HandlerBuilder($driver);
$handler->async();
$consumer = new SleepConsumer();
$daemon = new QueueHandlingDaemon($driver, $handler->build($consumer),'queue_test');
$worker = new Worker($daemon);

$logger = new Logger('test', [new StreamHandler('php://output')]);
$consumer->setLogger($logger);
$daemon->setLogger($logger);
$worker->setLogger($logger);

$worker->run();
