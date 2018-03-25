<?php

namespace OpenEuropa\TaskRunner;

use Composer\Autoload\ClassLoader;
use Consolidation\AnnotatedCommand\AnnotatedCommand;
use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use League\Container\ContainerAwareTrait;
use OpenEuropa\TaskRunner\Commands\DynamicCommands;
use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Services\Composer;
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
        $this->runner->registerCommandClasses($this->application, $this->getCommandDiscovery()->discover(__DIR__, 'OpenEuropa\\TaskRunner'));

        // Register commands defined in runner.yml file.
        $this->registerDynamicCommands($this->application);
    }

    /**
     * @return int
     */
    public function run()
    {
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
        $container->share('filesystem', Filesystem::class);

        // Add service inflectors.
        $container->inflector(ComposerAwareInterface::class)
            ->invokeMethod('setComposer', ['task_runner.composer']);
        $container->inflector(FilesystemAwareInterface::class)
            ->invokeMethod('setFilesystem', ['filesystem']);

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
        $customCommands = $this->getConfig()->get('commands', []);
        foreach ($customCommands as $name => $commandDefinition) {
            /** @var \Consolidation\AnnotatedCommand\AnnotatedCommandFactory $commandFactory */
            $commandFileName = DynamicCommands::class."Commands";
            $commandClass = $this->container->get($commandFileName);
            $commandFactory = $this->container->get('commandFactory');
            $commandInfo = $commandFactory->createCommandInfo($commandClass, 'runTasks');
            $command = $commandFactory->createCommand($commandInfo, $commandClass)->setName($name);

            // Dynamic commands may define their own options.
            $this->addOptions($command, $commandDefinition);

            // Append also options of subsequent tasks.
            foreach ($this->getTasks($name) as $taskEntry) {
                // This is a 'run' task.
                if (is_array($taskEntry) && isset($taskEntry['task']) && ($taskEntry['task'] === 'run') && !empty($taskEntry['command'])) {
                    if (!empty($customCommands[$taskEntry['command']])) {
                        // Add the options of another custom command.
                        $this->addOptions($command, $customCommands[$taskEntry['command']]);
                    } else {
                        // Add the options of an already registered command.
                        $subCommand = $this->application->get($taskEntry['command']);
                        $command->addOptions($subCommand->getDefinition()->getOptions());
                    }
                }
            }

            $application->add($command);
        }
    }

    /**
     * @param \Consolidation\AnnotatedCommand\AnnotatedCommand $command
     * @param array $commandDefinition
     */
    private function addOptions(AnnotatedCommand $command, array $commandDefinition)
    {
        // This command doesn't define any option.
        if (empty($commandDefinition['options'])) {
            return;
        }

        $defaults = array_fill_keys(['shortcut', 'mode', 'description', 'default'], null);
        foreach ($commandDefinition['options'] as $optionName => $optionDefinition) {
            $optionDefinition += $defaults;
            $command->addOption(
                "--$optionName",
                $optionDefinition['shortcut'],
                $optionDefinition['mode'],
                $optionDefinition['description'],
                $optionDefinition['default']
            );
        }
    }

    /**
     * @param string $command
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private function getTasks($command)
    {
        $commands = $this->getConfig()->get('commands', []);
        if (!isset($commands[$command])) {
            throw new \InvalidArgumentException("Custom command '$command' not defined.");
        }

        return !empty($commands[$command]['tasks']) ? $commands[$command]['tasks'] : $commands[$command];
    }
}
