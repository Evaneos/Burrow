<?php
namespace Burrow\Swarrot;

use Burrow\QueuePublisher;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;

class SwarrotAsyncPublisher implements QueuePublisher
{
    /**
     * @var MessagePublisherInterface
     */
    private $publisher;

    /**
     * Constructor
     *
     * @param MessagePublisherInterface $publisher
     */
    public function __construct(MessagePublisherInterface $publisher)
    {
        $this->publisher = $publisher;
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
            new Message(serialize($data), $this->getMessageProperties()),
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
