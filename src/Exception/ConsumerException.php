<?php

namespace Burrow\Exception;

class ConsumerException extends BurrowException
{
    /**
     * Build the exception from another exception.
     *
     * @param \Exception $e
     *
     * @return ConsumerException
     */
    public static function build(\Exception $e)
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }

    /**
     * @return ConsumerException
     */
    public static function invalidSyncMessage()
    {
        return new self('Invalid sync message.');
    }
}
