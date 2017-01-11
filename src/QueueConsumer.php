<?php

namespace Burrow;

interface QueueConsumer
{
    /**
     * Consumes a message
     *
     * @param mixed    $message
     * @param string[] $headers
     *
     * @return mixed|void
     */
    public function consume($message, array $headers = []);
}
