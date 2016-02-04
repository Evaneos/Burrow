<?php
namespace Burrow\Swarrot;

use Burrow\QueuePublisher;
use Burrow\Escaper;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;

class SwarrotAsyncPublisher implements QueuePublisher
{
    /**
     * @var MessagePublisherInterface
     */
    private $publisher;
    
    /**
     * @var string
     */
    private $escapeMode;

    /**
     * Constructor
     *
     * @param MessagePublisherInterface $publisher
     */
    public function __construct(MessagePublisherInterface $publisher, $escapeMode = Escaper::ESCAPE_MODE_SERIALIZE)
    {
        $this->publisher = $publisher;
        $this->escapeMode = $escapeMode;
    }

    /**
     * Publish a message on the queue
     *
     * @param  string $data
     * @param  string $routingKey
     * @return string|null|void
     */
    public function publish($data, $routingKey = "")
    {
        $this->publisher->publish(
            new Message(Escaper::escape($data, $this->escapeMode), $this->getMessageProperties()),
            $routingKey ? $routingKey : null
        );
    }

    /**
     * Returns the message parameters
     *
     * @return array
     */
    protected function getMessageProperties()
    {
        return array('delivery_mode' => 2);
    }
}
