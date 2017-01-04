<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueuePublisher;
use PhpAmqpLib\Wire\AMQPTable;

class AbstractAmqpPublisher extends AmqpTemplate implements QueuePublisher
{
    /**
     * @var string
     */
    protected $exchangeName;

    /**
     * Constructor
     *
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $pass
     * @param string $exchangeName
     * @param string $escapeMode
     */
    public function __construct($host, $port, $user, $pass, $exchangeName, $escapeMode = self::ESCAPE_MODE_SERIALIZE)
    {
        parent::__construct($host, $port, $user, $pass, $escapeMode);
        $this->exchangeName = $exchangeName;
    }

    /**
     * Publish a message on the queue
     *
     * @param string   $data
     * @param string   $routingKey
     * @param string[] $headers
     *
     * @return mixed|null|void
     */
    public function publish($data, $routingKey = '', array $headers = [])
    {
        $this->getChannel()->basic_publish(
            new AMQPMessage($this->escape($data), $this->getMessageProperties($headers)),
            $this->exchangeName,
            $routingKey
        );
    }

    /**
     * Returns the message parameters
     *
     * @param string[] $headers
     *
     * @return array
     */
    protected function getMessageProperties(array $headers = [])
    {
        return [
            'delivery_mode' => 2,
            'application_headers' => new AMQPTable($headers)
        ];
    }
}
