<?php

namespace Burrow;

interface Serializer
{
    /**
     * @param mixed $message
     *
     * @return string
     */
    public function serialize($message);

    /**
     * @param string $message
     *
     * @return mixed
     */
    public function deserialize($message);
}
