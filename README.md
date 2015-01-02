Burrow
======

Evaneos (a)synchronous event library

Installation
------------
```bash
composer require evaneos/events dev-master
```
Usage
-----

See examples directory for more details  
To test asynchronous Events, you may use a rabbitmq container, this one feets perfectly 
```bash
docker run -d -p 5672:5672 dockerfile/rabbitmq
```
### Synchronous events
```php
$dispatcher = new \Burrow\Synchronous\EventDispatcher();

// Register an event listener
$dispatcher->on('myEvent', function(\Burrow\Event $event) {
    // do stuff with your Event
});

// Dispatch an event
$event = new \Burrow\Event('myEvent', array('param' => 'value'));
$dispatcher->dispatch($event);
```    

### Asynchronous events

Burrow offers an easy way to use asynchronous dispatching with RabbitMQ
    
#### Dispatching an event
```php
$amqpService = new \Burrow\RabbitMQ\AmqpService('127.0.0.1', 5672, 'guest', 'guest');

$dispatcher = new \Burrow\RabbitMQ\EventDispatcher($amqpService);

$event = new \Burrow\Event('eventName', array('param' => 'value'));
$dispatcher->dispatch($event);
```

#### Write a daemon to consume events
```php
$amqpService = new \Burrow\RabbitMQ\AmqpService('127.0.0.1', 5672, 'guest', 'guest');

$worker = new \Burrow\RabbitMQ\Worker($amqpService);

$worker->registerListener('eventName', function(\Burrow\Event $event) {
    // do stuff with your Event
});

$worker->daemonize();
```