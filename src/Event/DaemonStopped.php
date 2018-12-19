<?php

namespace Burrow\Event;

use League\Event\EmitterAwareTrait;
use League\Event\EventInterface;

class DaemonStopped implements EventInterface
{
    use EmitterAwareTrait;

    const NAME = 'daemon.stopped';

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    public function stopPropagation()
    {
        return false;
    }

    public function isPropagationStopped()
    {
        return false;
    }
}
