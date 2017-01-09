<?php

namespace Burrow\Test\Publisher;

use Burrow\Driver;
use Burrow\Message;
use Burrow\Publisher\AsyncPublisher;
use Faker\Factory;
use Mockery\Mock;

class AsyncPublisherTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $data;

    /** @var string */
    private $routingKey;

    /** @var string[] */
    private $headers;

    /** @var Driver | Mock */
    private $driver;

    /** @var string */
    private $exchangeName;

    /** @var AsyncPublisher */
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

        $this->driver = \Mockery::mock(Driver::class);
        $this->exchangeName = $faker->word;

        $this->serviceUnderTest = new AsyncPublisher($this->driver, $this->exchangeName);
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
        $this->assertDriverWillPublishMessage();

        $returnValue = $this->serviceUnderTest->publish($this->data, $this->routingKey, $this->headers);

        $this->assertNull($returnValue);
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

                    return true;
                })
            )
            ->once();
    }
}
