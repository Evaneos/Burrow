<?php

namespace Burrow\Examples;

use Burrow\QueueConsumer;

class ReturnConsumer implements QueueConsumer
{
    /**
     * @param mixed    $message
     * @param string[] $headers
     *
     * @return mixed
     */
    public function consume($message, array $headers = [])
    {
        print_r($headers);
        return $message;
    }
}
