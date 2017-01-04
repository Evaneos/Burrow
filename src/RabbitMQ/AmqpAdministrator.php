<?php

namespace Burrow\RabbitMQ;

class AmqpAdministrator extends AmqpTemplate
{
    const DIRECT  = 'direct';
    const TOPIC   = 'topic';
    const HEADERS = 'headers';
    const FANOUT  = 'fanout';

    /**
     * Declare a persistent queue
     *
     * @param  string $queueName
     * @return void
     */
    public function declareSimpleQueue($queueName)
    {
        $this->getChannel()->queue_declare($queueName, false, true, false, false);
    }

    /**
     * Declare an exchange
     *
     * @param  string $queueName
     * @param  string $type
     * @return void
     */
    public function declareExchange($queueName, $type = self::FANOUT)
    {
        $this->getChannel()->exchange_declare($queueName, $type, false, true, false);
    }

    /**
     * Bind an existing queue to an exchange
     *
     * @param  string $exchange
     * @param  string $queueName
     * @param  string $routingKey
     * @return void
     */
    public function bindQueue($exchange, $queueName, $routingKey = '')
    {
        $this->getChannel()->queue_bind($queueName, $exchange, $routingKey);
    }

    /**
     * Create a persisting queue and bind it to an exchange
     *
     * @param  string $exchange
     * @param  string $queueName
     * @param  string $routingKey
     * @return void
     */
    public function declareAndBindQueue($exchange, $queueName, $routingKey = '')
    {
        $this->declareSimpleQueue($queueName);
        $this->bindQueue($exchange, $queueName, $routingKey);
    }

    /**
     * Delete a queue
     *
     * @param string $queueName
     * @return void
     */
    public function deleteQueue($queueName)
    {
        $this->getChannel()->queue_delete($queueName);
    }

    /**
     * Delete an exchange
     *
     * @param string $exchangeName
     * @return void
     */
    public function deleteExchange($exchangeName)
    {
        $this->getChannel()->exchange_delete($exchangeName);
    }
}
