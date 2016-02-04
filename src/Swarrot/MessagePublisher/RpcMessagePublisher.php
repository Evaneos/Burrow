<?php
namespace Burrow\Swarrot\MessagePublisher;

use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;

interface RpcMessagePublisher extends MessagePublisherInterface {

    /**
     * @return MessageProviderInterface
     */
    public function getReturnQueueProvider();
} 