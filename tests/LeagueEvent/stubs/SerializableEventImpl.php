<?php
namespace Burrow\tests\LeagueEvent\stubs;

use Burrow\LeagueEvent\SerializableEvent;
use League\Event\Event;

final class SerializableEventImpl extends Event implements SerializableEvent
{
    private $payload;

    /**
     * SerializableEvent constructor.
     * @param string $name
     * @param array $payload
     */
    public function __construct($name, array $payload = [])
    {
        parent::__construct($name);
        $this->payload = $payload;
    }

    public function toArray()
    {
        return [
            SerializableEventImpl::SERIALIZE_KEY_TYPE => $this->name,
            SerializableEventImpl::SERIALIZE_KEY_PAYLOAD => $this->payload
        ];
    }
}