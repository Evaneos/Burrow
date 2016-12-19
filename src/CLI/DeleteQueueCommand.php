<?php

namespace Burrow\CLI;

use Burrow\RabbitMQ\AmqpAdministrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteQueueCommand extends Command
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
        $this->setName('admin:delete:queue')
            ->setDescription('Deletes a durable queue in RabbitMQ.')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the queue to delete.'
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
        $this->burrowAdministrator->deleteQueue($queue);
        $output->writeln(sprintf('<info>Delete queue <comment>%s</comment></info>', $queue));
    }
}
