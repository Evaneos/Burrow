<?php

namespace Burrow\RabbitMQ;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpService
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
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $pass;

    /**
     * @var array
     */
    protected $exchanges = array();

    /**
     * @param string $host
     * @param int    $port
     * @param string $user
     * @param string $pass
     */
    public function __construct($host, $port, $user, $pass)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     * @return AMQPChannel
     */
    private function getChannel()
    {
        if (null === $this->connection) {
            $this->connection = new AMQPConnection($this->host, $this->port, $this->user, $this->pass);
            $this->channel = $this->connection->channel();
        }

        return $this->channel;
    }

    /**
     * @param string $exchange
     */
    private function declareExchangeIfNotExist($exchange)
    {
        if (!isset($this->exchanges[$exchange])) {
            $this->exchanges[$exchange] = $exchange;
            $this->getChannel()->exchange_declare($exchange, 'fanout', false, true, false);
        }
    }

    /**
     * @param string $exchange
     * @param mixed  $data
     *
     * @return bool
     */
    public function publish($exchange, $data)
    {
        $this->declareExchangeIfNotExist($exchange);
        $msg = new AMQPMessage(serialize($data),
            array('delivery_mode' => 2)
        );

        $this->getChannel()->basic_publish($msg, $exchange);

        return true;
    }

    /**
     * @param string   $exchange Name of the exchnge to bind to
     * @param callable $callback Callable function
     * @param string   $queue    Optional queue name to bind to. If none, rabbitmq will generate one.
     *
     * @return void
     */
    public function registerConsumer($exchange, callable $callback, $queue = '')
    {
        $this->declareExchangeIfNotExist($exchange);

        list($queue,,) = $this->getChannel()->queue_declare($queue, false, true, false, false);
        $this->getChannel()->queue_bind($queue, $exchange);

        $this->getChannel()->basic_qos(null, 1, null);
        $this->getChannel()->basic_consume($queue, '', false, false, false, false, function (AMQPMessage $message) use ($callback) {
            try {
                call_user_func($callback, unserialize($message->body));
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            } catch (\Exception $e) {
                // beware of unlimited loop !
                // $message->delivery_info['channel']->basic_reject($message->delivery_info['delivery_tag'], true);
            }
        });
    }

    /**
     * Run as a daemon
     */
    public function daemonize()
    {
        while (count($this->getChannel()->callbacks)) {
            $this->getChannel()->wait();
        }
    }

    /**
     * Stop current connection
     */
    public function shutdown()
    {
        $this->getChannel()->close();
        $this->connection->close();
    }
}
