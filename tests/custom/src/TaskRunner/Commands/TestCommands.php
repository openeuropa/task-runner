<?php

declare(strict_types=1);

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
     * @param string $content
     * @param array $options
     *
     * @option filepath
     * @option array-opt
     */
    public function customTest(string $content, array $options = [
        'filepath' => InputOption::VALUE_REQUIRED,
        'array-opt' => [],
    ]): void
    {
        file_put_contents($options['filepath'], $content . $options['array-opt'][0] . $options['array-opt'][1]);
    }
}
