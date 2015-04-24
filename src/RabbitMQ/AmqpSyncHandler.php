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

class AmqpSyncHandler extends AbstractAmqpHandler implements QueueHandler, Daemonizable, LoggerAwareInterface
{
    /**
     * @param  AMQPMessage $message
     * @return void
     */
    protected function consume(AMQPMessage $message)
    {
        $return = $this->getConsumer()->consume($this->unescape($message->body));
        $message->delivery_info['channel']->basic_publish(
            new AMQPMessage(
                $this->escape($return),
                array(
                    'correlation_id' => $message->get('correlation_id')
                )
            ),
            '',
            $message->get('reply_to')
        );
    }
}
