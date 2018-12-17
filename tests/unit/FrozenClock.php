<?php

namespace Burrow\Test;

use Burrow\Clock;

class FrozenClock implements Clock
{
    /** @var \DateTimeImmutable */
    private $time;

    /**
     * FrozenClock constructor.
     *
     * @param \DateTimeImmutable $time
     */
    public function __construct(\DateTimeImmutable $time)
    {
        $this->time = $time;
    }

    /**
     * @return float
     */
    public function timestampInMs()
    {
        return (float) $this->time->getTimestamp();
    }
}
