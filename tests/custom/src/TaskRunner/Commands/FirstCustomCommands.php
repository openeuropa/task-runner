<?php

namespace My\Custom\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Robo\Plugin\AbstractCommands;

/**
 * Class FirstCustomCommands
 *
 * @package My\Custom\TaskRunner\Commands
 */
class FirstCustomCommands extends AbstractCommands
{
    /**
     * @command custom:command-one
     */
    public function commandOne()
    {
    }

    /**
     * @command custom:command-two
     */
    public function commandTwo()
    {
    }
}
