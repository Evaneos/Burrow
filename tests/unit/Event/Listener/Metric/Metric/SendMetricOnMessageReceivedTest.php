<?php

namespace Burrow\Test\Event\Listener\Metric;

use Burrow\Event\Listener\Metric\SendMetricOnMessageReceived;
use Burrow\Event\MessageReceived;
use Burrow\Metric\MetricService;
use League\Event\Event;
use Mockery\Mock;

class SendMetricOnMessageReceivedTest extends \PHPUnit_Framework_TestCase
{
    /** @var MetricService | Mock */
    private $metricService;

    /** @var SendMetricOnMessageReceived */
    private $serviceUnderTest;

    public function setUp()
    {
        $this->metricService = \Mockery::mock(MetricService::class);

        $this->serviceUnderTest = new SendMetricOnMessageReceived($this->metricService);
    }

    /**
     * @test
     */
    public function itIncrementMessageReceived()
    {
        $this->assertIncrementCalledOnMetricService();

        $this->serviceUnderTest->handle(new MessageReceived());
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
            ->with('daemon.message_received')
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
