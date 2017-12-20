<?php

namespace EC\OpenEuropa\TaskRunner\Tests;

use EC\OpenEuropa\TaskRunner\TaskRunner;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;
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
     * Setup hook.
     */
    public function setup()
    {
        $runner = new TaskRunner([], null, new NullOutput());
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
