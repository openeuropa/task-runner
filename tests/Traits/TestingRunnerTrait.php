<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tests\Traits;

use Composer\Autoload\ClassLoader;
use OpenEuropa\TaskRunner\TaskRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait TestingRunnerTrait
{
    /**
     * Returns a task runner having the configuration already processed.
     *
     * The configuration is gathered from config provider classes and applied on
     * top of commands default configuration. The replacement of tokens is only
     * processed at runtime. This method forces the config token replacements in
     * order to make the full processed configuration available for tests.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Composer\Autoload\ClassLoader                    $classLoader
     *
     * @return \OpenEuropa\TaskRunner\TaskRunner
     */
    protected function getTestingRunner(InputInterface $input, OutputInterface $output, ClassLoader $classLoader): TaskRunner
    {
        $taskRunner = new TaskRunner($input, $output, $classLoader);
        $prepareConfigMethod = new \ReflectionMethod($taskRunner, 'prepareApplication');
        $prepareConfigMethod->setAccessible(TRUE);
        $prepareConfigMethod->invoke($taskRunner);
        return $taskRunner;
    }
}
