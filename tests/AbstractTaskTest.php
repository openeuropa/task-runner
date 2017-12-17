<?php

namespace EC\OpenEuropa\TaskRunner\Tests;

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
abstract class AbstractTaskTest extends TestCase implements ContainerAwareInterface
{
    use TaskAccessor;
    use ContainerAwareTrait;

    /**
     * Setup hook.
     */
    public function setup()
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);
    }

    /**
     * @return CollectionBuilder
     */
    public function collectionBuilder()
    {
        return CollectionBuilder::create($this->getContainer(), new \Robo\Tasks());
    }
}
