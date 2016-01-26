<?php
namespace Burrow\LeagueEvent;

use League\Event\EventInterface;

interface EventDeserializer
{
    /**
     * @param string $message
     * @return string | EventInterface
     */
    public function deserialize($message);
}