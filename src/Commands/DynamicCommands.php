<?php

namespace EC\OpenEuropa\TaskRunner\Commands;

use EC\OpenEuropa\TaskRunner\Tasks\CollectionFactory\loadTasks;

/**
 * Class DynamicCommands
 *
 * @package EC\OpenEuropa\TaskRunner\Commands
 */
class DynamicCommands extends BaseCommands
{
    use loadTasks;

    /**
     * @return \EC\OpenEuropa\TaskRunner\Tasks\CollectionFactory\CollectionFactory
     */
    public function runTasks()
    {
        $command = $this->input()->getArgument('command');
        $tasks = $this->getConfig()->get("commands.{$command}");

        return $this->taskCollectionFactory($tasks);
    }
}
