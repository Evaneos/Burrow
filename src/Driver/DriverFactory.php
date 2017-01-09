<?php

namespace Burrow\Driver;

use Burrow\Driver;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;

class DriverFactory
{
    /**
     * Return the driver for your system
     *
     * It accepts a PECL connection, a PhpAmqpLib connection or an array following
     * the following schema :
     * [
     *     'host' => '<hostValue>',
     *     'port' => '<portValue>',
     *     'user' => '<userValue>',
     *     'pwd'  => '<pwdValue>'
     * ]
     *
     * If you provide an array, the PECL extension has precedence over the PhpAmqpLib.
     *
     * @param $connection
     *
     * @return Driver
     */
    public static function getDriver($connection)
    {
        if (is_array($connection) &&
            isset($connection['host']) &&
            isset($connection['port']) &&
            isset($connection['user']) &&
            isset($connection['pwd'])
        ) {
            $connection = self::getConnectionFromArray($connection);
        }

        if ($connection instanceof AbstractConnection) {
            return new PhpAmqpLibDriver($connection);
        }

        if ($connection instanceof \AMQPConnection) {
            return new PeclAmqpDriver($connection);
        }

        throw new \InvalidArgumentException('You provided an unsupported connection');
    }

    /**
     * @param $connection
     *
     * @return \AMQPConnection | AMQPLazyConnection
     */
    private static function getConnectionFromArray($connection)
    {
        if (static::peclExtensionIsAvailable()) {
            return static::getPeclConnection($connection);
        }

        return static::getPhpAmqpLibConnection($connection);
    }

    /**
     * @param $connection
     *
     * @return \AMQPConnection
     */
    private static function getPeclConnection($connection)
    {
        $amqpConnection = new \AMQPConnection();
        $amqpConnection->setHost($connection['host']);
        $amqpConnection->setPort($connection['port']);
        $amqpConnection->setLogin($connection['user']);
        $amqpConnection->setPassword($connection['pwd']);

        return $amqpConnection;
    }

    /**
     * @param $connection
     *
     * @return AMQPLazyConnection
     */
    private static function getPhpAmqpLibConnection($connection)
    {
        return new AMQPLazyConnection(
            $connection['host'],
            $connection['port'],
            $connection['user'],
            $connection['pwd']
        );
    }

    /**
     * @return bool
     */
    protected static function peclExtensionIsAvailable()
    {
        return class_exists(\AMQPConnection::class);
    }
}
