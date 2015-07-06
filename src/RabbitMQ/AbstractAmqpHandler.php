<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueueHandler;
use Burrow\QueueConsumer;
use Burrow\Daemonizable;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractAmqpHandler extends AmqpTemplate implements QueueHandler, Daemonizable, LoggerAwareInterface
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
     * @param string $escapeMode
     */
    public function __construct($host, $port, $user, $pass, $queueName, $escapeMode = self::ESCAPE_MODE_SERIALIZE)
    {
        parent::__construct($host, $port, $user, $pass, $escapeMode);
        $this->queueName = $queueName;
    }

    /**
     * Sets the consumer
     *
     * @param  QueueConsumer $consumer
     * @return void
     */
    public function registerConsumer(QueueConsumer $consumer)
    {
        $this->consumer = $consumer;
    }
    
    /**
     * Returns the consumer
     *
     * @return QueueConsumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }
    
    /**
     * Returns the logger
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * Returns the current memory usage
     *
     * @return int
     */
    public function getMemory()
    {
        return $this->memory;
    }
    
    /**
     * Sets the memory usage
     *
     * @param  int $memory
     * @return void
     */
    public function setMemory($memory)
    {
        $this->memory = $memory;
    }
    
    /**
     * Inits the consumer
     *
     * @return void
     */
    public function initConsumer()
    {
        $self = $this;
        
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($this->queueName, '', false, false, false, false, function (AMQPMessage $message) use ($self) {
            try {
                $self->consume($message);
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                
                $currentMemory = memory_get_usage(true);
                if ($self->getLogger() && $self->getMemory() > 0 && $currentMemory > $self->getMemory()) {
                    $self->getLogger()->warning('Memory usage increased by ' . ($currentMemory - $self->getMemory()) . 'o (' . $currentMemory . 'o)');
                }
                $self->setMemory($currentMemory);
            } catch (\Exception $e) {
                // beware of unlimited loop !
                $message->delivery_info['channel']->basic_reject($message->delivery_info['delivery_tag'], true);
                if ($self->getLogger()) {
                    $self->getLogger()->error($e->getMessage());
                }
            }
        });
    }

    /**
     * Consume the message
     *
     * @param AMQPMessage $message
     * @return void
     */
    abstract public function consume(AMQPMessage $message);

    /**
     * Starts the daemon
     *
     * @return void
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
     * Shuts the daemon
     *
     * @return void
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
     * Sets the logger
     *
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
