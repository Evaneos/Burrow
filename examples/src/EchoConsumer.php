<?php
namespace Burrow\Examples;

use Burrow\QueueConsumer;

class EchoConsumer implements QueueConsumer
{
    /**
     * @param mixed $message
     * @return mixed|null|void
     */
    public function consume($message)
    {
        echo $message . "\n";
    }
}
