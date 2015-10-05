<?php
namespace Burrow;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
     * @var Daemonizable
     */
    private $daemonizable;
    
    /**
     * Constructor
     *
     * @param Daemonizable $daemonizable
     */
    public function __construct(Daemonizable $daemonizable)
    {
        $this->daemonizable = $daemonizable;
        $this->logger = new NullLogger();
    }

    /**
     * Run as a daemon
     *
     * @return void
     */
    public function run()
    {
        $this->sessionId = uniqid();

        if (function_exists('pcntl_signal')) {
            declare (ticks = 1);
            pcntl_signal(SIGTERM, array($this, 'signalHandler'));
            pcntl_signal(SIGINT, array($this, 'signalHandler'));
            pcntl_signal(SIGHUP, array($this, 'signalHandler'));
        }

        $this->daemonizable->daemonize();
    }

    /**
     * @param  int $signal
     * @return void
     */
    public function signalHandler($signal)
    {
        switch ($signal) {
            case SIGINT:
            case SIGTERM:
                $this->logger->alert('Worker killed or terminated', array('sessionId', $this->sessionId));
                $this->daemonizable->shutdown();
                exit(1);
                break;
            case SIGHUP:
                $this->logger->info('Starting daemon', array('session' => $this->sessionId));
                break;
        }
    }

    /**
     * Sets a logger instance on the object
     *
     * @param  LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
