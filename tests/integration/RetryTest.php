<?php

namespace Burrow\Test\Integration;

use Burrow\Driver\PhpAmqpLibDriver;
use Burrow\Publisher\AsyncPublisher;
use Burrow\QueuePublisher;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use Symfony\Component\Process\Process;

class RetryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueuePublisher
     */
    private $publisher;
    /** @var AMQPLazyConnection */
    private $connection;

    public function setUp()
    {
        if (!class_exists(AMQPConnection::class)) {
            $this->markTestSkipped('No AMQPConnection');

            return;
        }


        $this->connection = new AMQPLazyConnection(
            'rabbitmq',
            '5672',
            'guest',
            'guest'
        );
        $driver = new PhpAmqpLibDriver(
            $this->connection
        );

        $this->publisher = new AsyncPublisher($driver, 'exchange_test');

        $driver->deleteQueue('queue_test');
        $driver->declareAndBindQueue('exchange_test', 'queue_test');
    }

    /**
     * @test
     */
    public function it_retries_to_publish_an_event()
    {
        $this->publisher->publish('2');
        $this->connection->getIO()->close(); //will cause an exception to get a retry
        $this->publisher->publish('2');

        $worker = new Process('exec php tests/integration/sleep.php', null, null, null, 20);
        $worker->start();

        $messagesConsumed = 0;
        $worker->wait(
            function ($type, $buffer) use ($worker, &$messagesConsumed) {
                if (strpos($buffer, SleepConsumer::START_CONSUME)) {
                    $messagesConsumed++;
                    if ($messagesConsumed >= 2) {
                        $worker->signal(15);
                    }
                }

            }
        );

        self::assertEquals(2, $messagesConsumed);
    }
}
