<?php

namespace My\Custom\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Commands\AbstractCommands;
use Symfony\Component\Console\Input\InputOption;

/**
 * Commands for testing CollectionFactory tasks.
 */
class TestCommands extends AbstractCommands
{
    /**
     * @command custom:test
     *
     * @option filepath
     * @option content
     *
     * @param array $options
     */
    public function customTest(array $options = [
        'filepath' => InputOption::VALUE_REQUIRED,
        'content' => InputOption::VALUE_REQUIRED,
    ])
    {
        file_put_contents($options['filepath'], $options['content']);
    }
}
