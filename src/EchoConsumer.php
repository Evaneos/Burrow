<?php
namespace Burrow;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class EchoConsumer implements QueueConsumer
{
    /**
     * (non-PHPdoc)
     * @see \Burrow\QueueConsumer::consume()
     */
    public function consume($message)
    {
        echo $message . "\n";
    }
}
