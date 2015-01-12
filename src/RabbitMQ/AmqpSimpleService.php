<?php

namespace Burrow\RabbitMQ;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueueService;

class AmqpSimpleService extends AbstractAmqpService implements QueueService
{
    /**
     * (non-PHPdoc)
     * @see \Burrow\RabbitMQ\AbstractAmqpService::initQueue()
     */
    protected function initQueue()
    {
        $this->getChannel()->queue_declare($this->queueName, false, true, false, false);
    }

    /**
     * (non-PHPdoc)
     * @see \Burrow\RabbitMQ\AbstractAmqpService::registerConsumer()
     */
    public function registerConsumer(callable $callback)
    {
        $this->getChannel()->basic_qos(null, 1, null);
        $this->getChannel()->basic_consume($this->queueName, '', false, false, false, false, function (AMQPMessage $message) use ($callback) {
            try {
                call_user_func($callback, $message->body);
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            } catch (\Exception $e) {
                // beware of unlimited loop !
                $message->delivery_info['channel']->basic_reject($message->delivery_info['delivery_tag'], true);
            }
        });
    }
}
