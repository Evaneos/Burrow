<?php

namespace Burrow\Driver;

use Burrow\Driver;
use Burrow\Exception\TimeoutException;
use Burrow\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class PhpAmqpLibDriver implements Driver
{
    const DELIVERY_MODE = 'delivery_mode';
    const CONTENT_TYPE = 'content_type';
    const APPLICATION_HEADERS = 'application_headers';
    const CORRELATION_ID = 'correlation_id';
    const REPLY_TO = 'reply_to';

    /** @var AbstractConnection */
    private $connection;

    /** @var AMQPChannel */
    private $channel;

    /** @var bool */
    private $stop = false;

    /**
     * PhpAmqpLibDriver constructor.
     *
     * @param AbstractConnection $connection
     */
    public function __construct(AbstractConnection $connection)
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
     */
    public function declareSimpleQueue($queueName = '', $type = self::QUEUE_DURABLE)
    {
        $durable = ($type === self::QUEUE_DURABLE);
        $exclusive = ($type === self::QUEUE_EXCLUSIVE);

        list($name, , ) = $this->getChannel()->queue_declare($queueName, false, $durable, $exclusive, false);

        return $name;
    }

    /**
     * Declare an exchange
     *
     * @param  string $exchangeName
     * @param  string $type
     *
     * @return string
     */
    public function declareExchange($exchangeName = '', $type = self::EXCHANGE_TYPE_FANOUT)
    {
        list($name, , ) = $this->getChannel()->exchange_declare($exchangeName, $type, false, true, false);

        return $name;
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

    /**
     * Publish a message in the exchange
     *
     * @param string  $exchangeName
     * @param Message $message
     *
     * @return void
     */
    public function publish($exchangeName, Message $message)
    {
        $this->getChannel()->basic_publish(
            new AMQPMessage($message->getBody(), self::getMessageProperties($message)),
            $exchangeName,
            $message->getRoutingKey()
        );
    }

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
    public function consume($queueName, callable $callback, $timeout = 0)
    {
        $this->stop = false;

        $this->getChannel()->basic_qos(null, 1, null);
        $this->getChannel()->basic_consume(
            $queueName,
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($callback) {
                $burrowMessage = new Message(
                    $message->getBody(),
                    '', // Impossible to retrieve here
                    self::getHeaders($message),
                    self::getCorrelationId($message),
                    self::getReplyTo($message)
                );
                $burrowMessage->setDeliveryTag($message->delivery_info['delivery_tag']);

                $success = $callback($burrowMessage);
                if ($success === false) {
                    $this->stop = true;
                }
            }
        );

        while (count($this->getChannel()->callbacks) && !$this->stop) {
            try {
                $this->getChannel()->wait(null, false, $timeout);
            } catch (AMQPTimeoutException $e) {
                throw TimeoutException::build($e, $timeout);
            }
        }
    }

    /**
     * Acknowledge the reception of the message
     *
     * @param string $queueName
     * @param string $deliveryTag
     *
     * @return void
     */
    public function ack($queueName, $deliveryTag)
    {
        $this->getChannel()->basic_ack($deliveryTag);
    }

    /**
     * Aknowledge an error during the consumption of the message
     *
     * @param string $queueName
     * @param string $deliveryTag
     * @param bool   $requeue
     *
     * @return void
     */
    public function nack($queueName, $deliveryTag, $requeue = true)
    {
        $this->getChannel()->basic_reject($deliveryTag, $requeue);
    }

    /**
     * Close the connection
     *
     * @return void
     */
    public function close()
    {
        $this->stop = true;

        $this->getChannel()->close();
        $this->connection->close();
    }

    /**
     * @return AMQPChannel
     */
    private function getChannel()
    {
        if (null === $this->channel) {
            $this->channel = $this->connection->channel();
        }

        return $this->channel;
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
            self::DELIVERY_MODE       => 2,
            self::CONTENT_TYPE        => 'text/plain',
            self::APPLICATION_HEADERS => new AMQPTable($message->getHeaders())
        ];

        if ($message->getCorrelationId() !== null) {
            $properties[self::CORRELATION_ID] = $message->getCorrelationId();

        }

        if ($message->getReplyTo() !== null) {
            $properties[self::REPLY_TO] = $message->getReplyTo();
        }

        return $properties;
    }

    /**
     * @param AMQPMessage $message
     *
     * @return array
     */
    private static function getHeaders(AMQPMessage $message)
    {
        return $message->has(self::APPLICATION_HEADERS) ?
            $message->get(self::APPLICATION_HEADERS)->getNativeData() : [];
    }

    /**
     * @param AMQPMessage $message
     *
     * @return string
     */
    private static function getCorrelationId(AMQPMessage $message)
    {
        return $message->has(self::CORRELATION_ID) ?
            $message->get(self::CORRELATION_ID) : '';
    }

    /**
     * @param AMQPMessage $message
     *
     * @return string
     */
    private static function getReplyTo(AMQPMessage $message)
    {
        return $message->has(self::REPLY_TO) ?
            $message->get(self::REPLY_TO) : '';
    }
}
