<?php

namespace Burrow\Consumer;

use Burrow\QueueConsumer;
use Burrow\Serializer;

class SerializingConsumer implements QueueConsumer
{
    /** @var QueueConsumer */
    private $consumer;

    /** @var Serializer */
    private $serializer;

    /**
     * SerializingConsumer constructor.
     *
     * @param QueueConsumer $consumer
     * @param Serializer $serializer
     */
    public function __construct(QueueConsumer $consumer, Serializer $serializer)
    {
        $this->consumer = $consumer;
        $this->serializer = $serializer;
    }

    /**
     * Consumes a message.
     *
     * @param mixed $message
     * @param string[] $headers
     *
     * @return string
     */
    public function consume($message, array $headers = [])
    {
        return $this->serializer->serialize(
            $this->consumer->consume(
                $this->serializer->deserialize($message),
                $headers
            )
        );
    }
}
