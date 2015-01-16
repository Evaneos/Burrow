<?php
namespace Burrow\Examples;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Burrow\QueueConsumer;

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
