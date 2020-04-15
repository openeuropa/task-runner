<?php

namespace OpenEuropa\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Robo\Robo;

/**
 * Class DynamicCommands
 *
 * @package OpenEuropa\TaskRunner\Commands
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
