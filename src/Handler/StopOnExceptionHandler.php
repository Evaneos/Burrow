<?php

namespace Burrow\Handler;

use Burrow\Message;
use Burrow\QueueHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class StopOnExceptionHandler implements QueueHandler, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var QueueHandler */
    private $handler;

    /**
     * AckHandler constructor.
     *
     * @param QueueHandler $handler
     */
    public function __construct(QueueHandler $handler)
    {
        $this->handler = $handler;

        $this->logger = new NullLogger();
    }

    /**
     * Handle a message.
     *
     * @param Message $message
     *
     * @return bool
     */
    public function handle(Message $message)
    {
        try {
            $this->handler->handle($message);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Received exception', ['exception' => $e]);
            return false;
        }
    }
}
