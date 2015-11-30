<?php
namespace Burrow\Swarrot\Processor;

use Burrow\QueueConsumer;
use Burrow\Escaper;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

class QueueConsumerProcessor implements ProcessorInterface {

    /**
     * @var QueueConsumer
     */
    private $consumer;
    
    /**
     * @var string
     */
    private $escapeMode;

    /**
     * Consumer
     *
     * @param QueueConsumer $consumer
     */
    public function __construct(QueueConsumer $consumer, $escapeMode = Escaper::ESCAPE_MODE_SERIALIZE)
    {
        $this->consumer = $consumer;
        $this->escapeMode = $escapeMode;
    }

    /**
     * Process
     *
     * @param Message $message
     * @param array $options
     * @return bool|null|string|void
     */
    public function process(Message $message, array $options) {
        return Escaper::escape(
            $this->consumer->consume(
                Escaper::unescape(
                    $message->getBody(),
                    $this->escapeMode
                )
            ),
            $this->escapeMode
        );
    }
}