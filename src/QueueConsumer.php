<?php
namespace Burrow;

interface QueueConsumer
{
    /**
     * Consumes a message
     * 
     * @param mixed $message
     */
    public function consume($message);
}