<?php

namespace Burrow\Test\Integration;

use Assert\Assertion;
use Burrow\Driver\DriverFactory;
use Burrow\Publisher\AsyncPublisher;
use Burrow\QueuePublisher;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use Symfony\Component\Process\Process;

class StopTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueuePublisher
     */
    private $publisher;

    public function setUp()
    {
        $driver = DriverFactory::getDriver([
            'host' => 'rabbitmq',
            'port' => '5672',
            'user' => 'guest',
            'pwd' => 'guest'
        ]);
        $this->publisher = new AsyncPublisher($driver, 'exchange_test');
        $driver->deleteQueue('queue_test');
        $driver->declareAndBindQueue('exchange_test', 'queue_test');
    }

    /**
     * @test
     */
    public function it_stops_gracefully()
    {
        $stop = false;
        $gracefullyStopped = false;
        $messagesConsumed = 0;

        $this->publisher->publish('2');
        $this->publisher->publish('2');

        $worker = new Process('exec php tests/integration/sleep.php', null, null, null, 20);
        $worker->start();

        $worker->wait(function ($type, $buffer) use (&$stop, $worker, &$gracefullyStopped, &$messagesConsumed) {
            if (strpos($buffer, SleepConsumer::START_CONSUME)) {
                Assertion::same(0, $messagesConsumed, 'Second message is consumed');
                $stop = true;
                $worker->signal(15);
            }

            if (true === $stop && strpos($buffer,SleepConsumer::END_CONSUME)) {
                $messagesConsumed++;
                $gracefullyStopped = true;
            }
        });

        $worker->stop();
        Assertion::true($gracefullyStopped);
    }
}
