<?php

namespace OpenEuropa\TaskRunner;

use Composer\Autoload\ClassLoader;
use Consolidation\AnnotatedCommand\Parser\Internal\DocblockTag;
use Consolidation\AnnotatedCommand\Parser\Internal\TagFactory;
use Consolidation\Config\Loader\ConfigProcessor;
use Gitonomy\Git\Repository;
use OpenEuropa\TaskRunner\Commands\ChangelogCommands;
use OpenEuropa\TaskRunner\Commands\DrupalCommands;
use OpenEuropa\TaskRunner\Commands\DynamicCommands;
use OpenEuropa\TaskRunner\Commands\ReleaseCommands;
use OpenEuropa\TaskRunner\Commands\RunnerCommands;
use OpenEuropa\TaskRunner\ConfigProviders\DefaultConfigProvider;
use OpenEuropa\TaskRunner\ConfigProviders\FileFromEnvironmentConfigProvider;
use OpenEuropa\TaskRunner\ConfigProviders\LocalFileConfigProvider;
use OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use OpenEuropa\TaskRunner\Contract\ConfigProviderInterface;
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
        RunnerCommands::class,
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

        $this->config = new Config();
        $this->application = $this->createApplication();
        $this->application->setAutoExit(false);
        $this->container = $this->createContainer($this->input, $this->output, $this->application, $this->config, $classLoader);

        $this->createConfiguration();

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
     */
    private function createConfiguration()
    {
        /** @var \Robo\ClassDiscovery\RelativeNamespaceDiscovery $discovery */
        $discovery = Robo::service('relativeNamespaceDiscovery');
        $discovery->setRelativeNamespace('TaskRunner\ConfigProviders')
            ->setSearchPattern('/.*ConfigProvider\.php$/');

        // Add default config provider classes. Setting extreme priorities so
        // that we are sure that the default config provider runs first and the
        // other two are running at the very end. However, in some very specific
        // circumstances, third-party config providers are abie to set
        // priorities, either higher or lower than these, and, as an effect,
        // they can override even these default config providers.
        $classes = [
            DefaultConfigProvider::class => 1500,
            LocalFileConfigProvider::class => -1000,
            FileFromEnvironmentConfigProvider::class => -1500,
        ];

        // Discover 3rd party config providers.
        foreach ($discovery->getClasses() as $class) {
            if (is_subclass_of($class, ConfigProviderInterface::class)) {
                $classes[$class] = $this->getConfigProviderPriority($class);
            }
        }

        // High priority modifiers run first.
        arsort($classes, SORT_NUMERIC);

        $configArray = [];
        foreach (array_keys($classes) as $class) {
            $class::provide($configArray);
        }

        // Resolve variables and import into config.
        $processor = (new ConfigProcessor())->add($configArray);
        $this->config->import($processor->export());
        // Keep the container in sync.
        $this->container->share('config', $this->config);
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
}
