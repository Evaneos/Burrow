<?php

namespace Burrow\Daemon;

use Burrow\Daemonizable;
use Burrow\Driver;
use Burrow\Message;
use Burrow\QueueHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class QueueHandlingDaemon implements Daemonizable, LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    /** @var Driver */
    private $driver;

    /** @var string */
    private $queueName;

    /** @var QueueHandler */
    private $handler;

    /** @var int */
    protected $memory = 0;

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

        $this->logger = new NullLogger();
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
                return $this->handler->handle($message);
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
