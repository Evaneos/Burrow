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
     * @var string
     */
    protected $queueName;

    /**
     * @param AmqpService $amqpService
     * @param string      $queueName
     */
    public function __construct(AmqpService $amqpService, $queueName = '')
    {
        $this->amqpService = $amqpService;
        $this->queueName = $queueName;
    }

    /**
     * @inheritdoc
     */
    public function dispatch(Event $event)
    {
        $this->amqpService->publish($event->getCategory(), $event);
    }

    /**
     * @inheritdoc
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
        }, $this->queueName);
    }
}
