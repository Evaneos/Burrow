<?php

namespace Burrow\Event\Listener\Metric;

use Burrow\Clock;
use Burrow\Event\Listener\ListenerException;
use Burrow\Event\MessageConsumed;
use Burrow\Event\MessageReceived;
use Burrow\Metric\MetricService;
use Burrow\RealClock;
use League\Event\EventInterface;
use League\Event\ListenerInterface;

class SendMetricOnMessageConsumed implements ListenerInterface
{
    /** @var MetricService */
    private $metricService;

    /** @var Clock */
    private $clock;

    /** @var null|float */
    private $messageReceivedAt;

    /**
     * SendMetricOnMessageConsumed constructor.
     *
     * @param MetricService $metricService
     * @param Clock|null    $clock
     */
    public function __construct(MetricService $metricService, Clock $clock = null)
    {
        $this->metricService = $metricService;
        $this->clock = $clock ?: new RealClock();
    }

    /**
     * @param EventInterface | MessageConsumed $event
     *
     * @throws ListenerException
     */
    public function handle(EventInterface $event)
    {
        if ($event instanceof MessageReceived) {
            $this->messageReceivedAt = $this->clock->timestampInMs();
            return;
        }

        if (!($event instanceof MessageConsumed)) {
            throw ListenerException::badEventGiven($event);
        }

        $this->metricService->increment(
            'daemon.message_consumed'
        );

        $this->metricService->timing(
            'daemon.message_processing_time',
            $this->clock->timestampInMs() - $this->messageReceivedAt
        );
    }

    public function isListener($listener)
    {
        return $listener === $this;
    }
}
