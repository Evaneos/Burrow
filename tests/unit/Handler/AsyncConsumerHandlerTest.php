<?php

namespace Burrow\Test\Handler;

use Burrow\ConsumeOptions;
use Burrow\Handler\AsyncConsumerHandler;
use Burrow\Message;
use Burrow\QueueConsumer;
use Faker\Factory;
use Mockery\Mock;

class AsyncConsumerHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Message */
    private $message;

    /** @var ConsumeOptions */
    private $consumerOptions;

    /** @var QueueConsumer | Mock */
    private $consumer;

    /** @var AsyncConsumerHandler */
    private $serviceUnderTest;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->message = new Message($faker->text());
        $this->consumerOptions = new ConsumeOptions();

        $this->consumer = \Mockery::mock(QueueConsumer::class);

        $this->serviceUnderTest = new AsyncConsumerHandler($this->consumer);
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
    public function itShouldConsumeTheMessage()
    {
        $this->assertItWillConsumeMessage();

        $result = $this->serviceUnderTest->handle($this->message);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function itShouldNotChangeOptions()
    {
        $options = $this->serviceUnderTest->options($this->consumerOptions);

        $this->assertEquals($this->consumerOptions, $options);
    }

    private function assertItWillConsumeMessage()
    {
        $this->consumer
            ->shouldReceive('consume')
            ->with($this->message->getBody(), $this->message->getHeaders())
            ->once();
    }
}
