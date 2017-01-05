<?php

namespace Burrow\Exception;

class TimeoutException extends \RuntimeException
{
    /**
     * @param \Exception $e
     * @param int        $timeout
     *
     * @return TimeoutException
     */
    public static function build(\Exception $e, $timeout)
    {
        return new self(
            sprintf('The connection timed out after %d sec while awaiting incoming data', $timeout),
            $e->getCode(),
            $e
        );
    }
}
