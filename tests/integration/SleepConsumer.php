<?php

namespace Burrow\Test\Integration;

use Burrow\QueueConsumer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class SleepConsumer implements QueueConsumer, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const START_CONSUME = 'START CONSUME';
    const END_CONSUME = 'END CONSUME';

    /**
     * Consumes a message
     *
     * @param mixed $message
     * @param string[] $headers
     *
     * @return mixed|void
     */

    public function consume($message, array $headers = [])
    {
        $this->logger->info(self::START_CONSUME);

        $sec = (int) $message;
        $start = new \DateTime();

        do {
            usleep(1000);
            $end = new \DateTime();
        } while ($end->diff($start)->s < $sec);

        $this->logger->info(self::END_CONSUME);
    }
}
