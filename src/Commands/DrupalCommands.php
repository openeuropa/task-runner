<?php

namespace OpenEuropa\TaskRunner\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Base class for commands that interact with a Drupal installation.
 *
 * This contains shared code that can be used in commands regardless of the
 * Drupal version they target.
 */
class DrupalCommands extends AbstractDrupalCommands
{
}
