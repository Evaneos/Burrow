<?php
namespace Burrow;

interface QueuePublisher
{
    /**
     * Publish a message on the queue
     *
     * @param string   $data
     * @param string   $routingKey
     * @param string[] $headers
     *
     * @return null|string|void
     */
    public function publish($data, $routingKey = "", array $headers = []);
}
