<?php

namespace EC\OpenEuropa\TaskRunner;

use League\Container\ContainerAwareTrait;
use Robo\Common\ConfigAwareTrait;
use Robo\Config\Config;
use Robo\Robo;
use Robo\Runner as RoboRunner;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Application.
 *
 * @package EC\OpenEuropa\TaskRunner
 */
class TaskRunner
{
    use ConfigAwareTrait;
    use ContainerAwareTrait;

    const APPLICATION_NAME = 'Open Europa Task Runner';

    const REPOSITORY = 'ec-europa/oe-task-runner';

    /**
     * @var RoboRunner
     */
    private $runner;

    /**
     * TaskRunner constructor.
     * @param Config               $config
     * @param InputInterface|NULL  $input
     * @param OutputInterface|NULL $output
     */
    public function __construct(Config $config, InputInterface $input = null, OutputInterface $output = null)
    {

        // Create application.
        $this->setConfig($config);
        $application = new Application(self::APPLICATION_NAME, $config->get('version'));

        // Create and configure container.
        $container = Robo::createDefaultContainer($input, $output, $application, $config);
        $this->setContainer($container);

        // Instantiate Robo Runner.
        $this->runner = new RoboRunner();
        $this->runner->setContainer($container);
        $this->runner->setSelfUpdateRepository(self::REPOSITORY);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        return $this->runner->run($input, $output);
    }
}
