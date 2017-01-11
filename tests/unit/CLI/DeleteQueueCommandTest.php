<?php

namespace Burrow\Test\CLI;

use Faker\Factory;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class DeleteQueueCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $queueName;

    /** @var string */
    private $workingDirectory;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->queueName = $faker->word;
        $this->workingDirectory = dirname(dirname(dirname(__DIR__)));

        $this->getBurrowProcess('admin:declare:queue', $this->queueName)->run();
    }

    /**
     * Close
     */
    public function tearDown()
    {
        \Mockery::close();
        $this->getBurrowProcess('admin:delete:queue', $this->queueName)->run();
    }

    /**
     * @test
     */
    public function itShouldDeclareAQueue()
    {
        $process = $this->getBurrowProcess('admin:delete:queue', $this->queueName);
        $process->run();

        $this->assertTrue($process->isSuccessful());
        // TODO assert queue has been deleted
    }

    /**
     * @test
     */
    public function itShouldFailDeclaringQueueIfNotProvidingAQueueName()
    {
        $process = $this->getBurrowProcess('admin:delete:queue');
        $process->run();

        $this->assertFalse($process->isSuccessful());
        // TODO assert queue still exists
    }

    /**
     * @param string $command
     * @param string $queue
     *
     * @return Process
     */
    protected function getBurrowProcess($command, $queue = null)
    {
        $params = ['php', 'bin/burrow', $command];
        if ($queue != null) {
            $params[] = $queue;
        }
        $builder = new ProcessBuilder($params);
        $process = $builder->getProcess();
        $process->setWorkingDirectory($this->workingDirectory);

        return $process;
    }
}
