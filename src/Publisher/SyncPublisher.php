<?php

namespace Burrow\Publisher;

use Assert\AssertionFailedException;
use Burrow\Driver;
use Burrow\Message;
use Burrow\QueueHandler;
use Burrow\QueuePublisher;

class SyncPublisher implements QueuePublisher
{
    /** @var Driver */
    private $driver;

    /** @var string */
    private $exchangeName;

    /** @var int */
    private $timeout;

    /**
     * Constructor
     *
     * @param Driver $driver
     * @param string $exchangeName
     * @param int    $timeout
     */
    public function __construct(Driver $driver, $exchangeName, $timeout = 10)
    {
        $this->driver = $driver;
        $this->exchangeName = $exchangeName;
        $this->timeout = $timeout;
    }

    /**
     * Publish a message on the queue
     *
     * @param mixed    $data
     * @param string   $routingKey
     * @param string[] $headers
     *
     * @return mixed
     *
     * @throws AssertionFailedException
     * @throws \InvalidArgumentException
     */
    public function publish($data, $routingKey = '', array $headers = [])
    {
        $response = null;
        $correlationId = uniqid('', false);
        $replyTo = $this->driver->declareSimpleQueue('', Driver::QUEUE_EXCLUSIVE);

        $this->driver->publish(
            $this->exchangeName,
            new Message($data, $routingKey, $headers, $correlationId, $replyTo)
        );

        $this->driver->consume(
            $replyTo,
            function (Message $message) use ($correlationId, &$response) {
                if ($message->getCorrelationId() == $correlationId) {
                    $response = $message->getBody();
                    return QueueHandler::STOP_CONSUMING;
                }
                return QueueHandler::CONTINUE_CONSUMING;
            },
            $this->timeout
        );

        return $response;
    }
}
