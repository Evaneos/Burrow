<?php

namespace Burrow;

use Assert\Assertion;

class Message
{
    /** @var string */
    private $body;

    /** @var string */
    private $routingKey;

    /** @var string[] */
    private $headers;

    /** @var string */
    private $correlationId;

    /** @var string */
    private $replyTo;

    /** @var string */
    private $queue;

    /** @var string */
    private $deliveryTag;

    /**
     * Message constructor.
     *
     * @param string    $body
     * @param string    $routingKey
     * @param \string[] $headers
     * @param string    $correlationId
     * @param string    $replyTo
     */
    public function __construct($body, $routingKey = '', array $headers = [], $correlationId = '', $replyTo = '')
    {
        Assertion::string($body);
        Assertion::String($routingKey);
        Assertion::String($correlationId);
        Assertion::String($replyTo);

        $this->checkHeaders($headers);

        $this->body = $body;
        $this->routingKey = $routingKey;
        $this->headers = $headers;
        $this->correlationId = $correlationId;
        $this->replyTo = $replyTo;
    }

    /**
     * @param string $deliveryTag
     */
    public function setDeliveryTag($deliveryTag)
    {
        $this->deliveryTag = $deliveryTag;
    }

    /**
     * @param string $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getCorrelationId()
    {
        return $this->correlationId;
    }

    /**
     * @return string
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @return string
     */
    public function getDeliveryTag()
    {
        return $this->deliveryTag;
    }

    /**
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param string[] $headers
     */
    private function checkHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            Assertion::string($key, 'Header key must be a string');
            Assertion::notBlank($key, 'Header key must be a non empty string');
            Assertion::notNull($value, 'Value cannot be null');

            if (!is_string($value) &&
                !is_numeric($value) &&
                !is_bool($value)
            ) {
                throw new \InvalidArgumentException('Value must be a string, a number or a boolean.');
            }
        }
    }
}
