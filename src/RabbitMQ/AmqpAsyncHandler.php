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

class AmqpAsyncHandler extends AmqpTemplate implements QueueHandler, Daemonizable, LoggerAwareInterface
{
    /**
     * @var string
     */
    protected $queueName;
    
    /**
     * @var QueueConsumer
     */
    protected $consumer;
    
    /**
     * @var int
     */
    protected $memory = 0;
    
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
        $this->consumer = $consumer;
    }
    
    /**
     * Inits the consumer
     */
    public function initConsumer()
    {
        $self = $this;
        
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($this->queueName, '', false, false, false, false, function (AMQPMessage $message) use ($self) {
            try {
                $self->consumer->consume(unserialize($message->body));
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                
                $currentMemory = memory_get_usage(true);
                if ($self->logger && $self->memory > 0 && $currentMemory > $self->memory) {
                    $self->logger->warning('Memory usage increased by ' . ($currentMemory - $self->memory) . 'o (' . $currentMemory . 'o)');
                }
                $self->memory = $currentMemory;
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
     * @see \Burrow\Daemonizable::daemonize()
     */
    public function daemonize()
    {
        if ($this->logger) {
            $this->logger->info('Registering consumer...');
        }
        
        $this->initConsumer();
        
        if ($this->logger) {
            $this->logger->info('Starting AMqpAsyncHandler daemon...');
        }
        
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see \Burrow\Daemonizable::shutdown()
     */
    public function shutdown()
    {
        if ($this->logger) {
            $this->logger->info('Closing AMqpAsyncHandler daemon...');
        }
        
        $this->channel->close();
        $this->connection->close();
    }
    
    /**
     * (non-PHPdoc)
     * @see \Psr\Log\LoggerAwareInterface::setLogger()
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
}
