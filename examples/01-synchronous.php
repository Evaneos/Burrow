<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dispatcher = new \Burrow\Synchronous\EventDispatcher();

// register listener
$dispatcher->on('myEvent', function(\Burrow\Event $event) {
    echo 'I received an event : ' . PHP_EOL;
    var_dump($event);
});

// dispatch event
$event = new \Burrow\Event('myEvent', array('param' => 'value'));
$dispatcher->dispatch($event);

