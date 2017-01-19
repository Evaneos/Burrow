<?php

namespace Burrow\Test\Handler;

use Burrow\ConsumeOptions;
use Burrow\Handler\StopOnExceptionHandler;
use Burrow\Message;
use Burrow\QueueHandler;
use Faker\Factory;
use Mockery\Mock;

class StopOnExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Message */
    private $message;

    /** @var ConsumeOptions */
    private $consumerOptions;

    /** @var QueueHandler | Mock */
    private $handler;

    /** @var StopOnExceptionHandler */
    private $serviceUnderTest;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->message = new Message($faker->text());
        $this->consumerOptions = new ConsumeOptions();

        $this->handler = \Mockery::mock(QueueHandler::class);

        $this->serviceUnderTest = new StopOnExceptionHandler($this->handler);
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
    public function itShouldHandleTheMessage()
    {
        $this->assertItWillHandleMessage();

        $result = $this->serviceUnderTest->handle($this->message);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function itShouldStopTheHandler()
    {
        $this->givenItWillFailHandlingMessage();

        $result = $this->serviceUnderTest->handle($this->message);

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function itShouldNotChangeOptions()
    {
        $this->givenInnerHandlerWillReturnOptions();

        $options = $this->serviceUnderTest->options($this->consumerOptions);

        $this->assertEquals($this->consumerOptions, $options);
    }

    private function givenItWillFailHandlingMessage()
    {
        $this->handler
            ->shouldReceive('handle')
            ->with($this->message)
            ->andThrow(\Exception::class);
    }

    private function givenInnerHandlerWillReturnOptions()
    {
        $this->handler
            ->shouldReceive('options')
            ->with($this->consumerOptions)
            ->andReturn($this->consumerOptions);
    }

    private function assertItWillHandleMessage()
    {
        $this->handler
            ->shouldReceive('handle')
            ->with($this->message)
            ->once();
    }
}
