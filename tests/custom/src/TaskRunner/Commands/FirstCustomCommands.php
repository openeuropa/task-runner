<?php

namespace EC\OpenEuropa\TaskRunner\My\Custom\TaskRunner\Commands;

use EC\OpenEuropa\TaskRunner\Commands\BaseCommands;

/**
 * Class FirstCustomCommands
 *
 * @package My\Custom\TaskRunner\Commands
 */
class FirstCustomCommands extends BaseCommands
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
