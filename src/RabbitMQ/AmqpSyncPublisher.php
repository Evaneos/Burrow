<?php
namespace Burrow\RabbitMQ;

use Burrow\QueuePublisher;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;

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
     * @var mixed
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
    public function __construct(
        $host,
        $port,
        $user,
        $pass,
        $exchangeName,
        $escapeMode = self::ESCAPE_MODE_SERIALIZE,
        $timeout = 1
    ) {
        parent::__construct($host, $port, $user, $pass, $exchangeName, $escapeMode);
        $this->timeout = $timeout;

        $self = $this;
        list($this->callbackQueue, , ) = $this->getChannel()->queue_declare('', false, false, true, false);
        $this->getChannel()->basic_consume(
            $this->callbackQueue,
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($self) {
                if ($message->get('correlation_id') == $self->getCorrelationId()) {
                    $self->setResponse($this->unescape($message->body));
                }
            }
        );
    }

    /**
     * Publish a message on the queue
     *
     * @param string   $data
     * @param string   $routingKey
     * @param string[] $headers
     *
     * @return mixed
     */
    public function publish($data, $routingKey = '', array $headers = [])
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

        while (!$this->response && $elapsedTime < $msTimeout) {
            $waitTimeout = ceil(($msTimeout - $elapsedTime) / 1000);
            $this->getChannel()->wait(null, false, $waitTimeout);
            $elapsedTime = microtime(true) - $start;
        }

        if ($elapsedTime > $msTimeout) {
            throw new AMQPTimeoutException('Timeout expired');
        }
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
        $properties = parent::getMessageProperties($headers);
        $properties['correlation_id'] = $this->correlationId;
        $properties['reply_to'] = $this->callbackQueue;
        return $properties;
    }

    /**
     * @return string
     */
    public function getCorrelationId()
    {
        return $this->correlationId;
    }

    /**
     * @param string $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}
