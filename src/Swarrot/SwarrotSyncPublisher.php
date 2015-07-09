<?php
namespace Burrow\Swarrot;

use Burrow\QueuePublisher;
use Burrow\Swarrot\MessagePublisher\RpcMessagePublisher;
use Burrow\Swarrot\Processor\ReturnProcessor;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;
use Swarrot\Consumer;
use Swarrot\Processor\RPC\RpcClientProcessor;
use Swarrot\Processor\Stack\Builder;

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
     * Constructor
     *
     * @param RpcMessagePublisher $publisher
     * @param int                 $timeout
     */
    public function __construct(RpcMessagePublisher $publisher, $timeout = 1)
    {
        $this->publisher = $publisher;
        $this->timeout = $timeout;
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
            new Message(serialize($data), $this->getMessageProperties()),
            $routingKey ? $routingKey : null
        );

        $processor = new ReturnProcessor();

        $consumer = new Consumer(
            $returnProvider,
            (new Builder())
                ->push('Swarrot\Processor\Ack\AckProcessor', $returnProvider)
                ->push('Burrow\Swarrot\Processor\TimeoutProcessor')
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
}
