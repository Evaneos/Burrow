<?php

namespace Burrow\Driver;

use Burrow\Connection\PhpAmqpLib\AMQPLazySSLConnection;
use Burrow\Driver;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class DriverFactory
{
    /**
     * Return the driver for your system
     *
     * It accepts a PECL connection, a PhpAmqpLib connection or an array following
     * the following schema :
     * [
     *     'host'        => '<hostValue>',
     *     'port'        => '<portValue>',
     *     'user'        => '<userValue>',
     *     'pwd'         => '<pwdValue>',
     *     'ssl'         => true,                   // Optional: default to false
     *     'ssl_options' => [                       // Optional: default to [], see https://www.php.net/manual/context.ssl.php
     *         'capath'      => '/etc/ssl/certs',
     *         'verify_peer' => true,
     *     ],
     * ]
     *
     * SSL options is limited to PhpAmqpLib.
     * If you provide an array, the PECL extension has precedence over the PhpAmqpLib.
     *
     * @param $connection
     *
     * @return Driver
     *
     * @throws \AMQPConnectionException
     * @throws \InvalidArgumentException
     */
    public static function getDriver($connection)
    {
        if (is_array($connection) &&
            isset($connection['host'], $connection['port'], $connection['user'], $connection['pwd'])
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
     * @return mixed|\AMQPConnection|AMQPLazyConnection
     *
     * @throws \AMQPConnectionException
     */
    private static function getConnectionFromArray($connection)
    {
        if (static::peclExtensionIsAvailable()) {
            return static::getPeclConnection($connection);
        }

        return static::getPhpAmqpLibConnection($connection);
    }

    /**
     * @param string[] $connection
     *
     * @return \AMQPConnection
     *
     * @throws \AMQPConnectionException
     *
     * @codeCoverageIgnore
     */
    protected static function getPeclConnection(array $connection)
    {
        $amqpConnection = new \AMQPConnection();
        $amqpConnection->setHost($connection['host']);
        $amqpConnection->setPort($connection['port']);
        $amqpConnection->setLogin($connection['user']);
        $amqpConnection->setPassword($connection['pwd']);

        return $amqpConnection;
    }

    /**
     * @param string[] $connection
     *
     * @return AMQPStreamConnection
     *
     * @codeCoverageIgnore
     */
    protected static function getPhpAmqpLibConnection(array $connection)
    {
        if (isset($connection['ssl']) && $connection['ssl']) {
            return new AMQPLazySSLConnection(
                $connection['host'],
                $connection['port'],
                $connection['user'],
                $connection['pwd'],
                '/',
                isset($connection['ssl_options']) ? $connection['ssl_options'] : []
            );
        } else {
            return new AMQPLazyConnection(
                $connection['host'],
                $connection['port'],
                $connection['user'],
                $connection['pwd']
            );
        }
    }

    /**
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected static function peclExtensionIsAvailable()
    {
        return class_exists(\AMQPConnection::class);
    }
}
