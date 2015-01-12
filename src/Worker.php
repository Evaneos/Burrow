<?php
namespace Burrow;

interface Worker
{
    /**
     * Setter for the queue service
     * 
     * @param QueueService $queueService
     */
    public function setQueueService(QueueService $queueService);
    
    /**
     * Run as a daemon
     */
    public function daemonize();
}
