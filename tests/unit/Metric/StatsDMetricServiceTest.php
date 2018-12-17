<?php

namespace Burrow\Test\Metric;

use Beberlei\Metrics\Collector\StatsD;
use Burrow\Metric\StatsDMetricService;
use Mockery\Mock;

class StatsDMetricServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var StatsD | Mock */
    private $collector;

    /** @var StatsDMetricService */
    private $serviceUnderTest;

    public function setUp()
    {
        $this->collector = \Mockery::mock(StatsD::class);

        $this->serviceUnderTest = new StatsDMetricService($this->collector);
    }

    /**
     * @test
     */
    public function itSendIncrement()
    {
        $this->collector
            ->shouldReceive('increment')
            ->with('key')
            ->once();

        $this->collector
            ->shouldReceive('flush')
            ->once();

        $this->serviceUnderTest->increment('key');
    }

    /**
     * @test
     */
    public function itSendTiming()
    {
        $this->collector
            ->shouldReceive('timing')
            ->with('key', 0.01)
            ->once();

        $this->collector
            ->shouldReceive('flush')
            ->once();

        $this->serviceUnderTest->timing('key', 0.01);
    }

    /**
     * Close
     */
    public function tearDown()
    {
        \Mockery::close();
    }
}
