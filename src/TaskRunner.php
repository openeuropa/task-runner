<?php

namespace EC\OpenEuropa\TaskRunner;

use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use EC\OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use EC\OpenEuropa\TaskRunner\Services\Composer;
use League\Container\ContainerAwareTrait;
use Robo\Application;
use Robo\Common\ConfigAwareTrait;
use Robo\Config\Config;
use Robo\Robo;
use Robo\Runner as RoboRunner;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
     * @param InputInterface       $input
     * @param OutputInterface|null $output
     */
    public function __construct(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->input = is_null($input) ? new ArgvInput() : $input;
        $this->output = is_null($output) ? new ConsoleOutput() : $output;

        $this->application = $this->createApplication();
        $this->config = $this->createConfiguration();
        $this->container = $this->createContainer($this->input, $this->output, $this->application, $this->config);

        // Create and initialize runner.
        $this->runner = new RoboRunner();
        $this->runner->setContainer($this->container);
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
     * Create default configuration.
     *
     * @return Config
     */
    private function createConfiguration()
    {
        return Robo::createConfiguration([__DIR__.'/../config/runner.yml']);
    }

    /**
     * Create and configure container.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Robo\Application $application
     * @param \Robo\Config\Config $config
     *
     * @return \League\Container\Container|\League\Container\ContainerInterface
     */
    private function createContainer(InputInterface $input, OutputInterface $output, Application $application, Config $config)
    {
        $container = Robo::createDefaultContainer($input, $output, $application, $config);
        $container->get('commandFactory')->setIncludeAllPublicMethods(false);
        $container->share('task_runner.composer', Composer::class)
            ->withArgument(getcwd());

        // Add service inflectors.
        $container->inflector(ComposerAwareInterface::class)
          ->invokeMethod('setComposer', ['task_runner.composer']);

        return $container;
    }

    /**
     * Create application.
     *
     * @return \Robo\Application
     */
    private function createApplication()
    {
        $application = new Application(self::APPLICATION_NAME, null);
        $application
          ->getDefinition()
          ->addOption(new InputOption('--working-dir', null, InputOption::VALUE_REQUIRED, 'Working directory, defaults to current working directory.', getcwd()));

        return $application;
    }
}
