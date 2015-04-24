<?php
namespace Burrow\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
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
     * @var int
     */
    private $timeout;

    /**
     * Constructor
     *
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $pass
     * @param string $exchangeName
     * @param string $escapeMode
     * @param int    $timeout      timeout of the wait loop in seconds (default to 1)
     */
    public function __construct($host, $port, $user, $pass, $exchangeName, $escapeMode = self::ESCAPE_MODE_SERIALIZE, $timeout = 1)
    {
        parent::__construct($host, $port, $user, $pass, $exchangeName, $escapeMode);
        $this->timeout = $timeout;

        $self = $this;
        list($this->callbackQueue, ,) = $this->channel->queue_declare('', false, false, true, false);
        $this->channel->basic_consume(
            $this->callbackQueue, '', false, false, false, false, function (AMQPMessage $message) use ($self) {
                if($message->get('correlation_id') == $self->correlationId) {
                    $self->response = $this->unescape($message->body);
                }
            }
        );
    }

    /**
     * Publish a message on the queue
     *
     * @param  string $data
     * @param  string $routingKey
     * @return mixed|null|void
     */
    public function publish($data, $routingKey = '')
    {
        $this->response = null;
        $this->correlationId = uniqid();

        parent::publish($data, $routingKey);

        $this->waitForResponse();

        return $this->response;
    }

    /**
     * wait for response
     *
     * @return void
     */
    private function waitForResponse()
    {
        $start = microtime(true);
        $msTimeout = $this->timeout * 1000;
        $elapsedTime = 0;

        while(!$this->response && $elapsedTime < $msTimeout) {
            $waitTimeout = ceil(($msTimeout - $elapsedTime) / 1000);
            $this->channel->wait(null, false, $waitTimeout);
            $elapsedTime = microtime(true) - $start;
        }

        if ($elapsedTime > $msTimeout)
        {
            throw new AMQPTimeoutException('Timeout expired');
        }
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
