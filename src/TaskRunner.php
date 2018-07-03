<?php

namespace OpenEuropa\TaskRunner;

use Composer\Autoload\ClassLoader;
use Gitonomy\Git\Repository;
use OpenEuropa\TaskRunner\TaskRunner\Commands\DynamicCommands;
use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use OpenEuropa\TaskRunner\Contract\RepositoryAwareInterface;
use OpenEuropa\TaskRunner\Contract\TimeAwareInterface;
use OpenEuropa\TaskRunner\Services\Composer;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Services\Time;
use Psr\Container\ContainerInterface;
use Robo\Application;
use Robo\Common\ConfigAwareTrait;
use Robo\Config\Config;
use Robo\Contract\ConfigAwareInterface;
use Robo\Robo;
use Robo\Runner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class TaskRunner.
 *
 * @package OpenEuropa\TaskRunner
 */
class TaskRunner extends Runner implements ConfigAwareInterface
{
    use ConfigAwareTrait;

    const APPLICATION_NAME = 'OpenEuropa Task Runner';

    const REPOSITORY = 'openeuropa/task-runner';

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
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Composer\Autoload\ClassLoader                    $classLoader
     */
    public function __construct(InputInterface $input, OutputInterface $output, ClassLoader $classLoader)
    {
        parent::__construct();
        $this->setRelativePluginNamespace('TaskRunner');
        $this->setInput($input);
        $this->setOutput($output);
        $this->setClassLoader($classLoader);

        $this->workingDir = $this->getWorkingDir($this->input);
        chdir($this->workingDir);
        $this->application = $this->createApplication();
        $this->setClassLoader($this->classLoader);

        $config = $this->createConfiguration();

        // Create the container.
        $container = Robo::createDefaultContainer(
            $this->input,
            $this->output,
            $this->application,
            $config,
            $this->classLoader
        );

        // Configure the container.
        $this->configureContainer(
            $container,
            $this->application,
            $config
        );

        // Set the container.
        $this->setContainer($container);
        $this->setConfig($config);
    }

    /**
     * Wrapper around the Runner::run() method.
     *
     * @param null  $input
     * @param null  $output
     * @param null  $app
     * @param array $commandFiles
     * @param null  $classLoader
     *
     * @return int
     */
    public function run($input = null, $output = null, $app = null, $commandFiles = [], $classLoader = null)
    {
        $input = $input ?: $this->input;
        $output = $output ?: $this->output;
        $application = $app ?: $this->application;
        $classLoader = $classLoader ?: $this->classLoader;

        return parent::run($input, $output, $application, $commandFiles, $classLoader);
    }

    /**
     * @param string $class
     *
     * @return \OpenEuropa\TaskRunner\TaskRunner\Commands\AbstractCommands
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getCommands($class)
    {
        return $this->getContainer()->get("{$class}Commands");
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
            getenv('TASKRUNNER_CONFIG') ?: getenv('HOME').'/.config/runner/runner.yml',
        ])
            ->set(
                'runner.working_dir',
                realpath($this->workingDir)
            );
    }

    /**
     * Configure the container.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \Robo\Application $application
     * @param \Robo\Config\Config $config
     */
    private function configureContainer(ContainerInterface $container, Application $application, Config $config)
    {
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

        $commandClasses = $this->discoverCommandClasses($this->relativePluginNamespace);
        /** @var \Consolidation\AnnotatedCommand\AnnotatedCommandFactory $commandFactory */
        $commandFactory = $container->get('commandFactory');

        foreach ($commandClasses as $commandClass) {
            $commandFileInstance = $this->instantiateCommandClass($commandClass);
            if (!$commandFileInstance) {
                continue;
            }

            // Register commands for all of the public methods in the RoboFile.
            $commandList = $commandFactory->createCommandsFromClass($commandFileInstance);
            foreach ($commandList as $command) {
                $application->add($command);
            }
        }

        foreach ($config->get('commands', []) as $name => $tasks) {
            $commandClass = $container->get(DynamicCommands::class."Commands");
            $commandInfo = $commandFactory->createCommandInfo($commandClass, 'runTasks');
            $command = $commandFactory->createCommand($commandInfo, $commandClass)->setName($name);
            $application->add($command);
        }
    }

    /**
     * Create application.
     *
     * @return \Robo\Application
     */
    private function createApplication()
    {
        $application = Robo::createDefaultApplication(self::APPLICATION_NAME, null);
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
}
