<?php
namespace Burrow\Examples;

use Burrow\QueueConsumer;

class ReturnConsumer implements QueueConsumer
{
    /**
     * @param mixed $message
     * @return mixed|null|void
     */
    public function consume($message)
    {
        return $message;
    }
}
