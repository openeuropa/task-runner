<?php

namespace OpenEuropa\TaskRunner\Robo\Plugin\Commands;

use OpenEuropa\TaskRunner\Robo\Plugin\AbstractCommands;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;

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
        $command = $this->input()->getArgument('command');
        $tasks = $this->getConfig()->get("commands.{$command}");

        return $this->taskCollectionFactory($tasks);
    }
}
