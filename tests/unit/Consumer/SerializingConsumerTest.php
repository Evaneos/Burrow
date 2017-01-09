<?php

namespace Burrow\Test\Consumer;

use Burrow\Consumer\SerializingConsumer;
use Burrow\QueueConsumer;
use Burrow\Serializer;
use Faker\Factory;
use Mockery\Mock;

class SerializingConsumerTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $message;

    /** @var string */
    private $deserializedMessage;

    /** @var string[] */
    private $headers;

    /** @var string */
    private $returnValue;

    /** @var string */
    private $serializedReturnValue;

    /** @var QueueConsumer | Mock */
    private $consumer;

    /** @var Serializer | Mock */
    private $serializer;

    /** @var SerializingConsumer */
    private $serviceUnderTest;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->message = $faker->text();
        $this->deserializedMessage = $faker->text();
        $this->headers = [];
        $this->returnValue = $faker->text();
        $this->serializedReturnValue = $faker->text();

        $this->consumer = \Mockery::mock(QueueConsumer::class);
        $this->serializer = \Mockery::mock(Serializer::class);

        $this->serviceUnderTest = new SerializingConsumer($this->consumer, $this->serializer);
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
    public function itShouldDeserializeBeforeConsumingAndSerializeTheReturnValue()
    {
        $this->givenSerializerWillSerializeTheValueReturnedByTheConsumer();
        $this->givenSerializerWillDeserializeTheMessagePassedAsParameter();
        $this->givenTheConsumerWillReturnAValue();

        $returnValue = $this->serviceUnderTest->consume($this->message, $this->headers);

        $this->assertEquals($this->serializedReturnValue, $returnValue);
    }

    protected function givenSerializerWillSerializeTheValueReturnedByTheConsumer()
    {
        $this->serializer->shouldReceive('serialize')->with($this->returnValue)->andReturn($this->serializedReturnValue);
    }

    protected function givenSerializerWillDeserializeTheMessagePassedAsParameter()
    {
        $this->serializer->shouldReceive('deserialize')->with($this->message)->andReturn($this->deserializedMessage);
    }

    protected function givenTheConsumerWillReturnAValue()
    {
        $this->consumer->shouldReceive('consume')->with($this->deserializedMessage,
                                                        $this->headers)->andReturn($this->returnValue);
    }
}
