<?php
namespace Burrow;

interface QueuePublisher
{
    /**
     * Publish a message on the queue
     * 
     * @param  string $data
     * @param  string $routingKey
     * @return string|null|void
     */
    public function publish($data, $routingKey = "");
}
