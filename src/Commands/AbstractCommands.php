<?php

namespace EC\OpenEuropa\TaskRunner\Commands;

use Robo\Common\ConfigAwareTrait;
use Robo\Common\IO;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Contract\BuilderAwareInterface;
use Robo\Exception\TaskException;
use Robo\LoadAllTasks;
use Robo\Robo;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Class AbstractCommands
 *
 * @package EC\OpenEuropa\TaskRunner\Commands
 */
abstract class AbstractCommands implements BuilderAwareInterface, IOAwareInterface, ConfigAwareInterface
{
    use ConfigAwareTrait;
    use LoadAllTasks;
    use IO;

    /**
     * Path to YAML configuration file containing command defaults.
     *
     * Command classes should implement this method.
     *
     * @return string
     */
    public function getConfigurationFile()
    {
        return __DIR__.'/../../config/commands/base.yml';
    }

    /**
     * Command initialization.
     *
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     *
     * @hook pre-command-event *
     */
    public function initializeRuntimeConfiguration(ConsoleCommandEvent $event)
    {
        Robo::loadConfiguration([$this->getConfigurationFile()], $this->getConfig());
    }

    /**
     * @param  string $name
     * @return string
     *
     * @throws TaskException
     */
    protected function getBin($name)
    {
        $filename = $this->getConfig()->get('runner.bin_dir').'/'.$name;
        if (!file_exists($filename) && !$this->isSimulating()) {
            throw new TaskException($this, "Executable '{$filename}' not found.");
        }

        return $filename;
    }

    /**
     * @return bool
     */
    protected function isSimulating()
    {
        return (bool) $this->input()->getOption('simulate');
    }
}
