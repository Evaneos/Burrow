<?php

namespace Burrow\Event\Listener\Metric;

use Burrow\Event\DaemonStarted;
use Burrow\Event\DaemonStopped;
use Burrow\Event\MessageConsumed;
use Burrow\Event\MessageReceived;
use Burrow\Metric\MetricService;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;

class SendMetricListenerProvider implements ListenerProviderInterface
{
    /** @var MetricService  */
    private $metricService;

    /**
     * SendMetricSubscriber constructor.
     *
     * @param MetricService $metricService
     */
    public function __construct(MetricService $metricService)
    {
        $this->metricService = $metricService;
    }

    /**
     * @param ListenerAcceptorInterface $listenerAcceptor
     *
     * @return ListenerProviderInterface|void
     */
    public function provideListeners(ListenerAcceptorInterface $listenerAcceptor)
    {
        $listenerAcceptor->addListener(DaemonStarted::NAME, new SendMetricOnDaemonStarted($this->metricService));
        $listenerAcceptor->addListener(DaemonStopped::NAME, new SendMetricOnDaemonStopped($this->metricService));
        $listenerAcceptor->addListener(MessageReceived::NAME, new SendMetricOnMessageReceived($this->metricService));

        $sendMetricOnMessageConsumed = new SendMetricOnMessageConsumed($this->metricService);
        $listenerAcceptor->addListener(MessageReceived::NAME, $sendMetricOnMessageConsumed);
        $listenerAcceptor->addListener(MessageConsumed::NAME, $sendMetricOnMessageConsumed);
    }
}
