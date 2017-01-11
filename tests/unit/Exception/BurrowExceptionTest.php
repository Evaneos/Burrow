<?php

namespace Burrow\Test\Exception;

use Burrow\Exception\BurrowException;
use Faker\Factory;

class BurrowExceptionTest extends \PHPUnit_Framework_TestCase
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

        $exception = new BurrowException($message, $code);

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
