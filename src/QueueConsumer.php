<?php

namespace Burrow;

interface QueueConsumer
{
    /**
     * Consumes a message
     *
     * @param string   $message
     * @param string[] $headers
     *
     * @return null|string|void
     */
    public function consume($message, array $headers = []);
}
