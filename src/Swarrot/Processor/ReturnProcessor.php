<?php
namespace Burrow\Swarrot\Processor;

use Burrow\QueueConsumer;
use Burrow\Escaper;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

class ReturnProcessor implements ProcessorInterface {

    /**
     * @var mixed
     */
    private $return;
    
    /**
     * @var string
     */
    private $escapeMode;

    public function __construct($escapeMode = Escaper::ESCAPE_MODE_SERIALIZE)
    {
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
        $this->return = Escaper::unescape($message->getBody(), $this->escapeMode);
        return $this->return;
    }

    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->return;
    }
}