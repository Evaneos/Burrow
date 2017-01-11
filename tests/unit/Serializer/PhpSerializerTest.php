<?php

namespace Burrow\Test\Serializer;

use Burrow\Serializer\PhpSerializer;
use Faker\Factory;

class PhpSerializerTest extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    private $message;

    /** @var string */
    private $serializedMessage;

    /** @var PhpSerializer */
    private $serviceUnderTest;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->message = new \stdClass();
        $this->message->{$faker->word} = $faker->uuid;
        $this->serializedMessage = serialize($this->message);

        $this->serviceUnderTest = new PhpSerializer();
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
    public function itShouldSerialize()
    {
        $serialized = $this->serviceUnderTest->serialize($this->message);

        $this->assertEquals($this->serializedMessage, $serialized);
    }

    /**
     * @test
     */
    public function itShouldDeserialize()
    {
        $deserialized = $this->serviceUnderTest->deserialize($this->serializedMessage);

        $this->assertEquals($this->message, $deserialized);
    }

    /**
     * @test
     */
    public function itShouldDeserializeNull()
    {
        $deserialized = $this->serviceUnderTest->deserialize(null);

        $this->assertNull($deserialized);
    }

    /**
     * @test
     */
    public function itShouldNotDeserializeOtherThanString()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->serviceUnderTest->deserialize([]);
    }
}
