<?php

namespace Burrow\CLI;

use Assert\Assertion;
use Burrow\RabbitMQ\AmqpAdministrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    /** @var AmqpAdministrator */
    private $burrowAdministrator;

    /**
     * DeclareQueueCommand constructor.
     *
     * @param AmqpAdministrator $burrowAdministrator
     */
    public function __construct(AmqpAdministrator $burrowAdministrator)
    {
        parent::__construct();

        $this->burrowAdministrator = $burrowAdministrator;
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
        Assertion::file($file);

        $configurationString = file_get_contents($file);
        Assertion::isJsonString($configurationString);

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
        Assertion::keyIsset($configuration, 'queues');

        $queues = $configuration['queues'];
        Assertion::isArray($queues);
    }

    /**
     * @param array $configuration
     */
    private static function checkExchangesConfiguration(array $configuration)
    {
        Assertion::keyIsset($configuration, 'exchanges');

        $exchanges = $configuration['exchanges'];
        Assertion::isArray($exchanges);

        foreach ($exchanges as $exchangeInformation) {
            Assertion::keyIsset($exchangeInformation, 'name');
            Assertion::keyIsset($exchangeInformation, 'type');

            $queues = $exchangeInformation['queues'];
            Assertion::isArray($queues);

            foreach ($queues as $queueInformation) {
                Assertion::keyIsset($queueInformation, 'name');
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
            $this->burrowAdministrator->declareSimpleQueue($queue);
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

            $this->burrowAdministrator->declareExchange($exchangeName, $exchangeType);

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

                $this->burrowAdministrator->declareAndBindQueue($exchangeName, $queueName, $routingKey);

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
