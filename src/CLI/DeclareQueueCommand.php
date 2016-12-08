<?php

namespace Burrow\CLI;

use Burrow\RabbitMQ\AmqpAdministrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeclareQueueCommand extends Command
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
        $this->setName('admin:declare:queue')
            ->setDescription('Declares a durable queue in RabbitMQ.')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the queue to declare.'
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
        $queue = $input->getArgument('name');
        $this->burrowAdministrator->declareSimpleQueue($queue);
        $output->writeln(sprintf('<info>Declare queue <comment>%s</comment></info>', $queue));
    }
}
