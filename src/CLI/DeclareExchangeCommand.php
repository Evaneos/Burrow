<?php

namespace Burrow\CLI;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Burrow\Driver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
class DeclareExchangeCommand extends Command
{
    /** @var Driver */
    private $driver;

    /**
     * DeclareQueueCommand constructor.
     *
     * @param Driver $driver
     *
     * @throws LogicException
     */
    public function __construct(Driver $driver)
    {
        parent::__construct();

        $this->driver = $driver;
    }

    protected function configure()
    {
        $this->setName('admin:declare:exchange')
            ->setDescription('Declares an exchange in RabbitMQ.')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the exchange to declare.'
            )
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                'The type of the exchange. Can be any of ' .
                '"' . Driver::EXCHANGE_TYPE_DIRECT . '", ' .
                '"' . Driver::EXCHANGE_TYPE_TOPIC . '", ' .
                '"' . Driver::EXCHANGE_TYPE_FANOUT . '", ' .
                '"' . Driver::EXCHANGE_TYPE_HEADERS . '".',
                Driver::EXCHANGE_TYPE_FANOUT
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws InvalidArgumentException
     * @throws AssertionFailedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $type = $input->getArgument('type');
        Assertion::choice(
            $type,
            [
                Driver::EXCHANGE_TYPE_DIRECT,
                Driver::EXCHANGE_TYPE_TOPIC,
                Driver::EXCHANGE_TYPE_FANOUT,
                Driver::EXCHANGE_TYPE_HEADERS
            ],
            'The type of the exchange must be one of the four valid values.'
        );

        $this->driver->declareExchange($name, $type);
        $output->writeln(
            sprintf(
                '<info>Declare exchange <comment>%s</comment> [<comment>%s</comment>]</info>',
                $name,
                $type
            )
        );
    }
}
