<?php

namespace Burrow\Serializer;

use Burrow\Serializer;

class PhpSerializer implements Serializer
{
    /**
     * @param mixed $message
     *
     * @return string
     */
    public function serialize($message)
    {
        return serialize($message);
    }

    /**
     * @param string $message
     *
     * @return mixed
     */
    public function deserialize($message)
    {
        return unserialize($message);
    }
}
