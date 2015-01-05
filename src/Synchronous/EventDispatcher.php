<?php

namespace Burrow\Synchronous;

use Burrow\Event;
use Burrow\EventDispatcher as EventDispatcherInterface;

class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array
     */
    protected $listeners;

    /**
     * @inheritdoc
     */
    public function dispatch(Event $event)
    {
        if (isset($this->listeners[$event->getCategory()])) {
            foreach ($this->listeners[$event->getCategory()] as $callback) {
                call_user_func($callback, $event);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function on($eventCategory, callable $callback)
    {
        $this->listeners[$eventCategory][] = $callback;
    }
}
