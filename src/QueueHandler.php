<?php

namespace Burrow;

interface QueueHandler
{
    const STOP_CONSUMING = false;
    const CONTINUE_CONSUMING = true;

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
