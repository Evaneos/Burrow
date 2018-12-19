<?php

namespace Burrow\Test\Metric;


use Burrow\Metric\DogStatsDMetricService;
use Burrow\Metric\MetricServiceFactory;
use Burrow\Metric\StatsDMetricService;

class MetricServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itCreatesDogstatsdMetricService()
    {
        $service = MetricServiceFactory::create('dogstatsd');

        self::assertInstanceOf(DogStatsDMetricService::class, $service);
    }
    /**
     * @test
     */
    public function itCreatesStatsdMetricService()
    {
        $service = MetricServiceFactory::create('statsd');

        self::assertInstanceOf(StatsDMetricService::class, $service);
    }

    /**
     * Close
     */
    public function tearDown()
    {
        \Mockery::close();
    }
}
