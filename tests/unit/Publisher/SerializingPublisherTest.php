<?php

namespace Burrow\Test\Publisher;

use Burrow\Publisher\SerializingPublisher;
use Burrow\QueuePublisher;
use Burrow\Serializer;
use Faker\Factory;
use Mockery\Mock;

class SerializingPublisherTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $data;

    /** @var string */
    private $routingKey;

    /** @var string[] */
    private $headers;

    /** @var string */
    private $serializedData;

    /** @var string */
    private $serializedReturnValue;

    /** @var string */
    private $returnValue;

    /** @var QueuePublisher | Mock */
    private $publisher;

    /** @var Serializer | Mock */
    private $serializer;

    /** @var SerializingPublisher */
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

        $this->serializedData = $faker->word;
        $this->serializedReturnValue = $faker->word;
        $this->returnValue = $faker->word;

        $this->publisher = \Mockery::mock(QueuePublisher::class);
        $this->serializer = \Mockery::mock(Serializer::class);

        $this->serviceUnderTest = new SerializingPublisher($this->publisher, $this->serializer);
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
    public function itShouldSerializeDataAndDeserializeReturnValue()
    {
        $this->assertSerializerWillSerializeData();
        $this->assertSerializerWillDeserializePublishingResult();
        $this->assertInnerPublisherWillPublishSerializedData();

        $returnValue = $this->serviceUnderTest->publish($this->data, $this->routingKey, $this->headers);

        $this->assertEquals($this->returnValue, $returnValue);
    }

    /**
     * @test
     */
    public function itShouldSerializeDataAndReturnNull()
    {
        $this->assertSerializerWillSerializeData();
        $this->assertSerializerWillNotDeserializeAnything();
        $this->assertInnerPublisherWillPublishSerializedDataAndReturnNull();

        $returnValue = $this->serviceUnderTest->publish($this->data, $this->routingKey, $this->headers);

        $this->assertNull($returnValue);
    }

    private function assertSerializerWillSerializeData()
    {
        $this->serializer
            ->shouldReceive('serialize')
            ->with($this->data)
            ->andReturn($this->serializedData)
            ->once();
    }

    private function assertSerializerWillDeserializePublishingResult()
    {
        $this->serializer
            ->shouldReceive('deserialize')
            ->with($this->serializedReturnValue)
            ->andReturn($this->returnValue)
            ->once();
    }

    private function assertSerializerWillNotDeserializeAnything()
    {
        $this->serializer
            ->shouldNotReceive('deserialize');
    }

    private function assertInnerPublisherWillPublishSerializedData()
    {
        $this->publisher
            ->shouldReceive('publish')
            ->with($this->serializedData, $this->routingKey, $this->headers)
            ->andReturn($this->serializedReturnValue)
            ->once();
    }

    private function assertInnerPublisherWillPublishSerializedDataAndReturnNull()
    {
        $this->publisher
            ->shouldReceive('publish')
            ->with($this->serializedData, $this->routingKey, $this->headers)
            ->andReturnNull()
            ->once();
    }
}
