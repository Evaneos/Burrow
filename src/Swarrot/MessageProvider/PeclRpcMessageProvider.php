<?php
namespace Burrow\Swarrot\MessageProvider;

use Swarrot\Broker\MessageProvider\PeclPackageMessageProvider;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;
use Swarrot\Broker\MessagePublisher\PeclPackageMessagePublisher;

class PeclRpcMessageProvider extends PeclPackageMessageProvider implements RpcMessageProvider {
    /**
     * @return MessagePublisherInterface
     */
    public function getQueuePublisher($name = '')
    {
        $exchange = new \AMQPExchange($this->queue->getChannel());
        $exchange->setName($name);

        return new PeclPackageMessagePublisher($exchange);
    }
} 