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
     *
     * @throws \Exception
     */
    public function consume($message, array $headers = [])
    {
        print_r($headers);
        echo $message . "\n";
    }
}
