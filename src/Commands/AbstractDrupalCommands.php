<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;
use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Base class for Drupal commands.
 */
abstract class AbstractDrupalCommands extends AbstractCommands implements FilesystemAwareInterface
{
    use TaskRunnerTraits\ConfigurationTokensTrait;
    use TaskRunnerTraits\FilesystemAwareTrait;
    use TaskRunnerTasks\CollectionFactory\loadTasks;
    use TaskRunnerTasks\Drush\loadTasks;
    use NuvoleWebTasks\Config\Php\loadTasks;

    /**
     * @return \OpenEuropa\TaskRunner\Commands\Drupal7Commands|\OpenEuropa\TaskRunner\Commands\Drupal8Commands
     */
    public function getDrupal()
    {
        return $this->getConfig()->get('drupal.core') === 7 ?
        new Drupal7Commands() :
        new Drupal8Commands();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__ . '/../../config/commands/drupal.yml';
    }

    /**
     * {@inheritdoc}
     */
    public function getValuelessConfigurationKeys()
    {
        return [
            'drupal:site-install' => [
                'existing-config' => 'drupal.site.existing_config',
                'skip-permissions-setup' => 'drupal.site.skip_permissions_setup',
            ],
        ];
    }

    /**
     * Set runtime configuration values.
     *
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     *
     * @hook command-event *
     */
    public function setRuntimeConfig(ConsoleCommandEvent $event)
    {
        $root = $this->getConfig()->get('drupal.root');
        $rootFullPath = realpath($root);
        if ($rootFullPath) {
            $this->getConfig()->set('drupal.root_absolute', $rootFullPath);
        }
    }

    /**
     * @hook validate drupal:site-install
     *
     * @param CommandData $commandData
     * @throws \Exception
     *   Thrown when the settings file or its containing folder does not exist
     *   or is not writeable.
     */
    public function validateSiteInstall(CommandData $commandData)
    {
        $input = $commandData->input();

        // Validate if permissions will be set up.
        if (!$input->hasOption('skip-permissions-setup') || !$input->getOption('skip-permissions-setup')) {
            return;
        }

        $siteDirectory = implode('/', [
            getcwd(),
            $input->getOption('root'),
            'sites',
            $input->getOption('sites-subdir'),
        ]);

        // Check if required files/folders exist and they are writable.
        $requiredFiles = [$siteDirectory, $siteDirectory . '/settings.php'];
        foreach ($requiredFiles as $requiredFile) {
            if (file_exists($requiredFile) && !is_writable($requiredFile)) {
                $message = 'The file/folder %s must be writable for installation to continue.';
                throw new \Exception(sprintf($message, $requiredFile));
            }
        }
    }

