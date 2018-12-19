<?php

namespace Burrow\Test\Event\Listener\Metric;

use Burrow\Event\Listener\Metric\SendMetricOnDaemonStarted;
use Burrow\Event\Listener\Metric\SendMetricOnDaemonStopped;
use Burrow\Event\Listener\Metric\SendMetricOnMessageConsumed;
use Burrow\Event\Listener\Metric\SendMetricOnMessageReceived;
use Burrow\Event\Listener\Metric\SendMetricListenerProvider;
use Burrow\Metric\MetricService;
use League\Event\ListenerAcceptorInterface;
use Mockery\Matcher\MustBe;
use Mockery\Mock;

class SendMetricListenerSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var SendMetricListenerProvider */
    private $serviceUnderTest;

    /** @var MetricService */
    private $metricService;

    /** @var ListenerAcceptorInterface | Mock */
    private $listenerAcceptator;

    public function setUp()
    {
        $this->metricService = \Mockery::mock(MetricService::class);
        $this->listenerAcceptator = \Mockery::mock(ListenerAcceptorInterface::class);

        $this->serviceUnderTest = new SendMetricListenerProvider(
            $this->metricService
        );
    }

    /**
     * @test
     */
    public function itProvideListeners()
    {
        $this->assertListenersAdded();

        $this->serviceUnderTest->provideListeners($this->listenerAcceptator);
    }

    private function assertListenersAdded()
    {
        $this->listenerAcceptator
            ->shouldReceive('addListener')
            ->with('daemon.started', new MustBe(new SendMetricOnDaemonStarted($this->metricService)))
            ->once();

        $this->listenerAcceptator
            ->shouldReceive('addListener')
            ->with('daemon.stopped', new MustBe(new SendMetricOnDaemonStopped($this->metricService)))
            ->once();

        $this->listenerAcceptator
            ->shouldReceive('addListener')
            ->with('message.received', new MustBe(new SendMetricOnMessageReceived($this->metricService)))
            ->once();

        $sendMetricOnMessageConsumed = new SendMetricOnMessageConsumed($this->metricService);

        $this->listenerAcceptator
            ->shouldReceive('addListener')
            ->with('message.received', new MustBe($sendMetricOnMessageConsumed))
            ->once();

        $this->listenerAcceptator
            ->shouldReceive('addListener')
            ->with('message.consumed', new MustBe($sendMetricOnMessageConsumed))
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
