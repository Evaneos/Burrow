<?php

namespace Burrow\Serializer;

use Assert\Assertion;
use Burrow\Serializer;

class JsonSerializer implements Serializer
{
    /**
     * @param mixed $message
     *
     * @return string
     */
    public function serialize($message)
    {
        return json_encode($message);
    }

    /**
     * @param string $message
     *
     * @return mixed
     */
    public function deserialize($message)
    {
        if ($message === null) {
            return null;
        }

        Assertion::string($message, 'The message to deserialize must be a valid string');

        return json_decode($message);
    }
}
