<?php

namespace Burrow\Test\Exception;

use Burrow\Exception\BurrowException;
use Burrow\Exception\ConsumerException;
use Faker\Factory;

class ConsumerExceptionTest extends \PHPUnit_Framework_TestCase
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

        $exception = new ConsumerException($message, $code);

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

        $exception = new \Exception($message, $code);

        $consumerException = ConsumerException::build($exception);

        $this->assertInstanceOf(\RuntimeException::class, $consumerException);
        $this->assertInstanceOf(BurrowException::class, $consumerException);
        $this->assertEquals($message, $consumerException->getMessage());
        $this->assertEquals($code, $consumerException->getCode());
        $this->assertEquals($exception, $consumerException->getPrevious());
    }
}
