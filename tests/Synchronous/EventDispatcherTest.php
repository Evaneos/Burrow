<?php

class EventDispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Burrow\Synchronous\EventDispatcher
     */
    private $eventDispatcher;

    public function setUp()
    {
        $this->eventDispatcher = new \Burrow\Synchronous\EventDispatcher;
    }

    public function testEventDispatching()
    {
        $i = 0;
        $this->eventDispatcher->on('test', function(\Burrow\Event $event) use (&$i) {
            $i++;
        });

        $this->eventDispatcher->dispatch(new \Burrow\Event('test', 'data'));

        $this->assertEquals(1, $i);
    }

    public function testEventMultiDispatching()
    {
        $i = 0;
        $this->eventDispatcher->on('test', function(\Burrow\Event $event) use (&$i) {
            $i++;
        });

        $event = new \Burrow\Event('test', 'data');
        $this->eventDispatcher->dispatch($event);
        $this->eventDispatcher->dispatch($event);

        $this->assertEquals(2, $i);
    }

    public function testMultiListenersDispatching()
    {
        $i = 0;
        $this->eventDispatcher->on('test', function(\Burrow\Event $event) use (&$i) {
            $i++;
        });
        $this->eventDispatcher->on('test', function(\Burrow\Event $event) use (&$i) {
            $i++;
        });

        $this->eventDispatcher->dispatch(new \Burrow\Event('test', 'data'));

        $this->assertEquals(2, $i);
    }

    /**
     * @dataProvider eventDataProvider
     */
    public function testDispatchedEventIsIntact($eventData)
    {
        $originalEvent = new \Burrow\Event('test', $eventData);
        $dispatchedEvent = null;

        $this->eventDispatcher->on('test', function(\Burrow\Event $event) use (&$dispatchedEvent) {
            $dispatchedEvent = $event;
        });

        $this->eventDispatcher->dispatch($originalEvent);

        $this->assertEquals($originalEvent, $dispatchedEvent);
    }

    public function eventDataProvider()
    {
        return array(
            array(0),
            array('test'),
            array(array('test')),
            array(new \StdClass)
        );
    }
}
