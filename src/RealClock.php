<?php

namespace Burrow;

class RealClock implements Clock
{
    /**
     * {@inheritdoc}
     */
    public function timestampInMs()
    {
        return microtime(true);
    }
}
