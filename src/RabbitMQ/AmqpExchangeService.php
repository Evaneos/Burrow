<?php

namespace Burrow\RabbitMQ;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueueService;

class AmqpExchangeService extends AbstractAmqpService implements QueueService
{
    /**
     * (non-PHPdoc)
     * @see \Burrow\RabbitMQ\AbstractAmqpService::initQueue()
     */
    protected function initQueue()
    {
        $this->getChannel()->exchange_declare($this->queueName, 'fanout', false, true, false);
    }
    
    /**
     * Add a queue to the exchange to persist them before a consumer is declared
     * 
     * @param string $queueName
     */
    public function addQueue($queueName = '') {
        list($queueName,,) = $this->getChannel()->queue_declare($queueName, false, true, false, false);
        $this->getChannel()->queue_bind($queueName, $this->queueName);
        
        return $queueName;
    }

    /**
     * (non-PHPdoc)
     * @see \Burrow\RabbitMQ\AbstractAmqpService::registerConsumer()
     */
    public function registerConsumer(callable $callback)
    {
        throw new \BadMethodCallException('You can\'t register a consumer on an exchange!');
    }
}
