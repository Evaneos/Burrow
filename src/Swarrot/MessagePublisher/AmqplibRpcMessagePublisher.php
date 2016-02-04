<?php
namespace Burrow\Swarrot\MessagePublisher;

use PhpAmqpLib\Channel\AMQPChannel;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Broker\MessageProvider\PhpAmqpLibMessageProvider;
use Swarrot\Broker\MessagePublisher\PhpAmqpLibMessagePublisher;

class AmqplibRpcMessagePublisher extends PhpAmqpLibMessagePublisher implements RpcMessagePublisher {

    /** @var AMQPChannel $channel */
    private $channel;

    /**
     * Constructor
     *
     * @param AMQPChannel $channel
     * @param string      $exchange
     */
    public function __construct(AMQPChannel $channel, $exchange)
    {
        $this->channel  = $channel;
        parent::__construct($channel, $exchange);
    }

    /**
     * @return MessageProviderInterface
     */
    public function getReturnQueueProvider()
    {
        $name = substr(sha1(uniqid(mt_rand(), true)), 0, 10);
        $this->channel->queue_declare($name, false, false, true, false);

        return new PhpAmqpLibMessageProvider($this->channel, $name);
    }
} 