    /**
     * Install target site.
     *
     * This command will install a target Drupal site using configuration values
     * provided in local runner.yml.dist/runner.yml files.
     *
     * @command drupal:site-install
     *
     * @option root                   Drupal root.
     * @option site-name              Site name.
     * @option site-mail              Site mail.
     * @option site-profile           Installation profile
     * @option site-update            Whereas to enable the update module or not.
     * @option site-locale            Default site locale.
     * @option account-name           Admin account name.
     * @option account-password       Admin account password.
     * @option account-mail           Admin email.
     * @option database-type          Deprecated, use "database-scheme"
     * @option database-scheme        Database scheme.
     * @option database-host          Database host.
     * @option database-port          Database port.
     * @option database-name          Database name.
     * @option database-user          Database username.
     * @option database-password      Database password.
     * @option sites-subdir           Sites sub-directory.
     * @option config-dir             Deprecated, use "existing-config" for Drupal 8.6 and higher.
     * @option existing-config        Whether existing config should be imported during installation.
     * @option skip-permissions-setup Whether to skip making the settings file and folder writable during installation.
     *
     * @aliases drupal:si,dsi
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function siteInstall(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'base-url' => InputOption::VALUE_REQUIRED,
        'site-name' => InputOption::VALUE_REQUIRED,
        'site-mail' => InputOption::VALUE_REQUIRED,
        'site-profile' => InputOption::VALUE_REQUIRED,
        'site-update' => InputOption::VALUE_REQUIRED,
        'site-locale' => InputOption::VALUE_REQUIRED,
        'account-name' => InputOption::VALUE_REQUIRED,
        'account-password' => InputOption::VALUE_REQUIRED,
        'account-mail' => InputOption::VALUE_REQUIRED,
        'database-type' => InputOption::VALUE_REQUIRED,
        'database-scheme' => InputOption::VALUE_REQUIRED,
        'database-user' => InputOption::VALUE_REQUIRED,
        'database-password' => InputOption::VALUE_REQUIRED,
        'database-host' => InputOption::VALUE_REQUIRED,
        'database-port' => InputOption::VALUE_REQUIRED,
        'database-name' => InputOption::VALUE_REQUIRED,
        'sites-subdir' => InputOption::VALUE_REQUIRED,
        'config-dir' => InputOption::VALUE_REQUIRED,
        'existing-config' => false,
        'skip-permissions-setup' => false,
    ])
    {
        if ($options['database-type']) {
            $message = "'database-type' is deprecated and will be removed in 1.0.0. Use 'database-scheme' instead.";
            $this->io()->warning($message);
            $options['database-scheme'] = $options['database-type'];
        }
        if ($options['config-dir']) {
            $this->io()->warning("The 'config-dir' option is deprecated. Use 'existing-config' instead.");
            $options['existing-config'] = true;
        }

        $drush = $this->getConfig()->get('runner.bin_dir') . '/drush';
        $task = $this->taskDrush($drush)
            ->root($options['root'])
            ->siteName($options['site-name'])
            ->siteMail($options['site-mail'])
            ->locale($options['site-locale'])
            ->accountMail($options['account-mail'])
            ->accountName($options['account-name'])
            ->accountPassword($options['account-password'])
            ->databaseScheme($options['database-scheme'])
            ->databaseUser($options['database-user'])
            ->databasePassword($options['database-password'])
            ->databaseHost($options['database-host'])
            ->databasePort($options['database-port'])
            ->databaseName($options['database-name'])
            ->sitesSubdir($options['sites-subdir'])
            ->siteProfile($options['site-profile']);

        $task->setGenerateDbUrl($this->getConfig()->get('drupal.site.generate_db_url'));

        if (!empty($options['existing-config'])) {
            $task->setExistingConfig($options['existing-config']);
        }

        // Define collection of tasks.
        $collection = [
            $this->sitePreInstall($options),
        ];
        if (!$options['skip-permissions-setup']) {
            $collection[] = $this->permissionsSetup($options);
        }
        $collection[] = $task->siteInstall();
        $collection[] = $this->sitePostInstall($options);

        return $this->collectionBuilder()->addTaskList($collection);
    }

    /**
     * Run Drupal post-install commands.
     *
     * Commands have to be listed under the "drupal.post_install" property in
     * your local runner.yml.dist/runner.yml files, as shown below:
     *
     * > drupal:
     * >   ...
     * >   post_install:
     * >     - "./vendor/bin/drush en views -y"
     * >     - { task: "process", source: "behat.yml.dist", destination: "behat.yml" }
     *
     * Post-install commands are automatically executed after installing the site
     * when running "drupal:site-install".
     *
     * @command drupal:site-post-install
     *
     * @option root
     *   The Drupal root. All occurrences of "!root" in the post-install
     *   string-only commands will be substituted with this value.
     *
     * @return \Robo\Contract\TaskInterface
     */
    public function sitePostInstall(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = $this->getConfig()->get('drupal.post_install', []);
        $this->processPrePostInstallCommands($tasks, [
            '!root' => $options['root'],
        ]);

        return $this->taskCollectionFactory($tasks);
    }

