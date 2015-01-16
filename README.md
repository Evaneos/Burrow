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

### Declare an exchange and bind a queue with RabbitMQ
```php
$admin = new \Burrow\RabbitMQ\AmqpAdministrator('127.0.0.1', 5672, 'guest', 'guest');
$admin->declareExchange('exchange');
$admin->declareAndBindQueue('exchange', 'my_queue');
```

#### Dispatching an event with RabbitMQ
```php
$publisher = new \Burrow\RabbitMQ\AmqpAsyncPublisher('127.0.0.1', 5672, 'guest', 'guest', 'exchange');
$publisher->publish('my_event');
```

#### Write a daemon to consume events from RabbitMQ
```php
$handler = new \Burrow\RabbitMQ\AmqpAsyncHandler('127.0.0.1', 5672, 'guest', 'guest', 'my_queue');
$handler->registerConsumer(new \Burrow\Examples\EchoConsumer());
$worker = new \Burrow\Worker($handler);
$worker->run();
```

In the command-line, launch both scripts from a different terminal, the message 'my_message', should be displayed in the worker terminal.