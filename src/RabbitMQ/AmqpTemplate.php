<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Burrow\Escaper;

abstract class AmqpTemplate
{
    /**
     * @var AMQPStreamConnection
     */
    protected $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var string
     */
    protected $escapeMode;

    /**
     * Constructor
     *
     * @param string $host
     * @param int    $port
     * @param string $user
     * @param string $pass
     * @param string $escapeMode
     */
    public function __construct($host, $port, $user, $pass, $escapeMode = Escaper::ESCAPE_MODE_SERIALIZE)
    {
        $this->connection = new AMQPLazyConnection($host, $port, $user, $pass);
        $this->channel = $this->connection->channel();
        $this->escapeMode = $escapeMode;
    }

    /**
     * Escape the message
     *
     * @param $message
     * @return string
     */
    protected function escape($message)
    {
        return Escaper::escape($message, $this->escapeMode);
    }

    /**
     * Unescape the message
     *
     * @param $message
     * @return string
     */
    protected function unescape($message)
    {
        return Escaper::unescape($message, $this->escapeMode);
    }
}