    /**
     * Run Drupal pre-install commands.
     *
     * Commands have to be listed under the "drupal.pre_install" property in
     * your local runner.yml.dist/runner.yml files, as shown below:
     *
     * > drupal:
     * >   ...
     * >   pre_install:
     * >     - { task: "symlink", from: "../libraries", to: "${drupal.root}/libraries" }
     * >     - { task: "process", source: "behat.yml.dist", destination: "behat.yml" }
     *
     * Pre-install commands are automatically executed before installing the site
     * when running "drupal:site-install".
     *
     * @command drupal:site-pre-install
     *
     * @option root
     *   The Drupal root. All occurrences of "!root" in the pre-install
     *   string-only commands will be substituted with this value.
     *
     * @return \Robo\Contract\TaskInterface
     */
    public function sitePreInstall(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
    ])
    {
        $tasks = $this->getConfig()->get('drupal.pre_install', []);
        $this->processPrePostInstallCommands($tasks, [
            '!root' => $options['root'],
        ]);

        return $this->taskCollectionFactory($tasks);
    }

    /**
     * Write Drush configuration files to given directories.
     *
     * Works for both Drush 8 and 9, by default it will:
     *
     * - Generate a Drush 9 configuration file at "${drupal.root}/drush/drush.yml"
     * - Generate a Drush 8 configuration file at "${drupal.root}/sites/all/default/drushrc.php"
     *
     * Configuration file contents can be customized by editing "drupal.drush"
     * values in your local runner.yml.dist/runner.yml, as shown below:
     *
     * > drupal:
     * >   drush:
     * >     options:
     * >       ignored-directories: "${drupal.root}"
     * >       uri: "${drupal.base_url}"
     *
     * @command drupal:drush-setup
     *
     * @option root         Drupal root.
     * @option config-dir   Directory where to store Drush 9 configuration file.
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function drushSetup(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'config-dir' => InputOption::VALUE_REQUIRED,
    ])
    {
        $config = $this->getConfig();
        $yaml = Yaml::dump($config->get('drupal.drush'));

        return $this->collectionBuilder()->addTaskList([
            $this->taskWriteConfiguration($options['root'] . '/sites/default/drushrc.php', $config)
                ->setConfigKey('drupal.drush'),
            $this->taskWriteToFile($options['config-dir'] . '/drush.yml')->text($yaml),
        ]);
    }

    /**
     * Setup Drupal settings overrides.
     *
     * This command will:
     *
     * - Copy "default.settings.php" to "settings.php", which will be overridden if existing
     * - Append to "settings.php" an include operation for a "settings.override.php" file
     * - Write settings specified at "drupal.settings" in "settings.override.php"
     *
     * Default settings can be customized in your local runner.yml.dist/runner.yml
     * as shown below:
     *
     * > drupal:
     * >   settings:
     * >     config_directories:
     * >       sync: '../config/sync'
     * >       prod: '../config/prod'
     *
     * The settings override file name can be changed in the Task Runner
     * configuration by setting the "drupal.site.settings_override_file" property.
     *
     * @command drupal:settings-setup
     *
     * @option root                     Drupal root.
     * @option sites-subdir             Drupal site subdirectory.
     * @option settings-override-file   Drupal site settings override filename.
     * @option force                    Drupal force generation of a new settings.php.
     * @option skip-permissions-setup   Drupal skip permissions setup.
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function settingsSetup(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'sites-subdir' => InputOption::VALUE_REQUIRED,
        'settings-override-file' => InputOption::VALUE_REQUIRED,
        'force' => false,
        'skip-permissions-setup' => false,
    ])
    {
        $base_path = $options['root'] . '/sites/' . $options['sites-subdir'] . '/';
        $settings_default_path = $base_path . 'default.settings.php';
        $settings_path = $base_path . 'settings.php';
        $settings_override_path = $base_path . $options['settings-override-file'];

        // Save the filename of the override file in a single variable to use it
        // in the heredoc variable $custom_config hereunder.
        $settings_override_filename = $options['settings-override-file'];

        $custom_config = $this->getDrupal()->getSettingsSetupAddendum($settings_override_filename);

        $collection = [];

        if (true === (bool) $options['force'] || !file_exists($settings_path)) {
            $collection[] = $this->taskWriteToFile($settings_default_path)->append()->lines([$custom_config]);
            $collection[] = $this->taskFilesystemStack()->copy($settings_default_path, $settings_path, true);
        }

        $collection[] = $this->taskWriteConfiguration(
            $settings_override_path,
            $this->getConfig()
        )->setConfigKey('drupal.settings');

        if (!$options['skip-permissions-setup']) {
            $collection[] = $this->permissionsSetup($options);
        }

        return $this->collectionBuilder()->addTaskList($collection);
    }

    /**
     * Setup Drupal permissions.
     *
     * This command will set the necessary permissions on the default folder.
     *
     * @command drupal:permissions-setup
     *
     * @option root                     Drupal root.
     * @option sites-subdir             Drupal site subdirectory.
     * @option skip-permissions-setup   Drupal skip permissions setup.
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function permissionsSetup(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'sites-subdir' => InputOption::VALUE_REQUIRED,
    ])
    {
        $subdirPath = $options['root'] . '/sites/' . $options['sites-subdir'];

        // Define collection of tasks.
        $collection = [
            // Note that the chmod() method takes decimal values.
            $this->taskFilesystemStack()->chmod($subdirPath, octdec(775), 0000, true),
        ];

        if (file_exists($subdirPath . '/settings.php')) {
            // Note that the chmod() method takes decimal values.
            $collection[] = $this->taskFilesystemStack()->chmod($subdirPath . '/settings.php', octdec(664));
        }

        return $this->collectionBuilder()->addTaskList($collection);
    }

    /**
     * Process pre and post install string-only commands by replacing given tokens.
     *
     * @param array $commands
     *   List of commands.
     * @param array $tokens
     *   Replacement key-value tokens.
     */
    protected function processPrePostInstallCommands(array &$commands, array $tokens)
    {
        foreach ($commands as $key => $value) {
            if (is_string($value)) {
                $commands[$key] = str_replace(array_keys($tokens), array_values($tokens), $value);
            }
        }
    }
}
