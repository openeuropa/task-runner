<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Commands;

use Consolidation\AnnotatedCommand\AnnotatedCommand;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Robo\Robo;

/**
 * Command class for dynamic commands.
 *
 * Dynamic commands are defined in YAML and have no dedicated command class.
 * A command is comprised of an array of tasks with their configuration.
 * See the section in the README on dynamic commands for more information.
 */
class DynamicCommands extends AbstractCommands
{
    use TaskRunnerTasks\CollectionFactory\loadTasks;

    /**
     * @dynamic-command true
     *
     * @return \OpenEuropa\TaskRunner\Tasks\CollectionFactory\CollectionFactory
     */
    public function runTasks()
    {
        $commandName = $this->input()->getArgument('command');
        /** @var \Consolidation\AnnotatedCommand\AnnotatedCommand $command */
        $command = Robo::application()->get($commandName);
        $tasks = $command->getAnnotationData()['tasks'];

        $inputOptions = [];
        foreach ($this->input()->getOptions() as $name => $value) {
            if ($this->input()->hasParameterOption("--$name")) {
                $inputOptions[$name] = $value;
            }
        }

        return $this->taskCollectionFactory($tasks, $inputOptions);
    }

    /**
     * Bind input values of custom command options to config entries.
     *
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     *
     * @hook pre-command-event *
     */
    public function bindInputOptionsToConfig(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        if (get_class($command) !== AnnotatedCommand::class && !is_subclass_of($command, AnnotatedCommand::class)) {
            return;
        }

        /** @var \Consolidation\AnnotatedCommand\AnnotatedCommand $command */
        /** @var \Consolidation\AnnotatedCommand\AnnotationData $annotatedData */
        $annotatedData = $command->getAnnotationData();
        if (!$annotatedData->get('dynamic-command')) {
            return;
        }

        // Dynamic commands may define their own options bound to specific configuration. Dynamically set the
        // configuration from command options.
        $config = $this->getConfig();
        $commands = $config->get('commands');
        if (!empty($commands[$command->getName()]['options'])) {
            foreach ($commands[$command->getName()]['options'] as $optionName => $option) {
                if (!empty($option['config']) && $event->getInput()->hasOption($optionName)) {
                    $inputValue = $event->getInput()->getOption($optionName);
                    if ($inputValue !== null) {
                        $config->set($option['config'], $event->getInput()->getOption($optionName));
                    }
                }
            }
        }
    }
}
