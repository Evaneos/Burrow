<?php

namespace Burrow\Test\Metric;

use Beberlei\Metrics\Collector\DogStatsD;
use Burrow\Metric\DogStatsDMetricService;
use Burrow\Metric\StatsDMetricService;
use Mockery\Mock;

class DogStatsDMetricServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var DogStatsD | Mock */
    private $collector;

    /** @var StatsDMetricService */
    private $serviceUnderTest;

    /** @var array */
    private $tags;

    public function setUp()
    {
        $this->collector = \Mockery::mock(DogStatsD::class);

        $this->tags = ['tag' => 'value'];

        $this->serviceUnderTest = new DogStatsDMetricService($this->collector, $this->tags);
    }

    /**
     * @test
     */
    public function itSendIncrement()
    {
        $this->collector
            ->shouldReceive('increment')
            ->with('key', $this->tags)
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
            ->with('key', 0.01, $this->tags)
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
