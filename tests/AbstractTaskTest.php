<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tests;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use OpenEuropa\TaskRunner\TaskRunner;
use Robo\Collection\CollectionBuilder;
use Robo\TaskAccessor;
use Robo\Tasks;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Base class for testing tasks.
 */
abstract class AbstractTaskTest extends AbstractTest implements ContainerAwareInterface
{
    use TaskAccessor;
    use ContainerAwareTrait;

    /**
     * @var \Symfony\Component\Console\Output\BufferedOutput
     */
    protected $output;

    /**
     * Setup hook.
     */
    public function setup()
    {
        $this->output = new BufferedOutput();
        $runner = new TaskRunner(new StringInput(''), $this->output, $this->getClassLoader());
        $this->setContainer($runner->getContainer());
        $this->getContainer()->get('config')->set('runner.bin_dir', realpath(__DIR__ . '/../bin'));
    }

    /**
     * @return CollectionBuilder
     */
    public function collectionBuilder()
    {
        return CollectionBuilder::create($this->getContainer(), new Tasks());
    }
}
