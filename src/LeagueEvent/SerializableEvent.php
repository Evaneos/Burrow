<?php
namespace Burrow\LeagueEvent;

interface SerializableEvent
{
    const SERIALIZE_KEY_TYPE = 'type';
    const SERIALIZE_KEY_PAYLOAD = 'payload';

    /**
     * @return array
     */
    public function toArray();
}