<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tasks\Drush;

use OpenEuropa\TaskRunnerDrupal\Tasks\Drush\loadTasks as newLoadTasks;

/**
 * Robo loadTasks trait for the Drush commands.
 *
 * phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
 */
trait loadTasks
{
    use newLoadTasks;
}
