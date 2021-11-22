<?php

namespace OpenEuropa\TaskRunner\Tasks\RunInCurrentProcess;

use Consolidation\Config\ConfigInterface;
use Robo\Common\CommandArguments;
use Robo\Robo;
use Robo\Runner as RoboRunner;
use Robo\Task\BaseTask;
use Symfony\Component\Console\Input\StringInput;

/**
 * Run a configurable command in current process.
 */
class RunInCurrentProcess extends BaseTask {

  use CommandArguments;

  /**
   * @var string
   */
  protected $command;

  /**
   * @param string $command
   */
  public function __construct(string $command)
  {
    $this->command = $command;
  }

  public function run() {
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
    $statusCode = $runner->run($input, Robo::output());

    // Restore config.
    $config->replace($configExport);
    return $statusCode;
  }

}
