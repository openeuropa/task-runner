<?php

namespace EC\OpenEuropa\TaskRunner\Commands;

use Consolidation\AnnotatedCommand\AnnotatedCommand;
use Consolidation\AnnotatedCommand\AnnotationData;
use Robo\Common\ConfigAwareTrait;
use Robo\Common\IO;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Contract\BuilderAwareInterface;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Exception\TaskException;
use Robo\LoadAllTasks;
use Robo\Result;
use Robo\Robo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class BaseCommands.
 *
 * @package EC\OpenEuropa\TaskRunner\Commands
 */
class BaseCommands implements BuilderAwareInterface, IOAwareInterface, ConfigAwareInterface
{
    use ConfigAwareTrait;
    use LoadAllTasks;
    use IO;

    /**
     * Path to YAML configuration file containing command defaults.
     *
     * Command classes should implement this method.
     *
     * @return null|string
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
        $workingDir = $event->getInput()->getOption('working-dir');

        Robo::loadConfiguration([
            $this->getConfigurationFile(),
            $workingDir.'/runner.yml.dist',
            $workingDir.'/runner.yml',
        ], $this->getConfig());
    }

    /**
     * @param  string $name
     * @return string
     *
     * @throws TaskException
     */
    protected function getBin($name)
    {
        $filename = $this->getConfig()->get('runner.bin-dir').'/'.$name;
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
