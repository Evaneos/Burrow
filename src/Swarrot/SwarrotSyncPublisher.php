<?php
namespace Burrow\Swarrot;

use Burrow\QueuePublisher;
use Burrow\Swarrot\MessagePublisher\RpcMessagePublisher;
use Burrow\Swarrot\Processor\ReturnProcessor;
use Burrow\Escaper;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;
use Swarrot\Consumer;
use Swarrot\Processor\RPC\RpcClientProcessor;
use Swarrot\Processor\Stack\Builder;
use Psr\Log\LoggerInterface;

class SwarrotSyncPublisher implements QueuePublisher
{
    /**
     * @var MessagePublisherInterface
     */
    private $publisher;

    /**
     * @var string
     */
    private $callbackQueue;

    /**
     * @var string
     */
    private $correlationId;

    /**
     * @var int
     */
    private $timeout;
    
    /**
     * @var string
     */
    private $escapeMode;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param RpcMessagePublisher $publisher
     * @param int                 $timeout
     */
    public function __construct(
            RpcMessagePublisher $publisher,
            $timeout = 1,
            $escapeMode = Escaper::ESCAPE_MODE_SERIALIZE,
            LoggerInterface $logger = null
    ) {
        $this->publisher = $publisher;
        $this->timeout = $timeout;
        $this->escapeMode = $escapeMode;
        $this->logger = $logger;
    }

    /**
     * Publish a message on the queue
     *
     * @param  string $data
     * @param  string $routingKey
     * @return string|null|void
     */
    public function publish($data, $routingKey = "")
    {
        $returnProvider = $this->publisher->getReturnQueueProvider();

        $this->correlationId = uniqid();
        $this->callbackQueue = $returnProvider->getQueueName();

        $this->publisher->publish(
            new Message(Escaper::escape($data, $this->escapeMode), $this->getMessageProperties()),
            $routingKey ? $routingKey : null
        );

        $processor = new ReturnProcessor();

        $consumer = new Consumer(
            $returnProvider,
            (new Builder())
                ->push('Swarrot\Processor\Ack\AckProcessor', $returnProvider, $this->logger)
                ->push('Burrow\Swarrot\Processor\TimeoutProcessor', $this->logger)
                ->resolve(new RpcClientProcessor($processor))
        );
        $consumer->consume(
            array(
                'max_execution_time' => $this->timeout,
                'rpc_client_correlation_id' => $this->correlationId
            )
        );

        return $processor->getReturn();
    }

    /**
     * Returns the message parameters
     *
     * @return array
     */
    protected function getMessageProperties()
    {
        return array(
            'delivery_mode' => 2,
            'correlation_id' => $this->correlationId,
            'reply_to' => $this->callbackQueue
        );
    }
    
    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
