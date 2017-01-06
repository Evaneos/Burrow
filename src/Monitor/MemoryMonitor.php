<?php

namespace Burrow\Monitor;

use Burrow\Daemon;
use Burrow\DaemonMonitor;
use Psr\Log\LoggerInterface;

class MemoryMonitor implements DaemonMonitor
{
    /** @var LoggerInterface */
    private $logger;

    /** @var int */
    private $memory;

    /**
     * MemoryMonitor constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->memory = 0;
    }

    /**
     * Monitor the daemon
     *
     * @param Daemon $daemon
     * @param mixed  $currentObject
     *
     * @return void
     */
    public function monitor(Daemon $daemon, $currentObject = null)
    {
        $currentMemory = memory_get_usage(true);
        if ($this->logger && $this->memory > 0 && $currentMemory > $this->memory) {
            $this->logger
                ->warning(
                    'Memory usage increased',
                    [
                        'bytes_increased_by'   => $currentMemory - $this->memory,
                        'bytes_current_memory' => $currentMemory
                    ]
                );
        }
        $this->memory = $currentMemory;
    }
}
