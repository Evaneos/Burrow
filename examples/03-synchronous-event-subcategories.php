<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dispatcher = new \Burrow\Synchronous\EventDispatcher();

/*
 Events may have subcategories.
 Events with subcategories will be dispatched to listeners subscribing to their main category and each of their subcategories.

 Listeners can register to none, one, or several subcategories.

 Here an example of actions depending on log levels
*/


// register listener only for 'info', 'notice', 'debug' subcategories
$dispatcher->on('logs', array('info', 'notice', 'debug'), function(\Burrow\Event $event) {
    echo 'LISTENER 1 - info/notice/debug' . PHP_EOL;
});

// register listener only for 'warning', 'error' subcategories
$dispatcher->on('logs', array('warning', 'error'), function(\Burrow\Event $event) {
    echo 'LISTENER 2 - warning/error' . PHP_EOL;
});

// register listener for all logs subcategories
$dispatcher->on('logs', array(), function(\Burrow\Event $event) {
    echo 'LISTENER 3 - All subcategories' . PHP_EOL;
});

// Will be dispatched to Listeners 1 and 3
$event = new \Burrow\Event('logs', array('param' => 'value'), 'info');
$dispatcher->dispatch($event);

// Will be dispatched to Listeners 1, 2 and 3
$event = new \Burrow\Event('logs', array('param' => 'value'), array('info', 'warning'));
$dispatcher->dispatch($event);

// Will be dispatched to Listener 3
$event = new \Burrow\Event('logs', array('param' => 'value'));
$dispatcher->dispatch($event);
