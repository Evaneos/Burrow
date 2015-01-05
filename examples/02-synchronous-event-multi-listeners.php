#!/usr/bin/php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dispatcher = new \Burrow\Synchronous\EventDispatcher();

// register listener
$dispatcher->on('myEvent', function(\Burrow\Event $event) {
    echo 'I am the first listener' . PHP_EOL;
});

$dispatcher->on('myEvent', function(\Burrow\Event $event) {
    echo 'I am the second listener' . PHP_EOL;
});


// dispatch event
$event = new \Burrow\Event('myEvent', array('param' => 'value'));
$dispatcher->dispatch($event);

