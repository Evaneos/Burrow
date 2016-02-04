<?php
namespace Burrow\Swarrot\MessageProvider;

use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;

interface RpcMessageProvider extends MessageProviderInterface {

    /**
     * @return MessagePublisherInterface
     */
    public function getQueuePublisher($name = '');
} 