<?php

namespace Burrow\CLI;

use Assert\Assertion;
use Burrow\Driver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
class InitCommand extends Command
{
    /** @var Driver */
    private $driver;

    /**
     * DeclareQueueCommand constructor.
     *
     * @param Driver $driver
     */
    public function __construct(Driver $driver)
    {
        parent::__construct();

        $this->driver = $driver;
    }

    protected function configure()
    {
        $this->setName('admin:init')
            ->setDescription('Init a RabbitMQ exchange / queue wiring.')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'The path of the file to load wiring configuration from. It must contain a json declaration.'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getConfiguration($input);
        $this->declareQueues($configuration, $output);
        $this->bind($configuration, $output);
    }

    /**
     * @param InputInterface $input
     *
     * @return array
     */
    protected function getConfiguration(InputInterface $input)
    {
        $file = $input->getArgument('file');
        Assertion::file($file, 'You must provide a valid file name');

        $configurationString = file_get_contents($file);
        Assertion::isJsonString($configurationString, 'The file must be a valid JSON');

        $configuration = @json_decode($configurationString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid json : ' . json_last_error_msg());
        }

        self::checkConfiguration($configuration);

        return $configuration;
    }

    /**
     * @param array $configuration
     */
    private static function checkConfiguration(array $configuration)
    {
        self::checkQueuesConfiguration($configuration);
        self::checkExchangesConfiguration($configuration);
    }

    /**
     * @param array $configuration
     */
    private static function checkQueuesConfiguration(array $configuration)
    {
        Assertion::keyIsset($configuration, 'queues', 'You must provide a `queues` configuration');

        $queues = $configuration['queues'];
        Assertion::isArray($queues, 'The `queues` configuration must be an array');
    }

    /**
     * @param array $configuration
     */
    private static function checkExchangesConfiguration(array $configuration)
    {
        Assertion::keyIsset($configuration, 'exchanges', 'You must provide an `exchanges` configuration');

        $exchanges = $configuration['exchanges'];
        Assertion::isArray($exchanges, 'The `exchanges` configuration must be an array');

        foreach ($exchanges as $exchangeInformation) {
            Assertion::keyIsset($exchangeInformation, 'name', 'You must provide a name for the exchange');
            Assertion::keyIsset($exchangeInformation, 'type', 'You must provide a type for the exchange');

            $queues = $exchangeInformation['queues'];
            Assertion::keyIsset(
                $exchangeInformation,
                'queues',
                'You must provide a `queues` configuration for the exchange'
            );
            Assertion::isArray($queues, 'The `queues` configuration must be an array');

            foreach ($queues as $queueInformation) {
                Assertion::keyIsset($queueInformation, 'name', 'You must provide a name for the queue');
            }
        }
    }

    /**
     * @param array           $configuration
     * @param OutputInterface $output
     */
    protected function declareQueues($configuration, OutputInterface $output)
    {
        $queues = $configuration['queues'];
        foreach ($queues as $queue) {
            $this->driver->declareSimpleQueue($queue);
            $output->writeln(sprintf('<info>Declare queue <comment>%s</comment></info>', $queue));
        }
    }

    /**
     * @param array           $configuration
     * @param OutputInterface $output
     */
    protected function bind($configuration, OutputInterface $output)
    {
        $exchanges = $configuration['exchanges'];
        foreach ($exchanges as $exchangeInformation) {
            $exchangeName = $exchangeInformation['name'];
            $exchangeType = $exchangeInformation['type'];

            $this->driver->declareExchange($exchangeName, $exchangeType);

            $output->writeln(
                sprintf(
                    '<info>Declare exchange <comment>%s</comment> [<comment>%s</comment>]</info>',
                    $exchangeName,
                    $exchangeType
                )
            );

            $queues = $exchangeInformation['queues'];
            foreach ($queues as $queueInformation) {
                $queueName = $queueInformation['name'];
                $routingKey = isset($queueInformation['routingKey']) ? $queueInformation['routingKey'] : '';

                $this->driver->declareAndBindQueue($exchangeName, $queueName, $routingKey);

                $output->writeln(sprintf(
                    '<info>Bind exchange <comment>%s</comment> to queue ' .
                    '<comment>%s</comment> [<comment>%s</comment>]</info>',
                    $exchangeName,
                    $queueName,
                    $routingKey
                ));
            }
        }
    }
}
