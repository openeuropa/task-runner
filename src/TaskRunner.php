<?php

namespace OpenEuropa\TaskRunner;

use Composer\Autoload\ClassLoader;
use Gitonomy\Git\Repository;
use OpenEuropa\TaskRunner\Commands\ChangelogCommands;
use OpenEuropa\TaskRunner\Commands\DrupalCommands;
use OpenEuropa\TaskRunner\Commands\DynamicCommands;
use OpenEuropa\TaskRunner\Commands\ReleaseCommands;
use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use OpenEuropa\TaskRunner\Contract\RepositoryAwareInterface;
use OpenEuropa\TaskRunner\Contract\TimeAwareInterface;
use OpenEuropa\TaskRunner\Services\Composer;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use League\Container\ContainerAwareTrait;
use OpenEuropa\TaskRunner\Services\Time;
use Robo\Application;
use Robo\Common\ConfigAwareTrait;
use Robo\Config\Config;
use Robo\Robo;
use Robo\Runner as RoboRunner;
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
     * @var array
     */
    private $defaultCommandClasses = [
        ChangelogCommands::class,
        DrupalCommands::class,
        DynamicCommands::class,
        ReleaseCommands::class,
    ];

    /**
     * TaskRunner constructor.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Composer\Autoload\ClassLoader                    $classLoader
     */
    public function __construct(InputInterface $input, OutputInterface $output, ClassLoader $classLoader)
    {
        $this->input = $input;
        $this->output = $output;

        $this->workingDir = $this->getWorkingDir($this->input);
        chdir($this->workingDir);

        $this->config = $this->createConfiguration();
        $this->application = $this->createApplication();
        $this->container = $this->createContainer($this->input, $this->output, $this->application, $this->config, $classLoader);

        // Create and initialize runner.
        $this->runner = new RoboRunner();
        $this->runner->setRelativePluginNamespace('TaskRunner');
        $this->runner->setContainer($this->container);
    }

    /**
     * @return int
     */
    public function run()
    {
        // Register command classes.
        $this->runner->registerCommandClasses($this->application, $this->defaultCommandClasses);

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
        $this->runner->registerCommandClasses($this->application, $this->defaultCommandClasses);

        return $this->getContainer()->get("{$class}Commands");
    }

    /**
     * Create default configuration.
     *
     * @return Config
     */
    private function createConfiguration()
    {
        $config = new Config();
        $config->set('runner.working_dir', realpath($this->workingDir));
        Robo::loadConfiguration([
            __DIR__.'/../config/runner.yml',
            'runner.yml.dist',
            'runner.yml',
            $this->getLocalConfigurationFilepath(),
        ], $config);

        return $config;
    }

  /**
   * Get the local configuration filepath.
   *
   * @param string $configuration_file
   *   The default filepath.
   *
   * @return string|null
   *   The local configuration file path, or null if it doesn't exist.
   */
    private function getLocalConfigurationFilepath($configuration_file = 'openeuropa/taskrunner/runner.yml')
    {
        if ($config = getenv('TASKRUNNER_CONFIG')) {
            return $config;
        }

        if ($config = getenv('XDG_CONFIG_HOME')) {
            return $config . '/' . $configuration_file;
        }

        if ($home = getenv('HOME')) {
            return getenv('HOME') . '/.config/' . $configuration_file;
        }

        return null;
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
    private function createContainer(InputInterface $input, OutputInterface $output, Application $application, Config $config, ClassLoader $classLoader)
    {
        $container = Robo::createDefaultContainer($input, $output, $application, $config, $classLoader);
        $container->get('commandFactory')->setIncludeAllPublicMethods(false);
        $container->share('task_runner.composer', Composer::class)->withArgument($this->workingDir);
        $container->share('task_runner.time', Time::class);
        $container->share('repository', Repository::class)->withArgument($this->workingDir);
        $container->share('filesystem', Filesystem::class);

        // Add service inflectors.
        $container->inflector(ComposerAwareInterface::class)
            ->invokeMethod('setComposer', ['task_runner.composer']);
        $container->inflector(TimeAwareInterface::class)
            ->invokeMethod('setTime', ['task_runner.time']);
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
        $application = new Application(self::APPLICATION_NAME, 'UNKNOWN');
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

    /**
     * For testing purpose we could disable auto exit of application.
     */
    public function disableAutoExit()
    {
        $this->application->setAutoExit(false);
    }
}
