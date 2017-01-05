<?php

namespace Burrow\Handler;

use Burrow\Daemonizable;
use Burrow\Driver;
use Burrow\Message;
use Burrow\QueueConsumer;
use Burrow\QueueHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class UniversalHandler implements QueueHandler, Daemonizable, LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    /** @var Driver */
    private $driver;

    /** @var string */
    private $queueName;

    /** @var QueueConsumer */
    private $consumer;

    /** @var bool */
    private $requeueOnFailure;

    /** @var int */
    protected $memory = 0;

    /** @var bool */
    protected $stop = false;

    /**
     * Handler constructor.
     *
     * @param Driver $driver
     * @param string $queueName
     * @param bool   $requeueOnFailure
     */
    public function __construct(Driver $driver, $queueName, $requeueOnFailure = true)
    {
        $this->driver = $driver;
        $this->queueName = $queueName;
        $this->requeueOnFailure = $requeueOnFailure;

        $this->logger = new NullLogger();
    }

    /**
     * Register a consumer for the queue
     *
     * @param QueueConsumer $consumer consumer object
     *
     * @return void
     */
    public function registerConsumer(QueueConsumer $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * Run as a daemon
     *
     * @return void
     */
    public function daemonize()
    {
        $this->logger->info('Starting daemon...');

        $this->driver->consume(
            $this->queueName,
            function (Message $message) {
                $this->followMemoryUsage();
                try {
                    $returnValue = $this->consumer->consume($message->getBody(), $message->getHeaders());
                    $this->handleSyncMessage($message, $returnValue);
                    $this->driver->ack($this->queueName, $message->getDeliveryTag());
                    return true;
                } catch (\Exception $e) {
                    $this->logger->error('Received exception', ['exception' => $e]);
                    $this->driver->nack($this->queueName, $message->getDeliveryTag(), $this->requeueOnFailure);
                    return false;
                }
            }
        );

        $this->shutdown();
    }

    /**
     * Stop current connection / daemon
     *
     * @return void
     */
    public function shutdown()
    {
        $this->logger->info('Closing daemon...');

        $this->driver->close();
    }

    /**
     * @param Message $message
     * @param string  $returnValue
     */
    private function handleSyncMessage(Message $message, $returnValue)
    {
        if ($message->getCorrelationId() != '' && $message->getReplyTo() != '') {
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
    }

    /**
     * Follow the memory usage
     */
    private function followMemoryUsage()
    {
        $currentMemory = memory_get_usage(true);
        if ($this->logger && $this->memory > 0 && $currentMemory > $this->memory) {
            $this->logger
                ->warning(
                    'Memory usage increased',
                    array(
                        'bytes_increased_by'   => $currentMemory - $this->memory,
                        'bytes_current_memory' => $currentMemory
                    )
                );
        }
        $this->memory = $currentMemory;
    }
}
