<?php

namespace Burrow;

interface Driver
{
    const EXCHANGE_TYPE_DIRECT  = 'direct';
    const EXCHANGE_TYPE_TOPIC   = 'topic';
    const EXCHANGE_TYPE_HEADERS = 'headers';
    const EXCHANGE_TYPE_FANOUT  = 'fanout';

    const QUEUE_DURABLE = 'persistent';
    const QUEUE_EXCLUSIVE = 'exclusive';

    /**
     * Declare a persistent queue
     *
     * @param string $queueName
     * @param string  $type
     *
     * @return string
     */
    public function declareSimpleQueue($queueName = '', $type = self::QUEUE_DURABLE);

    /**
     * Declare an exchange
     *
     * @param  string $exchangeName
     * @param  string $type
     *
     * @return string
     */
    public function declareExchange($exchangeName = '', $type = self::EXCHANGE_TYPE_FANOUT);

    /**
     * Bind an existing queue to an exchange
     *
     * @param  string $exchange
     * @param  string $queueName
     * @param  string $routingKey
     *
     * @return void
     */
    public function bindQueue($exchange, $queueName, $routingKey = '');

    /**
     * Create a persisting queue and bind it to an exchange
     *
     * @param  string $exchange
     * @param  string $queueName
     * @param  string $routingKey
     *
     * @return void
     */
    public function declareAndBindQueue($exchange, $queueName, $routingKey = '');

    /**
     * Delete a queue
     *
     * @param string $queueName
     *
     * @return void
     */
    public function deleteQueue($queueName);

    /**
     * Delete an exchange
     *
     * @param string $exchangeName
     *
     * @return void
     */
    public function deleteExchange($exchangeName);

    /**
     * Publish a message in the exchange
     *
     * @param string  $exchangeName
     * @param Message $message
     *
     * @return void
     */
    public function publish($exchangeName, Message $message);

    /**
     * Consume the queue
     *
     * @param string   $queueName
     * @param callable $callback
     * @param int      $timeout
     *
     * @return void
     *
     * @throws \AMQPQueueException
     */
    public function consume($queueName, callable $callback, $timeout = 0);

    /**
     * Acknowledge the reception of the message
     *
     * @param Message $message
     *
     * @return void
     */
    public function ack(Message $message);

    /**
     * Acknowledge an error during the consumption of the message
     *
     * @param Message $message
     * @param bool   $requeue
     *
     * @return void
     */
    public function nack(Message $message, $requeue = true);

    /**
     * Close the connection
     *
     * @return void
     */
    public function close();
}
