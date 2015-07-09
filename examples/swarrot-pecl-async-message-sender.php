#!/usr/bin/php
<?php

if (!isset($argv[1])) {
    $io = fopen('php://stderr', 'w+');
    fwrite($io, "usage: php swarrot-pecl-async-message-sender-v2.php <nbevents:int>\n");
    die;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/base-info.php';

$credentials = array('host' => $host, 'port' => $port, 'vhost' => '/', 'login' => $user, 'password' => $pass);
$connection = new \AMQPConnection($credentials); $connection->connect();
$xchange = new \AMQPExchange(new \AMQPChannel($connection)); $xchange->setName($exchange);
$messagePublisher = new \Swarrot\Broker\MessagePublisher\PeclPackageMessagePublisher($xchange);

$publisher = new \Burrow\Swarrot\SwarrotAsyncPublisher($messagePublisher);

for ($i = 0; $i < $argv[1]; ++$i) {
    $publisher->publish('event #'.$i);
}
