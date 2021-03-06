<?php

namespace Burrow\Handler;

use Assert\AssertionFailedException;
use Burrow\ConsumeOptions;
use Burrow\Driver;
use Burrow\Exception\ConsumerException;
use Burrow\Message;
use Burrow\QueueConsumer;
use Burrow\QueueHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class SyncConsumerHandler implements QueueHandler, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var QueueConsumer */
    private $consumer;

    /** @var Driver */
    private $driver;

    /**
     * ConsumerHandler constructor.
     *
     * @param QueueConsumer $consumer
     * @param Driver        $driver
     */
    public function __construct(QueueConsumer $consumer, Driver $driver)
    {
        $this->consumer = $consumer;
        $this->driver = $driver;

        $this->logger = new NullLogger();
    }

    /**
     * Handle a message.
     *
     * @param Message $message
     *
     * @return bool
     *
     * @throws AssertionFailedException
     * @throws ConsumerException
     * @throws \InvalidArgumentException
     */
    public function handle(Message $message)
    {
        $returnValue = $this->consumer->consume($message->getBody(), $message->getHeaders());
        $this->handleSyncMessage($message, $returnValue);

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

    /**
     * Handle the return value.
     *
     * @param Message $message
     * @param string  $returnValue
     *
     * @throws ConsumerException
     * @throws AssertionFailedException
     * @throws \InvalidArgumentException
     */
    private function handleSyncMessage(Message $message, $returnValue)
    {
        self::checkMessageIsSync($message);

        $this->logger->debug(
            'Send return value back!',
            [
                'returnValue' => $returnValue,
                'correlationId' => $message->getCorrelationId(),
                'replyTo' => $message->getReplyTo()
            ]
        );

        $this->driver->publish(
            '',
            new Message(
                $returnValue,
                $message->getReplyTo(),
                $message->getHeaders(),
                $message->getCorrelationId()
            )
        );
    }

    /**
     * @param Message $message
     *
     * @throws ConsumerException
     */
    private static function checkMessageIsSync(Message $message)
    {
        if ($message->getCorrelationId() == '' && $message->getReplyTo() == '') {
            throw ConsumerException::invalidSyncMessage();
        }
    }
}
