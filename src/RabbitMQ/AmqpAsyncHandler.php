<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueueHandler;
use Burrow\QueueConsumer;
use Burrow\Daemonizable;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class AmqpAsyncHandler extends AbstractAmqpHandler implements QueueHandler, Daemonizable, LoggerAwareInterface
{
    /**
     * @param  AMQPMessage $message
     * @return void
     */
    public function consume(AMQPMessage $message)
    {
        $this->getConsumer()->consume(unserialize($message->body));
    }
}
