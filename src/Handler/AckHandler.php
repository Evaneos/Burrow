<?php

namespace Burrow\Handler;

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
            $this->handler->handle($message);
            $this->driver->ack($message);
            return true;
        } catch (\Exception $e) {
            $this->driver->nack($message, $this->requeueOnFailure);
            throw $e;
        }
    }
}
