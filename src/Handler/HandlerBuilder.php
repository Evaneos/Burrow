<?php

namespace Burrow\Handler;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Burrow\Driver;
use Burrow\QueueConsumer;
use Burrow\QueueHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HandlerBuilder
{
    /** @var Driver */
    private $driver;

    /** @var bool */
    private $autoAck;

    /** @var bool */
    private $sync;

    /** @var bool */
    private $requeueOnFailure;

    /** @var bool */
    private $stopOnFailure;

    /** @var LoggerInterface */
    private $logger;

    /**
     * HandlerBuilder constructor.
     *
     * @param Driver $driver
     */
    public function __construct(Driver $driver)
    {
        $this->driver = $driver;

        $this->autoAck = false;

        $this->sync = null;
        $this->requeueOnFailure = true;
        $this->stopOnFailure = true;

        $this->logger = new NullLogger();
    }

    /**
     * Keeps the autoAcking behavior
     */
    public function autoAck()
    {
        $this->autoAck = true;

        return $this->doNotRequeueOnFailure(); // cannot be re-queued if it's auto-acked
    }

    /**
     * Build a sync Handler.
     *
     * @return $this
     */
    public function sync()
    {
        $this->sync = true;

        return $this;
    }

    /**
     * Build an async Handler.
     *
     * @return $this
     */
    public function async()
    {
        $this->sync = false;

        return $this;
    }

    /**
     * Must the failed message be requeued.
     *
     * @return $this
     */
    public function doNotRequeueOnFailure()
    {
        $this->requeueOnFailure = false;

        return $this;
    }

    /**
     * Must the handler continue on failure
     *
     * @return $this
     */
    public function continueOnFailure()
    {
        $this->stopOnFailure = false;

        return $this;
    }

    /**
     * Set a logger.
     *
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function log(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Build the Handler.
     *
     * @param QueueConsumer $consumer
     *
     * @return QueueHandler
     *
     * @throws AssertionFailedException
     */
    public function build(QueueConsumer $consumer)
    {
        Assertion::notNull($this->sync, 'You must specify if the handler must be sync or async');

        // Sync
        $handler = $this->sync ?
            new SyncConsumerHandler($consumer, $this->driver) :
            new AsyncConsumerHandler($consumer);
        $handler->setLogger($this->logger);

        // Ack
        $handler = $this->autoAck ?
            $handler :
            new AckHandler($handler, $this->driver, $this->requeueOnFailure);

        // Stop / Continue
        $handler = $this->stopOnFailure ?
            new StopOnExceptionHandler($handler) :
            new ContinueOnExceptionHandler($handler);
        $handler->setLogger($this->logger);

        return $handler;
    }
}
