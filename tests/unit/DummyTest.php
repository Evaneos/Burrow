<?php

namespace Burrow\Test;

class DummyTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        \Mockery::close();
    }

    protected function setUp()
    {
    }

    /**
     * @test
     */
    public function test()
    {
        $this->assertTrue(true);
    }
}
