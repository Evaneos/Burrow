<?php

namespace Burrow\Publisher;

use Burrow\Driver;
use Burrow\Message;
use Burrow\QueuePublisher;

class AsyncPublisher implements QueuePublisher
{
    /** @var Driver */
    protected $driver;

    /** @var string */
    protected $exchangeName;

    /**
     * Constructor
     *
     * @param Driver $driver
     * @param string $exchangeName
     */
    public function __construct(Driver $driver, $exchangeName)
    {
        $this->driver = $driver;
        $this->exchangeName = $exchangeName;
    }

    /**
     * Publish a message on the queue
     *
     * @param mixed   $data
     * @param string   $routingKey
     * @param string[] $headers
     *
     * @return mixed
     */
    public function publish($data, $routingKey = '', array $headers = [])
    {
        $this->driver->publish($this->exchangeName, new Message($data, $routingKey, $headers));

        return null;
    }
}
