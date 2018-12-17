<?php

namespace Burrow\Metric;

use Beberlei\Metrics\Collector\DogStatsD as Collector;

class DogStatsDMetricService implements MetricService
{
    /** @var Collector */
    private $collector;

    /** @var array */
    private $tags;

    /**
     * MetricService constructor.
     *
     * @param Collector $collector
     * @param array     $tags
     */
    public function __construct(Collector $collector, array $tags = [])
    {
        $this->collector = $collector;
        $this->tags = $tags;
    }

    /**
     * @param $key
     */
    public function increment($key)
    {
        $this->collector->increment($key, $this->tags);
        $this->collector->flush();
    }

    /**
     * @param $key
     * @param float $timeInMs
     */
    public function timing($key, $timeInMs)
    {
        $this->collector->timing($key, $timeInMs, $this->tags);
        $this->collector->flush();
    }
}
