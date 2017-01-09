<?php

namespace Burrow\Test\Handler;

use Burrow\ConsumeOptions;
use Burrow\Driver;
use Burrow\Handler\AckHandler;
use Burrow\Message;
use Burrow\QueueHandler;
use Faker\Factory;
use Mockery\Mock;

class AckHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Message */
    private $message;

    /** @var bool */
    private $result;

    /** @var ConsumeOptions */
    private $consumerOptions;

    /** @var QueueHandler | Mock */
    private $handler;

    /** @var Driver | Mock */
    private $driver;

    /** @var bool */
    private $requeueOnFailure;

    /** @var AckHandler */
    private $serviceUnderTest;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->message = new Message($faker->text());
        $this->result = false;
        $this->consumerOptions = new ConsumeOptions();

        $this->handler = \Mockery::mock(QueueHandler::class);
        $this->driver = \Mockery::mock(Driver::class);
        $this->requeueOnFailure = true;

        $this->serviceUnderTest = new AckHandler($this->handler, $this->driver, $this->requeueOnFailure);
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
    public function itShouldHandleMessageAndAck()
    {
        $this->givenInnerHandlerWillHandleMessage();

        $this->assertDriverWillAckMessage();

        $result = $this->serviceUnderTest->handle($this->message);

        $this->assertEquals($this->result, $result);
    }

    /**
     * @test
     */
    public function itShouldNackIfMessageHandlingFails()
    {
        $this->givenInnerHandlerWillFailHandlingMessage();
        $this->assertDriverWillNackMessage();

        $this->setExpectedException(\Exception::class);

        $this->serviceUnderTest->handle($this->message);
    }

    /**
     * @test
     */
    public function itShouldDisableAutoAck()
    {
        $this->givenInnerHandlerWillReturnOptions();

        $options = $this->serviceUnderTest->options($this->consumerOptions);

        $this->assertFalse($options->isAutoAck());
    }

    private function givenInnerHandlerWillHandleMessage()
    {
        $this->handler
            ->shouldReceive('handle')
            ->with($this->message)
            ->andReturn($this->result);
    }

    private function givenInnerHandlerWillFailHandlingMessage()
    {
        $this->handler
            ->shouldReceive('handle')
            ->with($this->message)
            ->andReturn($this->result)
            ->andThrow(\Exception::class);
    }

    private function givenInnerHandlerWillReturnOptions()
    {
        $this->handler
            ->shouldReceive('options')
            ->with($this->consumerOptions)
            ->andReturn($this->consumerOptions);
    }

    private function assertDriverWillAckMessage()
    {
        $this->driver
            ->shouldReceive('ack')
            ->with($this->message)
            ->once();
    }

    private function assertDriverWillNackMessage()
    {
        $this->driver
            ->shouldReceive('nack')
            ->with($this->message, $this->requeueOnFailure)
            ->once();
    }
}
