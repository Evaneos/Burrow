<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueueService;

abstract class AbstractAmqpService implements QueueService
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
    protected $queueName;

    /**
     * Constructor
     * 
     * @param string $host
     * @param int    $port
     * @param string $user
     * @param string $pass
     */
    public function __construct($host, $port, $user, $pass, $queueName)
    {
        $this->connection = new AMQPConnection($host, $port, $user, $pass);
        $this->channel = $this->connection->channel();
        $this->queueName = $queueName;
        
        $this->initQueue();
    }
    
    /**
     * Getter for the channel
     * 
     * @return AMQPChannel
     */
    protected function getChannel()
    {
        return $this->channel;
    }
    
    /**
     * Initializes the queue
     */
    protected abstract function initQueue();
    
    
    /**
     * (non-PHPdoc)
     * @see \Burrow\QueueService::publish()
     */
    public function publish($data)
    {
        $msg = new AMQPMessage($data, array('delivery_mode' => 2));

        $this->getChannel()->basic_publish($msg, $this->queueName);
    }

    /**
     * (non-PHPdoc)
     * @see \Burrow\QueueService::registerConsumer()
     */
    public abstract function registerConsumer(callable $callback);

    /**
     * (non-PHPdoc)
     * @see \Burrow\QueueService::daemonize()
     */
    public function daemonize()
    {
        while (count($this->getChannel()->callbacks)) {
            $this->getChannel()->wait();
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Burrow\QueueService::shutdown()
     */
    public function shutdown()
    {
        $this->getChannel()->close();
        $this->connection->close();
    }
}
