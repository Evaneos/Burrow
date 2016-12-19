<?php

namespace Burrow\CLI;

use Assert\Assertion;
use Burrow\RabbitMQ\AmqpAdministrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteExchangeCommand extends Command
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
        $this->setName('admin:delete:exchange')
            ->setDescription('Deletes an exchange in RabbitMQ.')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the exchange to delete.'
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

        $this->burrowAdministrator->deleteExchange($name);
        $output->writeln(sprintf('<info>Delete exchange <comment>%s</comment></info>', $name));
    }
}
