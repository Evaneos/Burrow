<?php

namespace Burrow\Daemon;

use Burrow\ConsumeOptions;
use Burrow\Event\DaemonStarted;
use Burrow\Event\DaemonStopped;
use Burrow\Event\MessageConsumed;
use Burrow\Event\MessageReceived;
use Burrow\Event\NullEmitter;
use Evaneos\Daemon\Daemon;
use Evaneos\Daemon\DaemonMonitor;
use Burrow\Driver;
use Burrow\Message;
use Evaneos\Daemon\Monitor\NullMonitor;
use Burrow\QueueHandler;
use League\Event\Emitter;
use League\Event\EmitterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class QueueHandlingDaemon implements Daemon, LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    /** @var Driver */
    private $driver;

    /** @var QueueHandler */
    private $handler;

    /** @var string */
    private $queueName;

    /** @var DaemonMonitor */
    private $monitor;

    /** @var Emitter */
    private $eventEmitter;

    /**
     * Handler constructor.
     *
     * @param Driver                $driver
     * @param QueueHandler          $handler
     * @param string                $queueName
     * @param EmitterInterface|null $eventEmitter
     */
    public function __construct(
        Driver $driver,
        QueueHandler $handler,
        $queueName,
        EmitterInterface $eventEmitter = null
    ) {
        $this->driver = $driver;
        $this->handler = $handler;
        $this->queueName = $queueName;

        $this->monitor = new NullMonitor();
        $this->logger = new NullLogger();
        $this->eventEmitter = $eventEmitter ?: new NullEmitter();
    }

    /**
     * Run the daemon
     *
     * @return void
     */
    public function start()
    {
        $this->eventEmitter->emit(new DaemonStarted());

        $this->logger->info('Starting daemon...');

        $options = $this->handler->options(new ConsumeOptions());

        $this->driver->consume(
            $this->queueName,
            function (Message $message) {
                $this->eventEmitter->emit(new MessageReceived());
                $this->monitor->monitor($this, $message);

                $result = $this->handler->handle($message);

                $this->eventEmitter->emit(new MessageConsumed());
                pcntl_signal_dispatch();

                return $result;
            },
            $options->getTimeout(),
            $options->isAutoAck()
        );

        $this->stop();
    }

    /**
     * Stop the daemon
     *
     * @return void
     */
    public function stop()
    {
        $this->logger->info('Closing daemon...');

        $this->driver->close();

        $this->eventEmitter->emit(new DaemonStopped());
    }

    /**
     * Set a monitor.
     *
     * @param DaemonMonitor $monitor
     */
    public function setMonitor(DaemonMonitor $monitor)
    {
        $this->monitor = $monitor;
    }
}
