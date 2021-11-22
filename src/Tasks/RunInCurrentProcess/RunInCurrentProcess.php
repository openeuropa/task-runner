<?php

namespace OpenEuropa\TaskRunner\Tasks\RunInCurrentProcess;

use Consolidation\Config\ConfigInterface;
use Robo\Common\CommandArguments;
use Robo\Result;
use Robo\Robo;
use Robo\Runner as RoboRunner;
use Robo\Task\BaseTask;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Run a configurable command in current process.
 */
class RunInCurrentProcess extends BaseTask
{

    use CommandArguments;

    /**
     * @var string
     */
    protected $command;

    /**
     * @var bool
     */
    protected $capture = false;

    /**
     * @param string $command
     */
    public function __construct(string $command)
    {
        $this->command = $command;
    }

    /**
     * @return bool
     */
    public function getCapture()
    {
        return $this->capture;
    }

    /**
     * Capture and return output.
     *
     * @param bool $capture
     */
    public function setCapture(bool $capture)
    {
        $this->capture = $capture;
    }

    public function run()
    {
        // Backup config, as command may change it.
        $container = Robo::getContainer();
        $config = $container->get('config');
        assert($config instanceof ConfigInterface);
        $configExport = $config->export();

        // Assemble and run command.
        $line = trim($this->command . $this->arguments);
        $input = new StringInput($line);
        $runner = new RoboRunner();
        $runner->setContainer($container);
        $output = $this->capture ? new BufferedOutput() : Robo::output();
        $exitCode = $runner->run($input, $output);

        // Restore config.
        $config->replace($configExport);

        // Get captured output if requested.
        $message = $output instanceof BufferedOutput ? $output->fetch() : '';
        return new Result($this, $exitCode, $message);
    }

}
