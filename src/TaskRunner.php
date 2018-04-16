<?php

namespace OpenEuropa\TaskRunner;

use Composer\Autoload\ClassLoader;
use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use Gitonomy\Git\Repository;
use OpenEuropa\TaskRunner\Commands\DynamicCommands;
use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use OpenEuropa\TaskRunner\Contract\RepositoryAwareInterface;
use OpenEuropa\TaskRunner\Services\Composer;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
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
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Application.
 *
 * @package OpenEuropa\TaskRunner
 */
class TaskRunner
{
    use ConfigAwareTrait;
    use ContainerAwareTrait;

    const APPLICATION_NAME = 'OpenEuropa Task Runner';

    const REPOSITORY = 'openeuropa/task-runner';

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
     * @var string
     */
    private $workingDir;

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

        $this->workingDir = $this->getWorkingDir($this->input);
        chdir($this->workingDir);

        $this->config = $this->createConfiguration();
        $this->application = $this->createApplication();
        $this->container = $this->createContainer($this->input, $this->output, $this->application, $this->config);

        // Create and initialize runner.
        $this->runner = new RoboRunner();
        $this->runner->setContainer($this->container);
    }

    /**
     * @return int
     */
    public function run()
    {
        // Register command classes.
        $this->runner->registerCommandClasses($this->application, $this->getCommandDiscovery()->discover(__DIR__, 'OpenEuropa\\TaskRunner'));

        // Register commands defined in runner.yml file.
        $this->registerDynamicCommands($this->application);

        // Run command.
        return $this->runner->run($this->input, $this->output, $this->application);
    }

    /**
     * @param string $class
     *
     * @return \OpenEuropa\TaskRunner\Commands\AbstractCommands
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getCommands($class)
    {
        // Register command classes.
        $this->runner->registerCommandClasses($this->application, $this->getCommandDiscovery()->discover(__DIR__, 'OpenEuropa\\TaskRunner'));

        return $this->getContainer()->get("{$class}Commands");
    }

    /**
     * @param \Composer\Autoload\ClassLoader $classLoader
     */
    public function registerExternalCommands(ClassLoader $classLoader)
    {
        $commands = [];
        $discovery = $this->getCommandDiscovery();

        foreach ($classLoader->getPrefixesPsr4() as $baseNamespace => $directoryList) {
            $directoryList = array_filter($directoryList, function ($path) {
                return is_dir($path.'/TaskRunner/Commands');
            });

            if (!empty($directoryList)) {
                $discoveredCommands = $discovery->discover($directoryList, $baseNamespace);
                $commands = array_merge($commands, $discoveredCommands);
            }
        }

        $this->runner->registerCommandClasses($this->application, $commands);
    }

    /**
     * @return \Consolidation\AnnotatedCommand\CommandFileDiscovery
     */
    private function getCommandDiscovery()
    {
        $discovery = new CommandFileDiscovery();
        $discovery->setSearchPattern('*Commands.php')->setSearchLocations(['TaskRunner', 'Commands']);

        return $discovery;
    }

    /**
     * Create default configuration.
     *
     * @return Config
     */
    private function createConfiguration()
    {
        return Robo::createConfiguration([
            __DIR__.'/../config/runner.yml',
            'runner.yml.dist',
            'runner.yml',
        ]);
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
        $container->share('task_runner.composer', Composer::class)->withArgument($this->workingDir);
        $container->share('repository', Repository::class)->withArgument($this->workingDir);
        $container->share('filesystem', Filesystem::class);

        // Add service inflectors.
        $container->inflector(ComposerAwareInterface::class)
          ->invokeMethod('setComposer', ['task_runner.composer']);
        $container->inflector(FilesystemAwareInterface::class)
          ->invokeMethod('setFilesystem', ['filesystem']);
        $container->inflector(RepositoryAwareInterface::class)
          ->invokeMethod('setRepository', ['repository']);

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
          ->addOption(new InputOption('--working-dir', null, InputOption::VALUE_REQUIRED, 'Working directory, defaults to current working directory.', $this->workingDir));

        return $application;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return mixed
     */
    private function getWorkingDir(InputInterface $input)
    {
        return $input->getParameterOption('--working-dir', getcwd());
    }

    /**
     * @param \Robo\Application $application
     */
    private function registerDynamicCommands(Application $application)
    {
        foreach ($this->getConfig()->get('commands', []) as $name => $tasks) {
            /** @var \Consolidation\AnnotatedCommand\AnnotatedCommandFactory $commandFactory */
            $commandFileName = DynamicCommands::class."Commands";
            $commandClass = $this->container->get($commandFileName);
            $commandFactory = $this->container->get('commandFactory');
            $commandInfo = $commandFactory->createCommandInfo($commandClass, 'runTasks');
            $command = $commandFactory->createCommand($commandInfo, $commandClass)->setName($name);
            $application->add($command);
        }
    }
}
