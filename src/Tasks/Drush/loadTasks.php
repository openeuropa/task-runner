<?php

namespace EC\OpenEuropa\TaskRunner\Tasks\Drush;

/**
 * Trait loadTasks
 *
 * @package EC\OpenEuropa\TaskRunner\Tasks\Drush
 */
trait loadTasks
{
    /**
     * @return \EC\OpenEuropa\TaskRunner\Tasks\Drush\Drush
     */
    public function taskDrush($command)
    {
        return $this->task(Drush::class, $command);
    }
}
