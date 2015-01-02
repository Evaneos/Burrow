<?php

namespace Burrow\RabbitMQ;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class Worker implements LoggerAwareInterface
{
    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var AmqpService
     */
    private $amqpService;

    /**
     * @param AmqpService $amqpService
     */
    public function __construct(AmqpService $amqpService)
    {
        $this->amqpService = $amqpService;
        $this->dispatcher = new EventDispatcher($this->amqpService);
    }

    /**
     * @param $queue
     * @param $callback
     */
    public function registerListener($queue, $callback)
    {
        $this->dispatcher->on($queue, $callback);
    }

    /**
     * Run as a daemon
     */
    public function daemonize()
    {
        $this->sessionId = uniqid();

        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, array($this, 'signalHandler'));
            pcntl_signal(SIGINT, array($this, 'signalHandler'));
            pcntl_signal(SIGHUP, array($this, 'signalHandler'));
        }

        $this->amqpService->daemonize();
    }

    /**
     * @param int $signal
     */
    public function signalHandler($signal)
    {
        switch ($signal) {
            case SIGINT:
            case SIGTERM:
                if ($this->logger) {
                    $this->logger->alert('Worker killed or terminated', array('sessionId', $this->sessionId));
                }
                $this->amqpService->shutdown();
                exit(1);
                break;
            case SIGHUP:
                if ($this->logger) {
                    $this->logger->info('Starting daemon', array('session' => $this->sessionId));
                }
                break;
        }
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
