<?php

namespace Burrow\CLI;

use Assert\Assertion;
use Burrow\RabbitMQ\AmqpAdministrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeclareExchangeCommand extends Command
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
                '"' . AmqpAdministrator::DIRECT . '", ' .
                '"' . AmqpAdministrator::TOPIC . '", ' .
                '"' . AmqpAdministrator::FANOUT . '", ' .
                '"' . AmqpAdministrator::HEADERS . '".',
                AmqpAdministrator::FANOUT
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
        $name = $input->getArgument('name');
        $type = $input->getArgument('type');
        Assertion::choice(
            $type,
            [
                AmqpAdministrator::DIRECT,
                AmqpAdministrator::TOPIC,
                AmqpAdministrator::FANOUT,
                AmqpAdministrator::HEADERS
            ]
        );

        $this->burrowAdministrator->declareExchange($name, $type);
        $output->writeln(
            sprintf(
                '<info>Declare exchange <comment>%s</comment> [<comment>%s</comment>]</info>',
                $name,
                $type
            )
        );
    }
}
