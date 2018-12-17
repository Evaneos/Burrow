<?php

namespace Burrow;

interface Clock
{
    /**
     * @return float
     */
    public function timestampInMs();
}
