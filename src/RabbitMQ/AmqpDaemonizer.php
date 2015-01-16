<?php
namespace Burrow\RabbitMQ;

class AmqpDaemonizer extends AmqpTemplate
{
    /**
     * Run as a daemon
     * 
     * @return void
     */
    public function daemonize()
    {
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }
    
    /**
     * Stop current connection / daemon
     *
     * @return void
     */
    public function shutdown()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
