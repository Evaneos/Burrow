<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

abstract class AmqpTemplate
{
    const ESCAPE_MODE_NONE      = 'none';
    const ESCAPE_MODE_SERIALIZE = 'serialize';
    const ESCAPE_MODE_JSON      = 'json';

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
    public function __construct($host, $port, $user, $pass, $escapeMode = self::ESCAPE_MODE_SERIALIZE)
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
        $escapedMessage = $message;
        switch($this->escapeMode) {
            case self::ESCAPE_MODE_SERIALIZE :
                $escapedMessage = serialize($message);
                break;
            case self::ESCAPE_MODE_JSON :
                $escapedMessage = json_encode($message);
                break;
        }
        return $escapedMessage;
    }

    /**
     * Unescape the message
     *
     * @param $message
     * @return string
     */
    protected function unescape($message)
    {
        $unescapedMessage = $message;
        switch($this->escapeMode) {
            case self::ESCAPE_MODE_SERIALIZE :
                $unescapedMessage = unserialize($message);
                break;
            case self::ESCAPE_MODE_JSON :
                $unescapedMessage = json_decode($message);
                break;
        }
        return $unescapedMessage;
    }
}
