<?php

namespace Burrow\Daemon;

use Burrow\ConsumeOptions;
use Evaneos\Daemon\Daemon;
use Evaneos\Daemon\DaemonMonitor;
use Burrow\Driver;
use Burrow\Message;
use Evaneos\Daemon\Monitor\NullMonitor;
use Burrow\QueueHandler;
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

    /**
     * Handler constructor.
     *
     * @param Driver        $driver
     * @param QueueHandler $handler
     * @param string        $queueName
     */
    public function __construct(Driver $driver, QueueHandler $handler, $queueName)
    {
        $this->driver = $driver;
        $this->handler = $handler;
        $this->queueName = $queueName;

        $this->monitor = new NullMonitor();
        $this->logger = new NullLogger();
    }

    /**
     * Run the daemon
     *
     * @return void
     */
    public function start()
    {
        $this->logger->info('Starting daemon...');

        $options = $this->handler->options(new ConsumeOptions());

        $this->driver->consume(
            $this->queueName,
            function (Message $message) {
                $this->monitor->monitor($this, $message);
                return $this->handler->handle($message);
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
