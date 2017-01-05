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
    private $deliveryTag;

    /** @var string */
    private $correlationId;

    /** @var string */
    private $replyTo;

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
    public function getDeliveryTag()
    {
        return $this->deliveryTag;
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
}
