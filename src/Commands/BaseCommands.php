<?php

namespace EC\OpenEuropa\TaskRunner\Commands;

use Consolidation\AnnotatedCommand\AnnotatedCommand;
use Robo\Common\IO;
use Robo\Contract\IOAwareInterface;
use Robo\Contract\BuilderAwareInterface;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\LoadAllTasks;
use Robo\Result;

/**
 * Class BaseCommands.
 *
 * @package EC\OpenEuropa\TaskRunner\Commands
 */
class BaseCommands implements BuilderAwareInterface, IOAwareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    use LoadAllTasks;
    use IO;
}
