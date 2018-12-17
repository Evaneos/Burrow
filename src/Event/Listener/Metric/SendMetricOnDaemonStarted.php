<?php

namespace Burrow\Event\Listener\Metric;

use Burrow\Event\DaemonStarted;
use Burrow\Event\Listener\ListenerException;
use Burrow\Metric\MetricService;
use League\Event\EventInterface;
use League\Event\ListenerInterface;

class SendMetricOnDaemonStarted implements ListenerInterface
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
        if (!($event instanceof DaemonStarted)) {
            throw ListenerException::badEventGiven($event);
        }

        $this->metricService->increment('daemon.started');
    }

    public function isListener($listener)
    {
        return $listener === $this;
    }
}
