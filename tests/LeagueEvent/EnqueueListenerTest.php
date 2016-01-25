<?php
namespace Burrow\tests\LeagueEvent;

use Burrow\LeagueEvent\EnqueueListener;
use Burrow\QueuePublisher;
use Burrow\tests\LeagueEvent\stubs\SerializableEventImpl;
use League\Event\Event;
use Mockery;

class EnqueueListenerTest extends \PHPUnit_Framework_TestCase
{
    private $listener;

    private $queuePublisher;

    protected function tearDown()
    {
        Mockery::close();
    }

    protected function setUp()
    {
        $this->queuePublisher = Mockery::mock(QueuePublisher::class);
        $this->listener = new EnqueueListener($this->queuePublisher);
    }

    /**
     * @test
     */
    public function it_publishes_the_event_in_the_QueuePublisher()
    {
        $event = new SerializableEventImpl('SomethingHappened', [
            'aData' => 'something',
            'anotherData' => 'somethingElse'
        ]);

        $this->queuePublisher->shouldReceive('publish')->with(json_encode([
            'type' => 'SomethingHappened',
            'payload' => [
                'aData' => 'something',
                'anotherData' => 'somethingElse'
            ]
        ]), 'SomethingHappened')->once();

        $this->listener->handle($event);
    }
    
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_an_InvalidArgumentException_if_event_is_not_an_ArraySerializable()
    {
        $this->listener->handle(new Event('NotASerializableEvent'));
    }
}
