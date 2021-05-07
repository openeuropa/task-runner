<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner;

use Composer\Autoload\ClassLoader;
use Consolidation\AnnotatedCommand\Parser\Internal\DocblockTag;
use Consolidation\AnnotatedCommand\Parser\Internal\TagFactory;
use Consolidation\Config\Loader\ConfigProcessor;
use Gitonomy\Git\Repository;
use League\Container\ContainerAwareTrait;
use OpenEuropa\TaskRunner\Commands\ChangelogCommands;
use OpenEuropa\TaskRunner\Commands\DrupalCommands;
use OpenEuropa\TaskRunner\Commands\DynamicCommands;
use OpenEuropa\TaskRunner\Commands\ReleaseCommands;
use OpenEuropa\TaskRunner\Commands\RunnerCommands;
use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use OpenEuropa\TaskRunner\Contract\ConfigProviderInterface;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Contract\RepositoryAwareInterface;
use OpenEuropa\TaskRunner\Contract\TimeAwareInterface;
use OpenEuropa\TaskRunner\Services\Composer;
use OpenEuropa\TaskRunner\Services\Time;
use Robo\Application;
use Robo\Common\ConfigAwareTrait;
use Robo\Config\Config;
use Robo\Robo;
use Robo\Runner as RoboRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Logic for discovering and running commands and tasks.
 */
class TaskRunner
{
    use ConfigAwareTrait;
    use ContainerAwareTrait;

    public const APPLICATION_NAME = 'OpenEuropa Task Runner';

