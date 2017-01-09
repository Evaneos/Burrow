<?php

namespace Burrow\Test\Handler;

use Burrow\Driver;
use Burrow\Handler\ContinueOnExceptionHandler;
use Burrow\Handler\HandlerBuilder;
use Burrow\Handler\StopOnExceptionHandler;
use Burrow\QueueConsumer;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HandlerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var LoggerInterface */
    private $logger;

    /** @var QueueConsumer | Mock */
    private $consumer;

    /** @var Driver | Mock */
    private $driver;

    /** @var HandlerBuilder */
    private $serviceUnderTest;

    /**
     * Init
     */
    public function setUp()
    {
        $this->logger = new NullLogger();

        $this->consumer = \Mockery::mock(QueueConsumer::class);
        $this->driver = \Mockery::mock(Driver::class);

        $this->serviceUnderTest = new HandlerBuilder($this->driver);
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
    public function itShouldFailBuildingIfNotSpecifyingSyncOrAsync()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->serviceUnderTest->build();
    }

    /**
     * @test
     */
    public function itShouldBuildAsyncErrorStoppingAndNotRequeuingHandler()
    {
        $handler = $this->serviceUnderTest
            ->async($this->consumer)
            ->doNotRequeueOnFailure()
            ->log($this->logger)
            ->build();

        $this->assertInstanceOf(StopOnExceptionHandler::class, $handler);
    }

    /**
     * @test
     */
    public function itShouldBuildSyncErrorContinuing()
    {
        $handler = $this->serviceUnderTest
            ->sync($this->consumer)
            ->continueOnFailure()
            ->log($this->logger)
            ->build();

        $this->assertInstanceOf(ContinueOnExceptionHandler::class, $handler);
    }
}
