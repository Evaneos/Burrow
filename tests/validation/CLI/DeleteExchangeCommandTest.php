<?php

namespace Burrow\Test\Validation\CLI;

use Faker\Factory;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class DeleteExchangeCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $exchangeName;

    /** @var string */
    private $workingDirectory;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->exchangeName = $faker->word;
        $this->workingDirectory = dirname(dirname(dirname(__DIR__)));

        $this->getBurrowProcess('admin:declare:exchange', $this->exchangeName)->run();
    }

    /**
     * Close
     */
    public function tearDown()
    {
        \Mockery::close();
        $this->getBurrowProcess('admin:delete:exchange', $this->exchangeName)->run();
    }

    /**
     * @test
     */
    public function itShouldDeclareAnExchange()
    {
        $process = $this->getBurrowProcess('admin:delete:exchange', $this->exchangeName);
        $process->run();

        $this->assertTrue($process->isSuccessful());
        // TODO assert exchange has been deleted
    }

    /**
     * @test
     */
    public function itShouldFailDeclaringExchangeIfNotProvidingAnExchangeName()
    {
        $process = $this->getBurrowProcess('admin:delete:exchange');
        $process->run();

        $this->assertFalse($process->isSuccessful());
        // TODO assert exchange still exists
    }

    /**
     * @param string $command
     * @param string $exchange
     *
     * @return Process
     */
    protected function getBurrowProcess($command, $exchange = null)
    {
        $params = ['php', 'bin/burrow', $command];
        if ($exchange != null) {
            $params[] = $exchange;
        }
        $builder = new ProcessBuilder($params);
        $process = $builder->getProcess();
        $process->setWorkingDirectory($this->workingDirectory);

        return $process;
    }
}
