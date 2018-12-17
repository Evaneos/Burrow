<?php

namespace Burrow\Event\Listener;

use League\Event\EventInterface;

class ListenerException extends \Exception
{
    public static function badEventGiven(EventInterface $event)
    {
        return new self(sprintf('Bad event given %s', $event->getName()));
    }
}
