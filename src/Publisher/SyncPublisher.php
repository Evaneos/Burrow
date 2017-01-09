<?php

namespace Burrow\Publisher;

use Burrow\Driver;
use Burrow\Message;
use Burrow\QueuePublisher;

class SyncPublisher implements QueuePublisher
{
    /** @var Driver */
    protected $driver;

    /** @var string */
    protected $exchangeName;

    /** @var mixed */
    private $response;

    /** @var string */
    private $correlationId;

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
     */
    public function publish($data, $routingKey = '', array $headers = [])
    {
        $this->response = null;
        $this->correlationId = uniqid();
        $replyTo = $this->driver->declareSimpleQueue('', Driver::QUEUE_EXCLUSIVE);

        $this->driver->publish(
            $this->exchangeName,
            new Message($data, $routingKey, $headers, $this->correlationId, $replyTo)
        );

        $this->driver->consume(
            $replyTo,
            function (Message $message) use ($replyTo) {
                if ($message->getCorrelationId() == $this->correlationId) {
                    $this->response = $message->getBody();
                    return false; // stop the consuming
                }

                return true;
            },
            $this->timeout
        );

        return $this->response;
    }
}
