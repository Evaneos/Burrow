<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class AmqpTemplate
{
    /**
     * @var AMQPConnection
     */
    protected $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * Constructor
     * 
     * @param string $host
     * @param int    $port
     * @param string $user
     * @param string $pass
     */
    public function __construct($host, $port, $user, $pass)
    {
        $this->connection = new AMQPConnection($host, $port, $user, $pass);
        $this->channel = $this->connection->channel();
    }
}
