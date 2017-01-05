<?php

namespace Burrow;

interface QueuePublisher
{
    /**
     * Publish a message on the queue
     *
     * @param mixed    $data
     * @param string   $routingKey
     * @param string[] $headers
     *
     * @return mixed
     */
    public function publish($data, $routingKey = "", array $headers = []);
}
