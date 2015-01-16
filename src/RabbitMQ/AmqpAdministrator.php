<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpAdministrator extends AmqpTemplate
{
    const DIRECT  = 'direct';
    const TOPIC   = 'topic';
    const HEADERS = 'headers';
    const FANOUT  = 'fanout';
    
    /**
     * Declare a persistent queue
     * 
     * @param string $queueName
     */
    public function declareSimpleQueue($queueName)
    {
        $this->channel->queue_declare($queueName, false, true, false, false);
    }
    
    /**
     * Declare an exchange
     * 
     * @param string $queueName
     * @param string $type
     */
    public function declareExchange($queueName, $type = 'fanout')
    {
        $this->channel->exchange_declare($queueName, $type, false, true, false);
    }
    
    /**
     * Bind an existing queue to an exchange
     * 
     * @param string $exchange
     * @param string $queueName
     * @param string $routingKey
     */
    public function bindQueue($exchange, $queueName, $routingKey = '')
    {
        $this->channel->queue_bind($queueName, $exchange, $routingKey);
    }
    
    /**
     * Create a persisting queue and bind it to an exchange
     * 
     * @param string $exchange
     * @param string $queueName
     * @param string $routingKey
     */
    public function declareAndBindQueue($exchange, $queueName, $routingKey = '')
    {
        $this->declareSimpleQueue($queueName);
        $this->bindQueue($exchange, $queueName, $routingKey);
    }
}
