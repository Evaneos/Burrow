<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Burrow\QueuePublisher;

class AmqpSyncPublisher extends AbstractAmqpPublisher implements QueuePublisher
{
    /**
     * @var string
     */
    private $callbackQueue;

    /**
     * @var string
     */
    private $correlationId;

    /**
     * @var string
     */
    private $response;

    /**
     * Constructor
     *
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $pass
     * @param string $exchangeName
     */
    public function __construct($host, $port, $user, $pass, $exchangeName)
    {
        parent::__construct($host, $port, $user, $pass, $exchangeName);

        $self = $this;
        list($this->callbackQueue, ,) = $this->channel->queue_declare('', false, false, true, false);
        $this->channel->basic_consume(
            $this->callbackQueue, '', false, false, false, false, function (AMQPMessage $message) use ($self) {
                if($message->get('correlation_id') == $this->correlationId) {
                    $this->response = unserialize($message->body);
                }
            }
        );
    }

    /**
     * Publish a message on the queue
     *
     * @param string $data
     * @param string $routingKey
     *
     * @return mixed|null|void
     */
    public function publish($data, $routingKey = '')
    {
        $this->response = null;
        $this->correlationId = uniqid();

        parent::publish($data, $routingKey);

        while(!$this->response) {
            $this->channel->wait();
        }
        return $this->response;
    }

    /**
     * Returns the message parameters
     *
     * @return array
     */
    protected function getMessageProperties()
    {
        $properties = parent::getMessageProperties();
        $properties['correlation_id'] = $this->correlationId;
        $properties['reply_to'] = $this->callbackQueue;
        return $properties;
    }
}
