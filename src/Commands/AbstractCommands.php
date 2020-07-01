<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Commands;

use Consolidation\AnnotatedCommand\AnnotationData;
use Robo\Common\ConfigAwareTrait;
use Robo\Common\IO;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Exception\TaskException;
use Robo\LoadAllTasks;
use Robo\Robo;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Base class for commands.
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
        return __DIR__ . '/../../config/commands/base.yml';
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
     * Returns an array of valueless options and their corresponding config key.
     *
     * This provides the data for ::initializeValuelessOptions().
     *
     * @see \OpenEuropa\TaskRunner\Commands\AbstractCommands::initializeValuelessOptions()
     *
     * @return array
     *   An associative array, keyed by command name, with each value consisting
     *   of an associative array where the key is the valueless option name, and
     *   the value is the location where the option should be placed in the
     *   configuration array. Example:
     *   'drupal:my-command' => ['my-option' => 'drupal.site.my_option']
     */
    public function getValuelessConfigurationKeys()
    {
        return [];
    }

    /**
     * Initializes valueless options.
     *
     * Valueless options (i.e. options of type `InputOption::VALUE_NONE`) cannot
     * be defined in the usual way in a YAML configuration file such as
     * `drupal.yml` since the library which is responsible for value
     * substitution in config files only does straight string replacement. It
     * does not support the notion that the absence of a valueless option means
     * that it should be set to `FALSE`.
     *
     * This method uses the data from `::getValuelessConfigurationKeys()` to
     * look up whether the option is present in the specified location in the
     * configuration files, and will set the value to FALSE if the option is
     * missing. If the option is present it will use its actual value.
     *
     * If the valueless option is passed on the command line this will take
     * precedence over the value in the configuration files.
     *
     * @hook init *
     */
    public function initializeValuelessOptions(InputInterface $input, AnnotationData $annotationData)
    {
        $command_name = $annotationData->get('command');
        $keys = $this->getValuelessConfigurationKeys();
        if (!array_key_exists($command_name, $keys)) {
            return;
        }
        foreach ($keys[$command_name] as $option => $key) {
            // Check if the option was passed on the command line. This takes
            // precedence over the presence of the value in the configuration
            // file.
            if (!$input->getOption($option)) {
                // If not, take the corresponding value from the configuration.
                $value = (bool) $this->getConfig()->get($key);
                $input->setOption($option, $value);
            }
        }
    }

    /**
     * @param  string $name
     * @return string
     *
     * @throws TaskException
     */
    protected function getBin($name)
    {
        $filename = $this->getConfig()->get('runner.bin_dir') . '/' . $name;
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
