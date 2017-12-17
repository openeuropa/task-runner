<?php

namespace EC\OpenEuropa\TaskRunner;

use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use League\Container\ContainerAwareTrait;
use Robo\Application;
use Robo\Common\ConfigAwareTrait;
use Robo\Config\Config;
use Robo\Robo;
use Robo\Runner as RoboRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
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

    const APPLICATION_NAME = 'OpenEuropa Task Runner';

    const REPOSITORY = 'ec-europa/oe-task-runner';

    /**
     * @var RoboRunner
     */
    private $runner;

    /**
     * @var ConsoleOutput|OutputInterface
     */
    private $output;

    /**
     * TaskRunner constructor.
     *
     * @param array                $configPaths
     * @param OutputInterface|null $output
     */
    public function __construct(array $configPaths = [], OutputInterface $output = null)
    {
        $this->output = is_null($output) ? new ConsoleOutput() : $output;

        // Create application.
        $config = $this->createConfiguration($configPaths);
        $this->setConfig($config);
        $application = new Application(self::APPLICATION_NAME, $config->get('version'));

        // Create and configure container.
        $container = Robo::createDefaultContainer(null, $output, $application, $config);
        $container->get('commandFactory')->setIncludeAllPublicMethods(false);

        // Create and initialize runner.
        $this->runner = new RoboRunner();
        $this->runner->setContainer($container);
        $this->runner->registerCommandClasses($application, $this->getCommandClasses());

        // Set processed container.
        $this->setContainer($container);
    }

    /**
     * @param InputInterface $input
     *
     * @return int
     */
    public function run(InputInterface $input)
    {
        return $this->runner->run($input, $this->output);
    }

    /**
     * @return RoboRunner
     */
    public function getRunner()
    {
        return $this->runner;
    }

    /**
     * @return ConsoleOutput|OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return array
     */
    private function getCommandClasses()
    {
        $discovery = new CommandFileDiscovery();
        $discovery->setSearchPattern('*Commands.php')->setSearchLocations(['Commands']);

        return $discovery->discover(__DIR__.'/../src/', '\EC\OpenEuropa\TaskRunner');
    }

    /**
     * @param array $configPaths
     * @return Config
     */
    private function createConfiguration(array $configPaths)
    {
        $configPaths = [
            __DIR__.'/../config/runner.yml',
            __DIR__.'/../config/commands/drupal.yml',
        ] + $configPaths;

        return Robo::createConfiguration($configPaths);
    }
}
