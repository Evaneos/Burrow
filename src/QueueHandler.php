<?php

namespace Burrow;

interface QueueHandler
{
    /**
     * Handle a message.
     *
     * @param Message $message
     *
     * @return bool
     */
    public function handle(Message $message);
}
