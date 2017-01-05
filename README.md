Burrow
======

Evaneos AMQP library able to use both [php-amqplib](https://github.com/php-amqplib/php-amqplib)
and [pecl amqp C librairy](https://github.com/pdezwart/php-amqp)

Installation
------------
```bash
composer require evaneos/burrow
```
Usage
-----

See examples directory for more details  
To test it, you may use a rabbitmq container, this one feets perfectly 
```bash
docker run -d -p 5672:5672 rabbitmq
```

### Declare an exchange and bind a queue with RabbitMQ
```php
$admin = DriverFactory::getDriver([
    'host' => 'localhost',
    'port' => '5672',
    'user' => 'guest',
    'pwd' => 'guest'
]);
$admin->declareExchange('exchange');
$admin->declareAndBindQueue('exchange', 'my_queue');
```

Asynchronous management
-----------------------

#### Dispatching an async message with RabbitMQ
```php
$driver = DriverFactory::getDriver([
    'host' => 'localhost',
    'port' => '5672',
    'user' => 'guest',
    'pwd' => 'guest'
]);
$publisher = new AsyncPublisher($driver, 'exchange');
$publisher->publish('message', 'routing_key', [ 'meta' => 'data' ]);
```

#### Write a daemon to consume async messages from RabbitMQ
```php
$driver = DriverFactory::getDriver([
    'host' => 'default',
    'port' => '5672',
    'user' => 'guest',
    'pwd' => 'guest'
]);
$handler = new UniversalHandler($driver, 'my_queue');
$handler->registerConsumer(new EchoConsumer());
$worker = new Worker($handler);
$worker->run();
```

In the command-line, launch both scripts from a different terminal, the message 'my_message', should be displayed in the worker terminal.

Synchronous management
-----------------------

#### Dispatching an async message with RabbitMQ
```php
$driver = DriverFactory::getDriver([
   'host' => 'default',
   'port' => '5672',
   'user' => 'guest',
   'pwd' => 'guest'
]);
$publisher = new SyncPublisher($driver, 'xchange');
$publisher->publish('my_message', 'routing_key', [ 'meta' => 'data' ]);
```

#### Write a daemon to consume async messages from RabbitMQ
```php
$driver = DriverFactory::getDriver([
   'host' => 'default',
   'port' => '5672',
   'user' => 'guest',
   'pwd' => 'guest'
]);
$handler = new UniversalHandler($driver, $argv[1]);
$handler->registerConsumer(new ReturnConsumer());
$worker = new Worker($handler);
$worker->run();
```

In the command-line, launch both scripts from a different terminal, the message 'my_message', should be displayed in the publisher terminal.

Examples
--------

All these examples are also available in the `example` directory.
