<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tasks\Drush;

/**
 * Robo loadTasks trait for the Drush commands.
 *
 * phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
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
