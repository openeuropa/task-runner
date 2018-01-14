<?php

namespace EC\OpenEuropa\TaskRunner\Commands;

use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\AnnotatedCommand\CommandData;
use EC\OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Class DynamicCommands
 *
 * @package EC\OpenEuropa\TaskRunner\Commands
 */
class DynamicCommands extends AbstractCommands
{
    use TaskRunnerTasks\CollectionFactory\loadTasks;

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
