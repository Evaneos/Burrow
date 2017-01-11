<?php

namespace Burrow\Test\Publisher;

use Burrow\Driver;
use Burrow\Message;
use Burrow\Publisher\SyncPublisher;
use Faker\Factory;
use Mockery\Mock;

class SyncPublisherTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $data;

    /** @var string */
    private $routingKey;

    /** @var string[] */
    private $headers;

    /** @var string */
    private $replyTo;

    /** @var Message */
    private $message;

    /** @var string */
    private $badReturnValue;

    /** @var string */
    private $returnValue;

    /** @var Driver | Mock */
    private $driver;

    /** @var string */
    private $exchangeName;

    /** @var int */
    private $timeout;

    /** @var SyncPublisher */
    private $serviceUnderTest;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->data = $faker->word;
        $this->routingKey = $faker->word;
        $this->headers = [];

        $this->replyTo = $faker->word;
        $this->badReturnValue = $faker->word;
        $this->returnValue = $faker->word;

        $this->driver = \Mockery::mock(Driver::class);
        $this->exchangeName = $faker->word;
        $this->timeout = $faker->randomNumber();

        $this->serviceUnderTest = new SyncPublisher($this->driver, $this->exchangeName, $this->timeout);
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
    public function itShouldPublish()
    {
        $this->assertAReturnQueueWillBeDeclared();
        $this->assertDriverWillPublishMessage();
        $this->assertDriverConsumesReturnQueue();

        $returnValue = $this->serviceUnderTest->publish($this->data, $this->routingKey, $this->headers);

        $this->assertEquals($this->returnValue, $returnValue);
    }

    private function assertAReturnQueueWillBeDeclared()
    {
        $this->driver
            ->shouldReceive('declareSimpleQueue')
            ->with('', Driver::QUEUE_EXCLUSIVE)
            ->andReturn($this->replyTo)
            ->once();
    }

    private function assertDriverWillPublishMessage()
    {
        $this->driver
            ->shouldReceive('publish')
            ->with(
                $this->exchangeName,
                \Mockery::on(function (Message $message) {
                    $this->assertEquals($this->data, $message->getBody());
                    $this->assertEquals($this->routingKey, $message->getRoutingKey());
                    $this->assertEquals($this->headers, $message->getHeaders());
                    $this->assertNotEmpty($message->getCorrelationId());
                    $this->assertEquals($this->replyTo, $message->getReplyTo());

                    $this->message = $message;

                    return true;
                })
            )
            ->once();
    }

    private function assertDriverConsumesReturnQueue()
    {
        $this->driver
            ->shouldReceive('consume')
            ->with(
                $this->replyTo,
                \Mockery::on(function (callable $callable) {
                    $this->assertTrue($callable(new Message($this->badReturnValue)));
                    $this->assertFalse(
                        $callable(
                            new Message(
                                $this->returnValue,
                                '',
                                [],
                                $this->message->getCorrelationId()
                            )
                        )
                    );

                    return true;
                }),
                $this->timeout
            );
    }
}
