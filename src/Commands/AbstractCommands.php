<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Commands;

use Consolidation\AnnotatedCommand\AnnotationData;
use Robo\Common\ConfigAwareTrait;
use Robo\Common\IO;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Exception\TaskException;
use Robo\LoadAllTasks;
use Robo\Robo;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use OpenEuropa\TaskRunnerDrupal\Commands\AbstractCommands as TaskRunnerDrupalCommands;

/**
 * Base class for commands.
 */
abstract class AbstractCommands extends TaskRunnerDrupalCommands
{ }
