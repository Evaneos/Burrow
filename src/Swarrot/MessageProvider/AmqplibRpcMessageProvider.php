<?php
namespace Burrow\Swarrot\MessageProvider;

use PhpAmqpLib\Channel\AMQPChannel;
use Swarrot\Broker\MessageProvider\PhpAmqpLibMessageProvider;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;
use Swarrot\Broker\MessagePublisher\PhpAmqpLibMessagePublisher;

class AmqplibRpcMessageProvider extends PhpAmqpLibMessageProvider implements RpcMessageProvider {

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @param AMQPChannel $channel
     * @param string      $queueName
     */
    public function __construct(AMQPChannel $channel, $queueName)
    {
        parent::__construct($channel, $queueName);
        $this->channel   = $channel;
    }

    /**
     * @return MessagePublisherInterface
     */
    public function getQueuePublisher($name = '')
    {
        return new PhpAmqpLibMessagePublisher($this->channel, $name);
    }
} 