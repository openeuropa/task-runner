<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Commands;

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
     * @return \OpenEuropa\TaskRunner\Tasks\CollectionFactory\CollectionFactory
     */
    public function runTasks()
    {
        $commandName = $this->input()->getArgument('command');
        /** @var \Consolidation\AnnotatedCommand\AnnotatedCommand $command */
        $command = Robo::application()->get($commandName);
        $tasks = $command->getAnnotationData()['tasks'];

        return $this->taskCollectionFactory($tasks);
    }
}
