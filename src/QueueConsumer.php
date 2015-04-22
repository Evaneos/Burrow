<?php
namespace Burrow;

interface QueueConsumer
{
    /**
     * Consumes a message
     * 
     * @param  string $message
     * @return string|null|void
     */
    public function consume($message);
}