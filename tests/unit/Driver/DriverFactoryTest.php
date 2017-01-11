<?php

namespace Burrow\Test\Driver;

use Burrow\Driver\PeclAmqpDriver;
use Burrow\Driver\PhpAmqpLibDriver;
use Faker\Factory;
use PhpAmqpLib\Connection\AbstractConnection;

class DriverFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractConnection */
    private $phpAmqpLibConnection;

    /** @var \AMQPConnection */
    private $peclConnection;

    /** @var string[] */
    private $arrayConnection;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->phpAmqpLibConnection = \Mockery::mock(AbstractConnection::class);
        $this->peclConnection = \Mockery::mock(\AMQPConnection::class);
        $this->arrayConnection = [
            'host' => $faker->word,
            'port' => $faker->randomNumber(),
            'user' => $faker->word,
            'pwd' => $faker->word
        ];
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
    public function itShouldFailRetrievingDriverIfProvidedAnInvalidConnection()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        TestableDriverFactory::getDriver(null);
    }

    /**
     * @test
     */
    public function itShouldReturnAPeclDriverIfProvidedAPeclConnection()
    {
        $driver = TestableDriverFactory::getDriver($this->peclConnection);
        $this->assertInstanceOf(PeclAmqpDriver::class, $driver);
    }

    /**
     * @test
     */
    public function itShouldReturnAPhpLibDriverIfProvidedAPhpLibConnection()
    {
        $driver = TestableDriverFactory::getDriver($this->phpAmqpLibConnection);
        $this->assertInstanceOf(PhpAmqpLibDriver::class, $driver);
    }

    /**
     * @test
     */
    public function itShouldReturnAPeclDriverIfProvidedAnArrayAndPeclAvailable()
    {
        TestableDriverFactory::$peclAvailable = true;
        $driver = TestableDriverFactory::getDriver($this->arrayConnection);
        $this->assertInstanceOf(PeclAmqpDriver::class, $driver);
    }

    /**
     * @test
     */
    public function itShouldReturnAPhpLibDriverIfProvidedAnArrayAndPeclUnavailable()
    {
        TestableDriverFactory::$peclAvailable = false;
        $driver = TestableDriverFactory::getDriver($this->arrayConnection);
        $this->assertInstanceOf(PhpAmqpLibDriver::class, $driver);
    }
}
