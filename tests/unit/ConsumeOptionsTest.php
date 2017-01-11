<?php

namespace Burrow\Test;

use Burrow\ConsumeOptions;
use Faker\Factory;

class ConsumeOptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConsumeOptions */
    private $consumeOptions;

    /**
     * Init
     */
    public function setUp()
    {
        $this->consumeOptions = new ConsumeOptions();
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
    public function itShouldHaveDefaultOptionValues()
    {
        $this->assertTrue($this->consumeOptions->isAutoAck());
        $this->assertEquals(0, $this->consumeOptions->getTimeout());
    }

    /**
     * @test
     */
    public function itShouldAllowToModifyDefaultOptions()
    {
        $faker = Factory::create();
        $timeout = $faker->randomNumber();

        $this->consumeOptions->disableAutoAck();
        $this->consumeOptions->setTimeout($timeout);

        $this->assertFalse($this->consumeOptions->isAutoAck());
        $this->assertEquals($timeout, $this->consumeOptions->getTimeout());
    }
}
