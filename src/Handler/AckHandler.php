<?php

namespace Burrow\Handler;

use Burrow\ConsumeOptions;
use Burrow\Driver;
use Burrow\Message;
use Burrow\QueueHandler;

class AckHandler implements QueueHandler
{
    /** @var QueueHandler */
    private $handler;

    /** @var Driver */
    private $driver;

    /** @var bool */
    private $requeueOnFailure;

    /**
     * AckHandler constructor.
     *
     * @param QueueHandler $handler
     * @param Driver       $driver
     * @param bool         $requeueOnFailure
     */
    public function __construct(QueueHandler $handler, Driver $driver, $requeueOnFailure = true)
    {
        $this->handler = $handler;
        $this->driver = $driver;
        $this->requeueOnFailure = $requeueOnFailure;
    }

    /**
     * Handle a message.
     *
     * @param Message $message
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function handle(Message $message)
    {
        try {
            $result = $this->handler->handle($message);
            $this->driver->ack($message);
            return $result;
        } catch (\Exception $e) {
            $this->driver->nack($message, $this->requeueOnFailure);
            throw $e;
        }
    }

    /**
     * Modify and return the options for consumption.
     *
     * @param ConsumeOptions $options
     *
     * @return ConsumeOptions
     */
    public function options(ConsumeOptions $options)
    {
        $options->disableAutoAck();

        return $this->handler->options($options);
    }
}
