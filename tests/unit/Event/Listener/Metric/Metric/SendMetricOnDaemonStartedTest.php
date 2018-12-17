<?php

namespace Burrow\Test\Event\Listener\Metric;

use Burrow\Event\DaemonStarted;
use Burrow\Event\Listener\Metric\SendMetricOnDaemonStarted;
use Burrow\Metric\MetricService;
use League\Event\Event;
use Mockery\Mock;

class SendMetricOnDaemonStartedTest extends \PHPUnit_Framework_TestCase
{
    /** @var MetricService | Mock */
    private $metricService;

    /** @var SendMetricOnDaemonStarted */
    private $serviceUnderTest;

    public function setUp()
    {
        $this->metricService = \Mockery::mock(MetricService::class);

        $this->serviceUnderTest = new SendMetricOnDaemonStarted($this->metricService);
    }

    /**
     * @test
     */
    public function itIncrementDaemonStart()
    {
        $this->assertIncrementCalledOnMetricService();

        $this->serviceUnderTest->handle(new DaemonStarted());
    }

    /**
     * @test
     * @expectedException \Burrow\Event\Listener\ListenerException
     */
    public function itThrowExceptionWhenBadEventIsGiven()
    {
        $this->serviceUnderTest->handle(new Event('anEvent'));
    }

    private function assertIncrementCalledOnMetricService()
    {
        $this->metricService
            ->shouldReceive('increment')
            ->with('daemon.started')
            ->once();
    }

    /**
     * Close
     */
    public function tearDown()
    {
        \Mockery::close();
    }
}
