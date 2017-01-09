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

    /**
     * Modify and return the options for consumption.
     *
     * @param ConsumeOptions $options
     *
     * @return ConsumeOptions
     */
    public function options(ConsumeOptions $options);
}
