<?php

namespace Burrow\Test\Handler;

use Burrow\ConsumeOptions;
use Burrow\Driver;
use Burrow\Exception\ConsumerException;
use Burrow\Handler\SyncConsumerHandler;
use Burrow\Message;
use Burrow\QueueConsumer;
use Faker\Factory;
use Mockery\Mock;

class SyncConsumerHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $correlationId;

    /** @var string */
    private $replyTo;

    /** @var Message */
    private $message;

    /** @var string */
    private $returnValue;

    /** @var ConsumeOptions */
    private $consumerOptions;

    /** @var QueueConsumer | Mock */
    private $consumer;

    /** @var Driver | Mock */
    private $driver;

    /** @var SyncConsumerHandler */
    private $serviceUnderTest;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->correlationId = $faker->uuid;
        $this->replyTo = $faker->word;
        $this->message = new Message($faker->text(), '', [], $this->correlationId, $this->replyTo);
        $this->returnValue = $faker->word;
        $this->consumerOptions = new ConsumeOptions();

        $this->consumer = \Mockery::mock(QueueConsumer::class);
        $this->driver = \Mockery::mock(Driver::class);

        $this->serviceUnderTest = new SyncConsumerHandler($this->consumer, $this->driver);
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
        $this->assertItWillPublishReturnValueBack();

        $result = $this->serviceUnderTest->handle($this->message);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function itShouldFailIfNotASyncMessage()
    {
        $this->message = new Message('message');

        $this->assertItWillConsumeMessage();

        $this->setExpectedException(ConsumerException::class);

        $this->serviceUnderTest->handle($this->message);
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
            ->andReturn($this->returnValue)
            ->once();
    }

    private function assertItWillPublishReturnValueBack()
    {
        $this->driver
            ->shouldReceive('publish')
            ->with(
                '',
                \Mockery::on(function (Message $message) {
                    $this->assertEquals($this->returnValue, $message->getBody());
                    $this->assertEquals($this->replyTo, $message->getRoutingKey());
                    $this->assertEquals([], $message->getHeaders());
                    $this->assertEquals($this->correlationId, $message->getCorrelationId());

                    return true;
                })
            )
            ->once();
    }
}
