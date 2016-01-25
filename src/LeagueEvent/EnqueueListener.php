<?php
namespace Burrow\LeagueEvent;

use Burrow\QueuePublisher;
use League\Event\AbstractListener;
use League\Event\EventInterface;

final class EnqueueListener extends AbstractListener
{
    /**
     * @var QueuePublisher
     */
    private $publisher;

    /**
     * EnqueueListener constructor.
     * @param QueuePublisher $publisher
     */
    public function __construct(QueuePublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * Handle an event.
     *
     * @param EventInterface $event
     *
     * @return void
     */
    public function handle(EventInterface $event)
    {
        if(!$event instanceof SerializableEvent)
        {
            throw new \InvalidArgumentException(sprintf('Cannot serialize %s event.', $event->getName()));
        }

        $this->publisher->publish(json_encode($event->toArray()), $event->getName());
    }
}