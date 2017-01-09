<?php

namespace Burrow;

class ConsumeOptions
{
    /** @var bool */
    private $autoAck;

    /** @var int */
    private $timeout;

    /**
     * ConsumeOptions constructor.
     */
    public function __construct()
    {
        $this->autoAck = true;
        $this->timeout = 0;
    }

    /**
     * @return bool
     */
    public function isAutoAck()
    {
        return $this->autoAck;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Disable the auto ack.
     */
    public function disableAutoAck()
    {
        $this->autoAck = false;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }
}
