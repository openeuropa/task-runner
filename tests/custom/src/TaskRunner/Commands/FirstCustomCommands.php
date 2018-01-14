<?php

namespace EC\My\Custom\TaskRunner\Commands;

use EC\OpenEuropa\TaskRunner\Commands\AbstractCommands;

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
