<?php
namespace Burrow;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractWorker implements Worker, LoggerAwareInterface
{
    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var QueueSersvice
     */
    protected $queueService;
    
    /**
     * Constructor
     * 
     * @param QueueService $queueService
     */
    public function __construct(QueueService $queueService)
    {
        $this->setQueueService($queueService);
    }
    
    /**
     * (non-PHPdoc)
     * @see \Burrow\Worker::setQueueService()
     */
    public function setQueueService(QueueService $queueService)
    {
        $this->queueService = $queueService;
        
        $this->init();
    }

    /**
     * Init the consumption
     */
    abstract protected function init();

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

        $this->queueService->daemonize();
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
                $this->queueService->shutdown();
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
