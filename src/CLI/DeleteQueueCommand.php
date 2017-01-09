<?php

namespace Burrow\CLI;

use Burrow\Driver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
class DeleteQueueCommand extends Command
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
        $this->driver->deleteQueue($queue);
        $output->writeln(sprintf('<info>Delete queue <comment>%s</comment></info>', $queue));
    }
}
