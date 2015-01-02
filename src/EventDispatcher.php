<?php

namespace Burrow;

interface EventDispatcher
{
    /**
     * @param Event $event
     *
     * @return void
     */
    public function dispatch(Event $event);

    /**
     * Register to an Event.
     *
     * @param string   $eventCategory
     * @param callable $callback
     *
     * @return void
     */
    public function on($eventCategory, callable $callback);
}
