<?php

namespace Burrow\Test;

use Burrow\Message;
use Faker\Factory;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $body;

    /** @var string */
    private $routingKey;

    /** @var string[] */
    private $headers;

    /** @var string */
    private $correlationId;

    /** @var string */
    private $replyTo;

    /** @var string */
    private $queue;

    /** @var string */
    private $deliveryTag;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->body = $faker->realText();
        $this->routingKey = $faker->word;
        $this->headers = [ $faker->word => $faker->randomNumber() ];
        $this->correlationId = $faker->uuid;
        $this->replyTo = $faker->uuid;
        $this->queue = $faker->word;
        $this->deliveryTag = $faker->randomNumber();
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
    public function itShouldBuildADefaultMessage()
    {
        $message = new Message($this->body);

        $this->assertEquals($this->body, $message->getBody());
        $this->assertEquals('', $message->getRoutingKey());
        $this->assertEquals([], $message->getHeaders());
        $this->assertEquals('', $message->getCorrelationId());
        $this->assertEquals('', $message->getReplyTo());
        $this->assertNull($message->getQueue());
        $this->assertNull($message->getDeliveryTag());
    }

    /**
     * @test
     */
    public function itShouldBuildAMessage()
    {
        $message = new Message(
            $this->body,
            $this->routingKey,
            $this->headers,
            $this->correlationId,
            $this->replyTo
        );

        $this->assertEquals($this->body, $message->getBody());
        $this->assertEquals($this->routingKey, $message->getRoutingKey());
        $this->assertEquals($this->headers, $message->getHeaders());
        $this->assertEquals($this->correlationId, $message->getCorrelationId());
        $this->assertEquals($this->replyTo, $message->getReplyTo());
        $this->assertNull($message->getQueue());
        $this->assertNull($message->getDeliveryTag());
    }

    /**
     * @test
     */
    public function itShouldFailBuildingAMessageIfHeadersKeyAreInvalid()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        new Message(
            $this->body,
            $this->routingKey,
            [ 0 => [] ]
        );
    }

    /**
     * @test
     */
    public function itShouldFailBuildingAMessageIfHeadersValueAreInvalid()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        new Message(
            $this->body,
            $this->routingKey,
            [ 'a' => [] ]
        );
    }

    /**
     * @test
     */
    public function itShouldBeAbleToSetDeliveryTagAndQueue()
    {
        $message = new Message($this->body, $this->routingKey, [ 'a' => true ]);

        $message->setQueue($this->queue);
        $message->setDeliveryTag($this->deliveryTag);

        $this->assertEquals($this->queue, $message->getQueue());
        $this->assertEquals($this->deliveryTag, $message->getDeliveryTag());
    }
}
