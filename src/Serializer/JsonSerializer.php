<?php

namespace Burrow\Serializer;

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
        return json_decode($message);
    }
}
