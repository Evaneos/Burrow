<?php

namespace Burrow;

interface Daemonizable
{
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
