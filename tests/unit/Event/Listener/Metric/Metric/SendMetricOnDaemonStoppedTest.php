<?php

namespace Burrow\Test\Event\Listener\Metric;

use Burrow\Event\DaemonStopped;
use Burrow\Event\Listener\Metric\SendMetricOnDaemonStopped;
use Burrow\Metric\MetricService;
use League\Event\Event;
use Mockery\Mock;

class SendMetricOnDaemonStoppedTest extends \PHPUnit_Framework_TestCase
{
    /** @var MetricService | Mock */
    private $metricService;

    /** @var SendMetricOnDaemonStopped */
    private $serviceUnderTest;

    public function setUp()
    {
        $this->metricService = \Mockery::mock(MetricService::class);

        $this->serviceUnderTest = new SendMetricOnDaemonStopped($this->metricService);
    }

    /**
     * @test
     */
    public function itIncrementDaemonStop()
    {
        $this->assertIncrementCalledOnMetricService();

        $this->serviceUnderTest->handle(new DaemonStopped());
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
            ->with('daemon.stopped')
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
