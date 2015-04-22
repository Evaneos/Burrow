<?php
namespace Burrow;

interface QueueConsumer
{
    /**
     * Consumes a message
     * 
     * @param  mixed $message
     * @return mixed|null|void
     */
    public function consume($message);
}