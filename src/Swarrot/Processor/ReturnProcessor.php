<?php
namespace Burrow\Swarrot\Processor;

use Burrow\QueueConsumer;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

class ReturnProcessor implements ProcessorInterface {

    /**
     * @var mixed
     */
    private $return;

    /**
     * Process
     *
     * @param Message $message
     * @param array $options
     * @return bool|null|string|void
     */
    public function process(Message $message, array $options) {
        $this->return = unserialize($message->getBody());
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