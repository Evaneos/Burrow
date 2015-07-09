<?php
namespace Burrow\Swarrot\Processor;

use Burrow\QueueConsumer;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

class QueueConsumerProcessor implements ProcessorInterface {

    /**
     * @var QueueConsumer
     */
    private $consumer;

    /**
     * Consumer
     *
     * @param QueueConsumer $consumer
     */
    public function __construct(QueueConsumer $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * Process
     *
     * @param Message $message
     * @param array $options
     * @return bool|null|string|void
     */
    public function process(Message $message, array $options) {
        return serialize($this->consumer->consume(unserialize($message->getBody())));
    }
}