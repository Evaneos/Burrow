<?php

namespace Burrow;

interface Daemon
{
    /**
     * Start the daemon
     *
     * @return void
     */
    public function start();

    /**
     * Stop the daemon
     *
     * @return void
     */
    public function stop();

    /**
     * Set a monitor.
     *
     * @param DaemonMonitor $monitor
     *
     * @return void
     */
    public function setMonitor(DaemonMonitor $monitor);
}
