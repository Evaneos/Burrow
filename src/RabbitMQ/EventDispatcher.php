<?php

namespace Burrow\RabbitMQ;

use Burrow\Event;
use Burrow\EventDispatcher as EventDispatcherInterface;

class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var AmqpService
     */
    protected $amqpService;

    /**
     * @var array
     */
    protected $listeners;

    /**
     * @param AmqpService $amqpService
     */
    public function __construct(AmqpService $amqpService)
    {
        $this->amqpService = $amqpService;
    }

    /**
     * @param Event $event
     *
     * @return void
     */
    public function dispatch(Event $event)
    {
        $this->amqpService->publish($event->getCategory(), $event);
    }

    /**
     * Register to an Event.
     *
     * @param string   $eventCategory
     * @param callable $callback
     *
     * @return void
     */
    public function on($eventCategory, callable $callback)
    {
        $this->listeners[$eventCategory][] = $callback;

        $me = $this;

        $this->amqpService->registerConsumer($eventCategory, function ($data) use ($eventCategory, $me) {
            $event = new Event($eventCategory, $data);

            foreach ($me->listeners[$eventCategory] as $callback) {
                call_user_func($callback, $event);
            }
        });
    }
}
