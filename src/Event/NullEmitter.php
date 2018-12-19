<?php

namespace Burrow\Event;

use League\Event\EmitterInterface;
use League\Event\EmitterTrait;
use League\Event\GeneratorInterface;

class NullEmitter implements EmitterInterface
{
    use EmitterTrait;

    public function hasListeners($event)
    {
        return false;
    }

    public function getListeners($event)
    {
        return [];
    }

    public function emitBatch(array $events)
    {
        return [];
    }

    public function emitGeneratedEvents(GeneratorInterface $generator)
    {
    }
}
