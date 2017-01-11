<?php

namespace Burrow\Test\Daemon;

use Burrow\ConsumeOptions;
use Burrow\Daemon\QueueHandlingDaemon;
use Burrow\Driver;
use Burrow\Message;
use Burrow\QueueHandler;
use Evaneos\Daemon\DaemonMonitor;
use Faker\Factory;
use Mockery\Mock;

class QueueHandlingDaemonTest extends \PHPUnit_Framework_TestCase
{
    /** @var Message */
    private $message;

    /** @var ConsumeOptions */
    private $consumeOptions;

    /** @var Driver | Mock */
    private $driver;

    /** @var QueueHandler | Mock */
    private $handler;

    /** @var string */
    private $queueName;

    /** @var DaemonMonitor | Mock */
    private $monitor;

    /** @var QueueHandlingDaemon */
    private $serviceUnderTest;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->message = new Message($faker->text());
        $this->consumeOptions = new ConsumeOptions();

        $this->driver = \Mockery::mock(Driver::class);
        $this->handler = \Mockery::mock(QueueHandler::class);
        $this->queueName = $faker->word;

        $this->monitor = \Mockery::mock(DaemonMonitor::class);

        $this->serviceUnderTest = new QueueHandlingDaemon(
            $this->driver,
            $this->handler,
            $this->queueName
        );
    }

    /**
     * Close
     */
    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldStartTheDaemon()
    {
        $this->givenHandlerWillReturnConsumeOptions();

        $this->assertMessageWillBeHandled();
        $this->assertDriverWillConsume();
        $this->assertDriverWillBeClosedOnceConsumingIsOver();

        $this->serviceUnderTest->start();
    }

    /**
     * @test
     */
    public function itShouldMonitorTheDaemonIfProvidedAMonitor()
    {
        $this->givenHandlerWillReturnConsumeOptions();

        $this->assertMessageWillBeHandled();
        $this->assertDriverWillConsume();
        $this->assertDriverWillBeClosedOnceConsumingIsOver();

        $this->assertItWillMonitorTheConsumption();

        $this->serviceUnderTest->setMonitor($this->monitor);
        $this->serviceUnderTest->start();
    }

    protected function givenHandlerWillReturnConsumeOptions()
    {
        $this->handler
            ->shouldReceive('options')
            ->andReturn($this->consumeOptions);
    }

    protected function assertMessageWillBeHandled()
    {
        $this->handler
            ->shouldReceive('handle')
            ->with($this->message)
            ->andReturn(true)
            ->once();
    }

    protected function assertDriverWillConsume()
    {
        $this->driver
            ->shouldReceive('consume')
            ->with(
                $this->queueName,
                \Mockery::on(function (callable $callback) {
                    return $callback($this->message);
                }),
                $this->consumeOptions->getTimeout(),
                $this->consumeOptions->isAutoAck()
            );
    }

    protected function assertDriverWillBeClosedOnceConsumingIsOver()
    {
        $this->driver
            ->shouldReceive('close')
            ->once();
    }

    private function assertItWillMonitorTheConsumption()
    {
        $this->monitor
            ->shouldReceive('monitor')
            ->with($this->serviceUnderTest, $this->message)
            ->once();
    }
}
