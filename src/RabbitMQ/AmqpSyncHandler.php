<?php

namespace Burrow\RabbitMQ;

use Burrow\Daemonizable;
use Burrow\QueueHandler;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Psr\Log\LoggerAwareInterface;

class AmqpSyncHandler extends AbstractAmqpHandler implements QueueHandler, Daemonizable, LoggerAwareInterface
{
    /**
     * @param  AMQPMessage $message
     * @return void
     */
    public function consume(AMQPMessage $message)
    {
        $headers = $this->getHeaders($message);
        $return = $this->getConsumer()->consume($this->unescape($message->body), $headers);
        $message->delivery_info['channel']->basic_publish(
            new AMQPMessage(
                $this->escape($return),
                [
                    'correlation_id' => $message->get('correlation_id'),
                    'application_headers' => new AMQPTable($headers)
                ]
            ),
            '',
            $message->get('reply_to')
        );
    }
}
