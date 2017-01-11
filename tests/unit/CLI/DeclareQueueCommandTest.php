<?php

namespace Burrow\Test\CLI;

use Faker\Factory;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class DeclareQueueCommandTest extends \PHPUnit_Framework_TestCase
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
        $process = $this->getBurrowProcess('admin:declare:queue', $this->queueName);
        $process->run();

        $this->assertTrue($process->isSuccessful());
        // TODO assert queue exists
    }

    /**
     * @test
     */
    public function itShouldFailDeclaringQueueIfNotProvidingAQueueName()
    {
        $process = $this->getBurrowProcess('admin:declare:queue');
        $process->run();

        $this->assertFalse($process->isSuccessful());
        // TODO assert queue doesn't exist
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
