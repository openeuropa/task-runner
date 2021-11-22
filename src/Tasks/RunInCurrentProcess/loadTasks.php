<?php

namespace OpenEuropa\TaskRunner\Tasks\RunInCurrentProcess;

/**
 * Load run-in-current-process tasks.
 */
trait loadTasks
{

    /**
     * @param string|\Robo\Contract\CommandInterface $command
     *
     * @return \Robo\Task\Base\Exec|\Robo\Collection\CollectionBuilder
     */
    public function taskRunInCurrentProcess($command) {
        return $this->task(RunInCurrentProcess::class, $command);
    }

}
