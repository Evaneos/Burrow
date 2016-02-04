<?php
namespace Burrow\Swarrot\Processor;

use PhpAmqpLib\Exception\AMQPTimeoutException;
use Swarrot\Processor\MaxExecutionTime\MaxExecutionTimeProcessor;

class TimeoutProcessor extends MaxExecutionTimeProcessor {

    protected function isTimeExceeded(array $options)
    {
        if (parent::isTimeExceeded($options)) {
            throw new AMQPTimeoutException('Timeout expired');
        }

        return false;
    }
} 