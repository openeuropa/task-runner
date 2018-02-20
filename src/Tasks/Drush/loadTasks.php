<?php

namespace OpenEuropa\TaskRunner\Tasks\Drush;

/**
 * Trait loadTasks
 *
 * @package OpenEuropa\TaskRunner\Tasks\Drush
 */
trait loadTasks
{
    /**
     * @return \OpenEuropa\TaskRunner\Tasks\Drush\Drush
     */
    public function taskDrush($command)
    {
        return $this->task(Drush::class, $command);
    }
}
