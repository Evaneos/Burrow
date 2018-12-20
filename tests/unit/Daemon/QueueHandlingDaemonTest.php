<?php

namespace Burrow\Test\Daemon;

use Burrow\ConsumeOptions;
use Burrow\Daemon\QueueHandlingDaemon;
use Burrow\Driver;
use Burrow\Event\DaemonStarted;
use Burrow\Event\DaemonStopped;
use Burrow\Event\MessageConsumed;
use Burrow\Event\MessageReceived;
use Burrow\Message;
use Burrow\QueueHandler;
use Evaneos\Daemon\DaemonMonitor;
use Faker\Factory;
use League\Event\EmitterInterface;
use Mockery\Matcher\MustBe;
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

    /** @var EmitterInterface | Mock */
    private $eventEmitter;

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

        $this->eventEmitter = \Mockery::mock(EmitterInterface::class);

        $this->serviceUnderTest = new QueueHandlingDaemon(
            $this->driver,
            $this->handler,
            $this->queueName,
            $this->eventEmitter
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

        $this->assertItWillEmitEvents();
        $this->assertMessageWillBeHandled();
        $this->assertDriverWillConsume();
        $this->assertDriverWillBeClosed();

        $this->serviceUnderTest->start();
    }

    /**
     * @test
     */
    public function itShouldMonitorTheDaemonIfProvidedAMonitor()
    {
        $this->givenHandlerWillReturnConsumeOptions();

        $this->assertItWillEmitEvents();
        $this->assertMessageWillBeHandled();
        $this->assertDriverWillConsume();
        $this->assertDriverWillBeClosed();

        $this->assertItWillMonitorTheConsumption();

        $this->serviceUnderTest->setMonitor($this->monitor);
        $this->serviceUnderTest->start();
    }

    /**
     * @test
     */
    public function itShouldEmitDaemonStoppedWhenStop()
    {
        $this->assertDriverWillBeClosed();
        $this->assertItWillEmitDaemonStoppedEvent();

        $this->serviceUnderTest->stop();
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

    protected function assertDriverWillBeClosed()
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

    private function assertItWillEmitEvents()
    {
        $this->eventEmitter
            ->shouldReceive('emit')
            ->with(new MustBe(new DaemonStarted()))
            ->once();

        $this->eventEmitter
            ->shouldReceive('emit')
            ->with(new MustBe(new MessageReceived()))
            ->once();

        $this->eventEmitter
            ->shouldReceive('emit')
            ->with(new MustBe(new MessageConsumed()))
            ->once();

        $this->eventEmitter
            ->shouldReceive('emit')
            ->with(new MustBe(new DaemonStopped()))
            ->once();
    }

    private function assertItWillEmitDaemonStoppedEvent()
    {
        $this->eventEmitter
            ->shouldReceive('emit')
            ->with(new MustBe(new DaemonStopped()))
            ->once();
    }
}
