<?php
namespace Burrow\tests\LeagueEvent;

use Burrow\LeagueEvent\EventDeserializer;
use Burrow\LeagueEvent\EventQueueConsumer;
use League\Event\EmitterInterface;
use Mockery;

class QueueConsumerTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function it_emit_events_coming_from_the_queue()
    {
        $emitter = Mockery::mock(EmitterInterface::class);
        $consumer = new EventQueueConsumer($emitter);

        $emitter->shouldReceive('emit')->with(['poney' => 'Eole'])->once();

        $consumer->consume(json_encode(['poney' => 'Eole']));
    }
    
    /**
     * @test
     */
    public function it_deserialize_the_message_before_emitting_it_if_a_deserializer_is_given()
    {
        $emitter = Mockery::mock(EmitterInterface::class);
        $deserializer = Mockery::mock(EventDeserializer::class);
        $consumer = new EventQueueConsumer($emitter, $deserializer);

        $deserializer->shouldReceive('deserialize')->with(['poney' => 'Eole'])->andReturn('deserializedEvent');
        $emitter->shouldReceive('emit')->with('deserializedEvent')->once();

        $consumer->consume(json_encode(['poney' => 'Eole']));
    }

}
