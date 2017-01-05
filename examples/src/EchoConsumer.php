<?php

namespace Burrow\Examples;

use Burrow\QueueConsumer;

class EchoConsumer implements QueueConsumer
{
    /**
     * @param mixed    $message
     * @param string[] $headers
     *
     * @return void
     */
    public function consume($message, array $headers = [])
    {
        var_dump($headers);
        echo $message . "\n";
    }
}
