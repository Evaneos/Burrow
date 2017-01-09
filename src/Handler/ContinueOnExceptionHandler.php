<?php

namespace Burrow\Handler;

use Burrow\ConsumeOptions;
use Burrow\Exception\ConsumerException;
use Burrow\Message;
use Burrow\QueueHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class ContinueOnExceptionHandler implements QueueHandler, LoggerAwareInterface
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
        } catch (\Exception $e) {
            $this->logger->error('Received exception', ['exception' => $e]);

            if ($e instanceof ConsumerException) {
                return false;
            }
        }

        return true;
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
        return $this->handler->options($options);
    }
}
