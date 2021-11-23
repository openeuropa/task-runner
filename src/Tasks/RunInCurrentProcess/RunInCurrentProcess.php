<?php

namespace OpenEuropa\TaskRunner\Tasks\RunInCurrentProcess;

use OpenEuropa\TaskRunner\TaskRunner;
use Robo\Common\CommandArguments;
use Robo\Result;
use Robo\Robo;
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
     * @var bool
     */
    protected $inheritConfig = false;

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

    /**
     * @return bool
     */
    public function getInheritConfig()
    {
        return $this->inheritConfig;
    }

    /**
     * @param bool $inheritConfig
     */
    public function setInheritConfig(bool $inheritConfig)
    {
        $this->inheritConfig = $inheritConfig;
    }

    public function run()
    {
        // Assemble command and input.
        $line = trim($this->command . $this->arguments);
        $input = new StringInput($line);

        // Backup the container, robo has a global for it :-/.
        $backupOutput = Robo::output();
        $backupConfig = Robo::config();
        $backupContainer = Robo::getContainer();
        $classLoader = $backupContainer->get('classLoader');
        Robo::unsetContainer();

        if ($this->inheritConfig) {
            Robo::config()->replace($backupConfig->export());
        }

        // Run command.
        $output = $this->capture ? new BufferedOutput() : $backupOutput;
        $taskRunner = new TaskRunner($input, $output, $classLoader);
        $exitCode = $taskRunner->run();

        // Restore the container.
        Robo::setContainer($backupContainer);

        // Get captured output if requested.
        $message = $output instanceof BufferedOutput ? $output->fetch() : '';
        return new Result($this, $exitCode, $message);
    }

}
