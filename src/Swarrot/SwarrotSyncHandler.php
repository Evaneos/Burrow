<?php
namespace Burrow\Swarrot;

use Burrow\QueueHandler;
use Burrow\QueueConsumer;
use Burrow\Daemonizable;
use Burrow\Swarrot\MessageProvider\RpcMessageProvider;
use Burrow\Swarrot\Processor\QueueConsumerProcessor;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Swarrot\Consumer;
use Swarrot\Processor\RPC\RpcServerProcessor;
use Swarrot\Processor\Stack\Builder;

class SwarrotSyncHandler implements QueueHandler, Daemonizable, LoggerAwareInterface
{
    /**
     * @var QueueConsumer
     */
    protected $consumer;

    /**
     * @var RpcMessageProvider
     */
    protected $messageProvider;

    /**
     * @var Consumer
     */
    protected $daemon;

    /**
     * @var Builder
     */
    protected $stack;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param RpcMessageProvider $messageProvider
     */
    public function __construct(RpcMessageProvider $messageProvider, LoggerInterface $logger = null)
    {
        $this->messageProvider = $messageProvider;
        $this->logger = $logger;
    }

    /**
     * Sets the consumer
     *
     * @param  QueueConsumer $consumer
     * @return void
     */
    public function registerConsumer(QueueConsumer $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * Run as a daemon
     *
     * @return void
     */
    public function daemonize()
    {
        if ($this->logger) {
            $this->logger->info('Starting AMqpAsyncHandler daemon...');
        }

        $processor = (new Builder())
            ->push('Burrow\Swarrot\Processor\ExceptionProcessor', $this->logger)
            ->push('Swarrot\Processor\Ack\AckProcessor', $this->messageProvider, $this->logger)
            ->resolve(
                new RpcServerProcessor(
                    new QueueConsumerProcessor($this->consumer),
                    $this->messageProvider->getQueuePublisher()
                )
            );

        $daemon = new Consumer($this->messageProvider, $processor);
        $daemon->consume(array('requeue_on_error' => true));
    }

    /**
     * Stop current connection / daemon
     *
     * @return void
     */
    public function shutdown()
    {
        if ($this->logger) {
            $this->logger->info('Closing AMqpAsyncHandler daemon...');
        }

        // TODO close something?
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
