<?php
namespace Burrow;

interface QueueHandler
{

    /**
     * Register a consumer for the queue
     * 
     * @param QueueConsumer $consumer consumer object
     *
     * @return void
     */
    public function registerConsumer(QueueConsumer $consumer);

    /**
     * Run as a daemon
     * 
     * @return void
     */
    public function daemonize();

    /**
     * Stop current connection / daemon
     * 
     * @return void
     */
    public function shutdown();
}
