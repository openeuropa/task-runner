<?php

namespace EC\OpenEuropa\TaskRunner\Tests;

use EC\OpenEuropa\TaskRunner\TaskRunner;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\BufferedOutput;
use Robo\TaskAccessor;
use Robo\Collection\CollectionBuilder;

/**
 * Class AbstractTaskTest.
 *
 * @package EC\OpenEuropa\TaskRunner\Tests
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
        $runner = new TaskRunner([], null, $this->output);
        $this->setContainer($runner->getContainer());
    }

    /**
     * @return CollectionBuilder
     */
    public function collectionBuilder()
    {
        return CollectionBuilder::create($this->getContainer(), new \Robo\Tasks());
    }
}
