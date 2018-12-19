<?php

namespace Burrow\Metric;

use Beberlei\Metrics\Collector\DogStatsD;
use Beberlei\Metrics\Collector\StatsD;
use Beberlei\Metrics\Factory;
use Beberlei\Metrics\MetricsException;

class MetricServiceFactory
{
    /**
     * @param       $type
     * @param array $options
     * @param array $contextualTags
     *
     * @return MetricService
     * @throws MetricsException
     * @throws \Exception
     */
    public static function create($type, $options = [], $contextualTags = [])
    {
        $collector = Factory::create($type, $options);

        switch ($type) {
            case 'dogstatsd':
                /** @var DogStatsD $collector */
                return new DogStatsDMetricService($collector, $contextualTags);
            case 'statsd':
                /** @var StatsD $collector */
                return new StatsDMetricService($collector);
        }

        throw new \Exception(sprintf('MetricService for %s is not implemented', $type));
    }
}
