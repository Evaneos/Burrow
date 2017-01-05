<?php

namespace Burrow\Publisher;

use Burrow\QueuePublisher;
use Burrow\Serializer;

class SerializingPublisher implements QueuePublisher
{
    /** @var QueuePublisher */
    private $publisher;

    /** @var Serializer */
    private $serializer;

    /**
     * SerializingPublisher constructor.
     *
     * @param QueuePublisher $publisher
     * @param Serializer     $serializer
     */
    public function __construct(
        QueuePublisher $publisher,
        Serializer $serializer
    ) {
        $this->publisher = $publisher;
        $this->serializer = $serializer;
    }

    /**
     * Publish a message on the queue
     *
     * @param mixed    $data
     * @param string   $routingKey
     * @param string[] $headers
     *
     * @return mixed
     */
    public function publish($data, $routingKey = '', array $headers = [])
    {
        $returnValue = $this->publisher->publish(
            $this->serializer->serialize($data),
            $routingKey,
            $headers
        );

        if ($returnValue === null) {
            return null;
        }

        return $this->serializer->deserialize($returnValue);
    }
}
