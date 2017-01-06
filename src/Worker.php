<?php

namespace Burrow;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Worker implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var Daemon
     */
    private $daemon;
    
    /**
     * Constructor
     *
     * @param Daemon $daemon
     */
    public function __construct(Daemon $daemon)
    {
        $this->daemon = $daemon;
        $this->logger = new NullLogger();
    }

    /**
     * Run as a daemon
     *
     * @param string $sessionId
     *
     * @return void
     */
    public function run($sessionId = null)
    {
        $this->sessionId = ($sessionId !== null) ? $sessionId : uniqid();

        if (function_exists('pcntl_signal')) {
            declare (ticks = 1);
            pcntl_signal(SIGTERM, [$this, 'signalHandler']);
            pcntl_signal(SIGINT, [$this, 'signalHandler']);
            pcntl_signal(SIGHUP, [$this, 'signalHandler']);
        }

        $this->daemon->start();
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
                $this->logger->alert('Worker killed or terminated', ['sessionId', $this->sessionId]);
                $this->daemon->stop();
                exit(1);
                break;
            case SIGHUP:
                $this->logger->info('Starting daemon', ['session' => $this->sessionId]);
                break;
        }
    }
}
