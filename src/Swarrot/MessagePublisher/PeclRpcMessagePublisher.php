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
        $queue = new \AMQPQueue($this->exchange->getChannel());
        $queue->setFlags(\AMQP_EXCLUSIVE);
        $queue->setName(substr(sha1(uniqid(mt_rand(), true)), 0, 10));
        $queue->declareQueue();

        return new PeclPackageMessageProvider($queue);
    }
} 