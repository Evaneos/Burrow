Burrow
======

Evaneos message queue library (with a RabbitMQ Implementation)

Installation
------------
```bash
composer require evaneos/Burrow dev-master
```
Usage
-----

See examples directory for more details  
To test it, you may use a rabbitmq container, this one feets perfectly 
```bash
docker run -d -p 5672:5672 dockerfile/rabbitmq
```

#### Dispatching an event with RabbitMQ
```php
$queueService = new \Burrow\RabbitMQ\AmqpExchangeService('127.0.0.1', 5672, 'guest', 'guest', 'xchange');
$queueService->addQueue('my_queue');
$queueService->publish('my message');
```

#### Write a daemon to consume events from RabbitMQ
```php
$amqpService = new \Burrow\RabbitMQ\AmqpSimpleService('127.0.0.1', 5672, 'guest', 'guest', 'my_queue');
$worker = new \Burrow\EchoWorker($amqpService);
$worker->daemonize();
```

In the command-line, launch both scripts from a different terminal, the message 'my_message', should be displayed in the worker terminal.