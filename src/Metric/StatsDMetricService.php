<?php

namespace Burrow\Metric;

use Beberlei\Metrics\Collector\StatsD as Collector;

class StatsDMetricService implements MetricService
{
    /** @var Collector */
    private $collector;

    /**
     * MetricService constructor.
     *
     * @param Collector $collector
     */
    public function __construct(Collector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * @param $key
     */
    public function increment($key)
    {
        $this->collector->increment($key);
        $this->collector->flush();
    }

    /**
     * @param $key
     * @param float $timeInMs
     */
    public function timing($key, $timeInMs)
    {
        $this->collector->timing($key, $timeInMs);
        $this->collector->flush();
    }
}
