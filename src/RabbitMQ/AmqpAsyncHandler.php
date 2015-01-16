<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueueHandler;
use Burrow\QueueConsumer;

class AmqpAsyncHandler extends AmqpDaemonizer implements QueueHandler
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
     * @see \Burrow\QueueHandler::registerConsumer()
     */
    public function registerConsumer(QueueConsumer $consumer)
    {
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($this->queueName, '', false, false, false, false, function (AMQPMessage $message) use ($consumer) {
            try {
                $consumer->consume($message->body);
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            } catch (\Exception $e) {
                // beware of unlimited loop !
                $message->delivery_info['channel']->basic_reject($message->delivery_info['delivery_tag'], true);
            }
        });
    }
}
