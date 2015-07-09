<?php
namespace Burrow\Swarrot\MessagePublisher;

use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Broker\MessageProvider\PeclPackageMessageProvider;
use Swarrot\Broker\MessagePublisher\PeclPackageMessagePublisher;

class PeclRpcMessagePublisher extends PeclPackageMessagePublisher implements RpcMessagePublisher {

    /**
     * @return MessageProviderInterface
     */
    public function getReturnQueueProvider()
    {
        $channel = $this->exchange->getChannel();
        $name = substr(sha1(uniqid(mt_rand(), true)), 0, 10);

        $queue = new \AMQPQueue($channel);
        $queue->setFlags(\AMQP_EXCLUSIVE);
        $queue->setName($name);
        $queue->declareQueue();

        return new PeclPackageMessageProvider($queue);
    }
} 