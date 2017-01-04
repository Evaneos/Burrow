<?php
namespace Burrow\RabbitMQ;

use Burrow\Exception\ConsumerException;
use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueueHandler;
use Burrow\QueueConsumer;
use Burrow\Daemonizable;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
     * @var bool
     */
    protected $stop = false;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var bool
     */
    private $requeueOnFailure;

    /**
     * Constructor
     *
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $pass
     * @param string $queueName
     * @param string $escapeMode
     * @param bool $requeueOnFailure
     */
    public function __construct(
        $host,
        $port,
        $user,
        $pass,
        $queueName,
        $escapeMode = self::ESCAPE_MODE_SERIALIZE,
        $requeueOnFailure = true
    ) {
        parent::__construct($host, $port, $user, $pass, $escapeMode);
        $this->queueName = $queueName;
        $this->logger = new NullLogger();
        $this->requeueOnFailure = $requeueOnFailure;
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

        $this->getChannel()->basic_qos(null, 1, null);
        $this->getChannel()->basic_consume(
            $this->queueName,
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($self) {
                try {
                    $self->consume($message);
                    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

                    $currentMemory = memory_get_usage(true);
                    if ($self->getLogger() && $self->getMemory() > 0 && $currentMemory > $self->getMemory()) {
                        $self->getLogger()
                            ->warning(
                                'Memory usage increased',
                                array (
                                    'bytes_increased_by' => $currentMemory - $self->getMemory(),
                                    'bytes_current_memory' => $currentMemory
                                )
                            );
                    }
                    $self->setMemory($currentMemory);
                } catch (\Exception $e) {
                    // beware of infinite loop !
                    $message->delivery_info['channel']->basic_reject(
                        $message->delivery_info['delivery_tag'],
                        $this->requeueOnFailure
                    );
                    $self->getLogger()->error('Received exception', array('exception' => $e));

                    if ($e instanceof ConsumerException) {
                        $self->shutdown();
                    }
                }
            }
        );
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
        $this->logger->info('Registering consumer...');

        $this->initConsumer();

        $this->logger->info('Starting AMqpAsyncHandler daemon...');

        while (count($this->getChannel()->callbacks) && !$this->stop) {
            $this->getChannel()->wait();
        }

        $this->getChannel()->close();
        $this->connection->close();
    }

    /**
     * Shuts the daemon
     *
     * @return void
     */
    public function shutdown()
    {
        $this->logger->info('Closing AMqpAsyncHandler daemon...');

        $this->stop = true;
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
