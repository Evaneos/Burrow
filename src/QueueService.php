<?php
namespace Burrow;

interface QueueService
{
    /**
     * Publish a message on the queue
     * 
     * @param string $data
     *
     * @return void
     */
    public function publish($data);

    /**
     * Register a consumer for the queue
     * 
     * @param callable $callback Callable function
     *
     * @return void
     */
    public function registerConsumer(callable $callback);

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