    public const REPOSITORY = 'openeuropa/task-runner';

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
        ReleaseCommands::class,
        RunnerCommands::class,
    ];

    /**
     * Constructs a new TaskRunner.
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

        $this->config = new Config();
        $this->application = $this->createApplication();
        $this->application->setAutoExit(false);
        $this->container = $this->createContainer(
            $this->input,
            $this->output,
            $this->application,
            $this->config,
            $classLoader
        );

        $this->createConfiguration();

        // Create and initialize runner.
        $this->runner = new RoboRunner();
        $this->runner->setContainer($this->container);
    }

    /**
     * Executes the command that has been provided on the command line input.
     *
     * A command consists of multiple tasks and is defined either as a Command
     * class in the `vendor\TaskRunner\Commands` subnamespace, or a dynamic
     * command defined in "runner.yml".
     *
     * Robo is not architected in a way that makes it easily extensible. It has
     * no events that we can hook into to allow it to discover our two custom
     * types of commands. We work around this by registering our own commands on
     * the container in the same way as is done by Robo, and then delegating to
     * `\Robo\Runner::run()`.
     *
     * @return int
     *   The exit code returned by the command.
     */
    public function run()
    {
        // Discover early the commands to allow dynamic command overrides.
        $commandClasses = $this->discoverCommandClasses();
        $commandClasses = array_merge($this->defaultCommandClasses, $commandClasses);

        // Register command classes.
        $this->runner->registerCommandClasses($this->application, $commandClasses);

        // Register commands defined in runner.yml file. These are registered
        // after the command classes so that dynamic commands can override
        // commands defined in classes.
        $this->registerDynamicCommands($this->application);

        // Run the command entered by the user in the CLI.
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
     * Parses the configuration files, and merges them into the Config object.
     */
    private function createConfiguration()
    {
        $config = new Config();
        $config->set('runner.working_dir', realpath($this->workingDir));

        foreach ($this->getConfigProviders() as $class) {
            /** @var \OpenEuropa\TaskRunner\Contract\ConfigProviderInterface $class */
            $class::provide($config);
        }

        // Resolve variables and import into config.
        $processor = (new ConfigProcessor())->add($config->export());
        $this->config->import($processor->export());
        // Keep the container in sync.
        $this->container->share('config', $this->config);
    }

    /**
     * Discovers all config providers.
     *
     * @return string[]
     *   An array of fully qualified class names of available config providers.
     *
     * @throws \ReflectionException
     *   Thrown if a config provider doesn't have a valid annotation.
     */
    private function getConfigProviders(): array
    {
        /** @var \Robo\ClassDiscovery\RelativeNamespaceDiscovery $discovery */
        $discovery = Robo::service('relativeNamespaceDiscovery');
        $discovery->setRelativeNamespace('TaskRunner\ConfigProviders')
            ->setSearchPattern('/.*ConfigProvider\.php$/');

        // Discover config providers.
        foreach ($discovery->getClasses() as $class) {
            if (is_subclass_of($class, ConfigProviderInterface::class)) {
                $classes[$class] = $this->getConfigProviderPriority($class);
            }
        }

        // High priority modifiers run first.
        arsort($classes, SORT_NUMERIC);

        return array_keys($classes);
    }

    /**
     * @param string $class
     * @return float
     * @throws \ReflectionException
     */
    private function getConfigProviderPriority($class)
    {
        $priority = 0.0;
        $reflectionClass = new \ReflectionClass($class);
        if ($docBlock = $reflectionClass->getDocComment()) {
            // Remove the leading /** and the trailing */
            $docBlock = preg_replace('#^\s*/\*+\s*#', '', $docBlock);
            $docBlock = preg_replace('#\s*\*+/\s*#', '', $docBlock);

            // Nothing left? Exit.
            if (empty($docBlock)) {
                return $priority;
            }

            $tagFactory = new TagFactory();
            foreach (explode("\n", $docBlock) as $row) {
                // Remove trailing whitespace and leading space + '*'s
                $row = rtrim($row);
                $row = preg_replace('#^[ \t]*\**#', '', $row);
                $tagFactory->parseLine($row);
            }

            $priority = array_reduce($tagFactory->getTags(), function ($priority, DocblockTag $tag) {
                if ($tag->getTag() === 'priority') {
                    $value = $tag->getContent();
                    if (is_numeric($value)) {
                        $priority = (float) $value;
                    }
                }
                return $priority;
            }, $priority);
        }
        return $priority;
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
    private function createContainer(
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        Config $config,
        ClassLoader $classLoader
    ) {
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
            ->addOption(new InputOption(
                '--working-dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Working directory, defaults to current working directory.',
                $this->workingDir
            ));

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
     * Registers dynamic commands in the container so Robo can find them.
     *
     * The standard class defined commands have already been registered at this
     * point. If a dynamic command has the same identifier or alias as a class
     * defined command it will replace it. This allows users to override
     * existing commands in their runner.yml file.
     *
     * @param \Robo\Application $application
     *   The Robo Symfony application.
     */
    private function registerDynamicCommands(Application $application)
    {
        if (!$commands = $this->getConfig()->get('commands')) {
            return;
        }

        /** @var \Consolidation\AnnotatedCommand\AnnotatedCommandFactory $commandFactory */
        $commandFactory = $this->container->get('commandFactory');

        // Robo registers command classes in the container using the qualified
        // namespace with "Commands" appended to it. This results in identifiers
        // like "OpenEuropa\TaskRunner\Commands\DrupalCommandsCommands".
        // @see \Robo\Runner::instantiateCommandClass()
        $commandFileName = DynamicCommands::class . "Commands";
        $this->runner->registerCommandClass($this->application, DynamicCommands::class);
        $commandClass = $this->container->get($commandFileName);

        foreach ($commands as $name => $tasks) {
            $aliases = [];
            // This command has been already registered as an annotated command.
            if ($application->has($name)) {
                $registeredCommand = $application->get($name);
                $aliases = $registeredCommand->getAliases();
                // The dynamic command overrides an alias rather than a
                // registered command main name. Get the command main name.
                if (in_array($name, $aliases, true)) {
                    $name = $registeredCommand->getName();
                }
            }

            $commandInfo = $commandFactory->createCommandInfo($commandClass, 'runTasks');
            $commandInfo->addAnnotation('tasks', $tasks);
            $command = $commandFactory->createCommand($commandInfo, $commandClass)
                ->setName($name)
                ->setAliases($aliases);
            $application->add($command);
        }
    }

    /**
     * Discovers task runner commands that are provided by various packages.
     *
     * This traverses the namespace tree and returns all classes that are
     * located in the source tree in the folder "TaskRunner/Commands/" and have
     * a filename that ends with "*Command.php" or "*Commands.php".
     *
     * @return string[]
     */
    protected function discoverCommandClasses()
    {
        /** @var \Robo\ClassDiscovery\RelativeNamespaceDiscovery $discovery */
        $discovery = Robo::service('relativeNamespaceDiscovery');
        $discovery->setRelativeNamespace('TaskRunner\Commands')
            ->setSearchPattern('/.*Commands?\.php$/');
        return $discovery->getClasses();
    }
}
