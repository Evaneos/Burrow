<?php

namespace Burrow\Test\Exception;

use Burrow\Exception\BurrowException;
use Burrow\Exception\TimeoutException;
use Faker\Factory;

class TimeoutExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Init
     */
    public function setUp()
    {
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
    public function itShouldBuildTheException()
    {
        $faker = Factory::create();

        $message = $faker->text();
        $code = $faker->randomNumber();

        $exception = new TimeoutException($message, $code);

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(BurrowException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * @test
     */
    public function itShouldBuildTheFromAPreviousOne()
    {
        $faker = Factory::create();

        $message = $faker->text();
        $code = $faker->randomNumber();
        $timeout = $faker->randomNumber();

        $exception = new \Exception($message, $code);

        $consumerException = TimeoutException::build($exception, $timeout);

        $this->assertInstanceOf(\RuntimeException::class, $consumerException);
        $this->assertInstanceOf(BurrowException::class, $consumerException);
        $this->assertEquals(
            sprintf('The connection timed out after %d sec while awaiting incoming data', $timeout),
            $consumerException->getMessage()
        );
        $this->assertEquals($code, $consumerException->getCode());
        $this->assertEquals($exception, $consumerException->getPrevious());
    }
}
