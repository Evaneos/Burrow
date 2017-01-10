<?php

namespace Burrow\Handler;

use Assert\Assertion;
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
        $this->consumer = null;

        $this->sync = null;
        $this->requeueOnFailure = true;
        $this->stopOnFailure = true;

        $this->logger = new NullLogger();
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
     */
    public function build(QueueConsumer $consumer)
    {
        Assertion::notNull($this->sync);

        $syncAsync = ($this->sync) ?
            new SyncConsumerHandler($consumer, $this->driver) :
            new AsyncConsumerHandler($consumer);

        $ackHandler = new AckHandler($syncAsync, $this->driver, $this->requeueOnFailure);

        $handler = ($this->stopOnFailure) ?
            new StopOnExceptionHandler($ackHandler) :
            new ContinueOnExceptionHandler($ackHandler);

        $syncAsync->setLogger($this->logger);
        $handler->setLogger($this->logger);

        return $handler;
    }
}
