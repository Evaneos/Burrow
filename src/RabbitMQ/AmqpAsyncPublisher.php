<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueuePublisher;

class AmqpAsyncPublisher extends AmqpTemplate implements QueuePublisher
{
    /**
     * @var string
     */
    protected $queueName;

    /**
     * Constructor
     * 
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $pass
     * @param string $queueName
     */
    public function __construct($host, $port, $user, $pass, $queueName)
    {
        parent::__construct($host, $port, $user, $pass);
        $this->queueName = $queueName;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Burrow\QueuePublisher::publish()
     */
    public function publish($data, $routingKey = '')
    {
        $msg = new AMQPMessage($data, array('delivery_mode' => 2));

        $this->channel->basic_publish($msg, $this->queueName, $routingKey);
    }
}
