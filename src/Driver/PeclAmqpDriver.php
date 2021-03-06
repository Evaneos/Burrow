<?php

namespace Burrow\Driver;

use Assert\AssertionFailedException;
use Burrow\Driver;
use Burrow\Exception\ConsumerException;
use Burrow\Exception\TimeoutException;
use Burrow\Message;

/**
 * @codeCoverageIgnore
 */
class PeclAmqpDriver implements Driver
{
    const DELIVERY_MODE = 'delivery_mode';
    const CONTENT_TYPE = 'content_type';
    const APPLICATION_HEADERS = 'headers';
    const CORRELATION_ID = 'correlation_id';
    const REPLY_TO = 'reply_to';

    /** @var \AMQPConnection */
    private $connection;

    /** @var \AMQPChannel */
    private $channel;

    /**
     * PeclAmqpDriver constructor.
     *
     * @param \AMQPConnection $connection
     */
    public function __construct(\AMQPConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Declare a persistent queue
     *
     * @param string $queueName
     * @param string $type
     *
     * @return string
     *
     * @throws \AMQPConnectionException
     * @throws \AMQPChannelException
     * @throws \AMQPQueueException
     */
    public function declareSimpleQueue($queueName = '', $type = self::QUEUE_DURABLE)
    {
        $flag = AMQP_DURABLE;
        if ($type === self::QUEUE_EXCLUSIVE) {
            $flag = AMQP_EXCLUSIVE;
        }

        $queue = $this->getQueue($queueName);
        $queue->setFlags($flag);
        $queue->declareQueue();

        return $queue->getName();
    }

    /**
     * Declare an exchange
     *
     * @param string $exchangeName
     * @param string $type
     *
     * @return string
     *
     * @throws \AMQPConnectionException
     * @throws \AMQPChannelException
     * @throws \AMQPExchangeException
     */
    public function declareExchange($exchangeName = '', $type = self::EXCHANGE_TYPE_FANOUT)
    {
        $exchange = $this->getExchange($exchangeName);
        $exchange->setType($type);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();

        return $exchange->getName();
    }

    /**
     * Bind an existing queue to an exchange
     *
     * @param string $exchange
     * @param string $queueName
     * @param string $routingKey
     *
     * @return void
     *
     * @throws \AMQPConnectionException
     * @throws \AMQPChannelException
     * @throws \AMQPQueueException
     */
    public function bindQueue($exchange, $queueName, $routingKey = '')
    {
        $queue = $this->getQueue($queueName);
        $queue->bind($exchange, $routingKey);
    }

    /**
     * Create a persisting queue and bind it to an exchange
     *
     * @param string $exchange
     * @param string $queueName
     * @param string $routingKey
     *
     * @return void
     *
     * @throws \AMQPConnectionException
     * @throws \AMQPChannelException
     * @throws \AMQPQueueException
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
     *
     * @return void
     *
     * @throws \AMQPConnectionException
     * @throws \AMQPChannelException
     * @throws \AMQPQueueException
     */
    public function deleteQueue($queueName)
    {
        $this->getQueue($queueName)->delete();
    }

    /**
     * Delete an exchange
     *
     * @param string $exchangeName
     *
     * @return void
     *
     * @throws \AMQPConnectionException
     * @throws \AMQPChannelException
     * @throws \AMQPExchangeException
     */
    public function deleteExchange($exchangeName)
    {
        $this->getExchange($exchangeName)->delete();
    }

    /**
     * Publish a message in the exchange
     *
     * @param string $exchangeName
     * @param Message $message
     *
     * @return void
     *
     * @throws \AMQPConnectionException
     * @throws \AMQPChannelException
     * @throws \AMQPExchangeException
     */
    public function publish($exchangeName, Message $message)
    {
        $exchange = $this->getExchange($exchangeName);
        $exchange->publish(
            $message->getBody(),
            $message->getRoutingKey(),
            AMQP_NOPARAM,
            self::getMessageProperties($message)
        );
    }

    /**
     * Consume the queue
     *
     * @param string $queueName
     * @param callable $callback Must return false if you want to consume only one message
     * @param int $timeout
     * @param bool $autoAck
     *
     * @return void
     *
     * @throws ConsumerException
     * @throws TimeoutException
     * @throws \AMQPConnectionException
     * @throws \AMQPChannelException
     * @throws \AMQPQueueException
     * @throws \InvalidArgumentException
     * @throws AssertionFailedException
     */
    public function consume($queueName, callable $callback, $timeout = 0, $autoAck = true)
    {
        $this->connection->setReadTimeout($timeout);
        $this->getChannel()->setPrefetchCount(1);
        $queue = $this->getQueue($queueName);
        $flags = $autoAck ? AMQP_AUTOACK : AMQP_NOPARAM;
        $consumerTag = self::generateConsumerTag();
        try {
            $queue->consume(function (\AMQPEnvelope $message) use ($callback, $queueName) {

                $burrowMessage = new Message(
                    $message->getBody(),
                    $message->getRoutingKey(),
                    $message->getHeaders(),
                    $message->getCorrelationId(),
                    $message->getReplyTo()
                );
                $burrowMessage->setDeliveryTag($message->getDeliveryTag());
                $burrowMessage->setQueue($queueName);

                return $callback($burrowMessage);
            }, $flags, $consumerTag);
        } catch (\AMQPQueueException $e) {
            if ($e->getMessage() === 'Consumer timeout exceed') {
                throw TimeoutException::build($e, $timeout);
            }
            throw ConsumerException::build($e);
        } finally {
            $queue->cancel($consumerTag);
        }
    }

    /**
     * Acknowledge the reception of the message.
     *
     * @param Message $message
     *
     * @return void
     *
     * @throws \AMQPConnectionException
     * @throws \AMQPChannelException
     * @throws \AMQPQueueException
     */
    public function ack(Message $message)
    {
        $queue = $this->getQueue($message->getQueue());
        $queue->ack($message->getDeliveryTag());
    }

    /**
     * Acknowledge an error during the consumption of the message
     *
     * @param Message $message
     * @param bool $requeue
     *
     * @return void
     *
     * @throws \AMQPConnectionException
     * @throws \AMQPChannelException
     * @throws \AMQPQueueException
     */
    public function nack(Message $message, $requeue = true)
    {
        $queue = $this->getQueue($message->getQueue());
        $queue->nack($message->getDeliveryTag(), $requeue ? AMQP_REQUEUE : AMQP_NOPARAM);
    }

    /**
     * Close the connection
     *
     * @return void
     */
    public function close()
    {
        $this->connection->disconnect();
    }

    /**
     * @return \AMQPChannel
     *
     * @throws \AMQPConnectionException
     */
    private function getChannel()
    {
        if (null === $this->channel) {
            $this->connection->connect();
            $this->channel = new \AMQPChannel($this->connection);
        }

        return $this->channel;
    }

    /**
     * @param string $queueName
     *
     * @return \AMQPQueue
     *
     * @throws \AMQPQueueException
     * @throws \AMQPConnectionException
     */
    private function getQueue($queueName)
    {
        $queue = new \AMQPQueue($this->getChannel());
        if ($queueName) {
            $queue->setName($queueName);
        }

        return $queue;
    }

    /**
     * @param string $exchangeName
     *
     * @return \AMQPExchange
     *
     * @throws \AMQPExchangeException
     * @throws \AMQPConnectionException
     */
    private function getExchange($exchangeName)
    {
        $exchange = new \AMQPExchange($this->getChannel());
        if ($exchangeName) {
            $exchange->setName($exchangeName);
        }

        return $exchange;
    }

    /**
     * Returns the message parameters
     *
     * @param Message $message
     *
     * @return array
     */
    private static function getMessageProperties(Message $message)
    {
        $properties = [
            self::DELIVERY_MODE => 2,
            self::CONTENT_TYPE => 'text/plain',
            self::APPLICATION_HEADERS => $message->getHeaders(),
        ];

        if ($message->getCorrelationId() !== null) {
            $properties[self::CORRELATION_ID] = $message->getCorrelationId();
        }

        if ($message->getReplyTo() !== null) {
            $properties[self::REPLY_TO] = $message->getReplyTo();
        }

        return $properties;
    }

    private static function generateConsumerTag(
        $prefix = 'consumer-tags',
        $length = 10,
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ) {
        return $prefix . substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 1, $length);
    }
}
