<?php

namespace Burrow\Event\Listener\Metric;

use Burrow\Event\Listener\ListenerException;
use Burrow\Event\MessageReceived;
use Burrow\Metric\MetricService;
use League\Event\EventInterface;
use League\Event\ListenerInterface;

class SendMetricOnMessageReceived implements ListenerInterface
{
    /** @var MetricService */
    private $metricService;

    /**
     * SendMetricOnDaemonStarted constructor.
     *
     * @param MetricService $metricService
     */
    public function __construct(MetricService $metricService)
    {
        $this->metricService = $metricService;
    }

    /**
     * @param EventInterface $event
     *
     * @throws ListenerException
     */
    public function handle(EventInterface $event)
    {
        if (!($event instanceof MessageReceived)) {
            throw ListenerException::badEventGiven($event);
        }

        $this->metricService->increment('daemon.message_received');
    }

    public function isListener($listener)
    {
        return $listener === $this;
    }
}
