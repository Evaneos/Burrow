<?php

namespace Burrow\Handler;

use Burrow\ConsumeOptions;
use Burrow\Message;
use Burrow\QueueConsumer;
use Burrow\QueueHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class AsyncConsumerHandler implements QueueHandler, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var QueueConsumer */
    private $consumer;

    /**
     * ConsumerHandler constructor.
     *
     * @param QueueConsumer $consumer
     */
    public function __construct(QueueConsumer $consumer)
    {
        $this->consumer = $consumer;

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
        $this->consumer->consume($message->getBody(), $message->getHeaders());

        return self::CONTINUE_CONSUMING;
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
        return $options;
    }
}
