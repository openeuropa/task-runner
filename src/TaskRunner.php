<?php

namespace EC\OpenEuropa\TaskRunner;

use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use EC\OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use EC\OpenEuropa\TaskRunner\Services\Composer;
use League\Container\Container;
use League\Container\ContainerAwareTrait;
use League\Container\Exception\NotFoundException;
use Robo\Application;
use Robo\Common\ConfigAwareTrait;
use Robo\Config\Config;
use Robo\Robo;
use Robo\Runner as RoboRunner;
use Symfony\Component\Console\Input\ArgvInput;
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
     * @var InputInterface
     */
    private $input;

    /**
     * @var Application
     */
    private $application;

    /**
     * TaskRunner constructor.
     *
     * TaskRunner constructor.
     * @param array                $configPaths
     * @param InputInterface       $input
     * @param OutputInterface|null $output
     */
    public function __construct(array $configPaths = [], InputInterface $input = null, OutputInterface $output = null)
    {
        $this->output = is_null($output) ? new ConsoleOutput() : $output;
        $this->input = is_null($input) ? new ArgvInput() : $input;

        // Create configuration.
        $config = $this->createConfiguration($configPaths);
        $this->setConfig($config);

        // Create application.
        $this->application = new Application(self::APPLICATION_NAME, $config->get('version'));

        // Create container.
        $container = $this->createContainer($config);
        $this->setContainer($container);

        // Create and initialize runner.
        $this->runner = new RoboRunner();
        $this->runner->setContainer($container);
        $this->runner->registerCommandClasses($this->application, $this->discoverCommandClasses());
    }

    /**
     * @return int
     */
    public function run()
    {
        return $this->runner->run($this->input, $this->output, $this->application);
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
     * @param string $class
     *
     * @return \EC\OpenEuropa\TaskRunner\Commands\BaseCommands
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getCommands($class)
    {
        $serviceName = "\\{$class}Commands";

        return $this->getContainer()->get($serviceName);
    }

    /**
     * @return array
     */
    private function discoverCommandClasses()
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
        $configPaths = array_merge([
            __DIR__.'/../config/runner.yml',
            __DIR__.'/../config/commands/drupal.yml',
            __DIR__.'/../config/commands/setup.yml',
            __DIR__.'/../config/commands/changelog.yml',
        ], $configPaths);

        return Robo::createConfiguration($configPaths);
    }

    /**
     * Create and configure container.
     */
    private function createContainer(Config $config)
    {
        $container = Robo::createDefaultContainer($this->input, $this->output, $this->application, $config);
        $container->get('commandFactory')->setIncludeAllPublicMethods(false);
        $container->share('task_runner.composer', Composer::class)
            ->withArgument(getcwd());

        // Add service inflectors.
        $container->inflector(ComposerAwareInterface::class)
          ->invokeMethod('setComposer', ['task_runner.composer']);

        return $container;
    }
}
