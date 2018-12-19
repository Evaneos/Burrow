<?php

namespace Burrow\Metric;

interface MetricService
{
    /**
     * @param $key
     */
    public function increment($key);

    /**
     * @param $key
     * @param float $timeInMs
     */
    public function timing($key, $timeInMs);
}
