<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueueHandler;
use Burrow\Daemonizable;
use Psr\Log\LoggerAwareInterface;

class AmqpAsyncHandler extends AbstractAmqpHandler implements QueueHandler, Daemonizable, LoggerAwareInterface
{
    /**
     * @param  AMQPMessage $message
     * @return void
     */
    public function consume(AMQPMessage $message)
    {
        $this->getConsumer()->consume($this->unescape($message->body), $this->getHeaders($message));
    }
}
