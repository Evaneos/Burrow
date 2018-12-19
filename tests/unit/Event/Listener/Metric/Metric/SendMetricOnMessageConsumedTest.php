<?php

namespace Burrow\Test\Event\Listener\Metric;

use Burrow\Event\Listener\Metric\SendMetricOnMessageConsumed;
use Burrow\Event\MessageConsumed;
use Burrow\Event\MessageReceived;
use Burrow\Metric\MetricService;
use Burrow\Test\FrozenClock;
use League\Event\Event;
use Mockery\Mock;

class SendMetricOnMessageConsumedTest extends \PHPUnit_Framework_TestCase
{
    /** @var MetricService | Mock */
    private $metricService;

    /** @var SendMetricOnMessageConsumed */
    private $serviceUnderTest;

    public function setUp()
    {
        $clock = new FrozenClock(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2018-12-17 00:00:00'));
        $this->metricService = \Mockery::mock(MetricService::class);

        $this->serviceUnderTest = new SendMetricOnMessageConsumed($this->metricService, $clock);
    }

    /**
     * @test
     */
    public function itStoreTimeWhenMessageIsReceivedAndSendTimeWhenMessageConsumed()
    {
        $this->assertIncrementCalledOnMetricService();

        $this->serviceUnderTest->handle(new MessageReceived());
        $this->serviceUnderTest->handle(new MessageConsumed());
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
            ->shouldReceive('timing')
            ->with('daemon.message_consumed', 0)
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
