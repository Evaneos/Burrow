<?php

namespace Burrow\Monitor;

use Burrow\Daemon;
use Burrow\DaemonMonitor;

class NullMonitor implements DaemonMonitor
{
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
    }
}
