<?php

namespace Burrow\Test\Driver;

use Burrow\Driver\DriverFactory;
use PhpAmqpLib\Connection\AbstractConnection;

class TestableDriverFactory extends DriverFactory
{
    /** @var bool */
    public static $peclAvailable;

    /**
     * @param string[] $connection
     *
     * @return \Mockery\MockInterface
     */
    protected static function getPeclConnection(array $connection)
    {
        return \Mockery::mock(\AMQPConnection::class);
    }

    /**
     * @param string[] $connection
     *
     * @return \Mockery\MockInterface
     */
    protected static function getPhpAmqpLibConnection(array $connection)
    {
        return \Mockery::mock(AbstractConnection::class);
    }

    /**
     * @return bool
     */
    protected static function peclExtensionIsAvailable()
    {
        return static::$peclAvailable;
    }

}
