<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueueHandler;
use Burrow\QueueConsumer;
use Burrow\Daemonizable;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class AmqpAsyncHandler extends AmqpDaemonizer implements QueueHandler, Daemonizable, LoggerAwareInterface
{
    /**
     * @var string
     */
    protected $queueName;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

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
        $memory = 0;
        $self = $this;
        
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($this->queueName, '', false, false, false, false, function (AMQPMessage $message) use ($self, $consumer, $memory) {
            try {
                $consumer->consume(unserialize($message->body));
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                
                $currentMemory = memory_get_usage(true);
                if ($self->logger && $memory > 0 && $currentMemory > $memory) {
                    $self->logger->warning('Memory usage increased by ' . $currentMemory-$memory . 'o (' . $currentMemory . 'o)');
                }
                $memory = $currentMemory;
            } catch (\Exception $e) {
                // beware of unlimited loop !
                $message->delivery_info['channel']->basic_reject($message->delivery_info['delivery_tag'], true);
                if ($self->logger) {
                    $self->logger->error($e->getMessage());
                }
            }
        });
    }
    
    /**
     * (non-PHPdoc)
     * @see \Psr\Log\LoggerAwareInterface::setLogger()
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
}
