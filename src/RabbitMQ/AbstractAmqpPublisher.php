<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueuePublisher;

class AbstractAmqpPublisher extends AmqpTemplate implements QueuePublisher
{
    /**
     * @var string
     */
    protected $exchangeName;

    /**
     * Constructor
     * 
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $pass
     * @param string $exchangeName
     */
    public function __construct($host, $port, $user, $pass, $exchangeName)
    {
        parent::__construct($host, $port, $user, $pass);
        $this->exchangeName = $exchangeName;
    }

    /**
     * Publish a message on the queue
     *
     * @param string $data
     * @param string $routingKey
     *
     * @return mixed|null|void
     */
    public function publish($data, $routingKey = '')
    {
        $msg = new AMQPMessage(serialize($data), $this->getMessageProperties());

        $this->channel->basic_publish($msg, $this->exchangeName, $routingKey);
    }

    /**
     * Returns the message parameters
     *
     * @return array
     */
    protected function getMessageProperties()
    {
        return array('delivery_mode' => 2);
    }
}
