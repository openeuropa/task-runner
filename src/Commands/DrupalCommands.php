<?php

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
 * Class DrupalCommands.
 *
 * @package OpenEuropa\TaskRunner\Commands
 */
class DrupalCommands extends AbstractCommands implements FilesystemAwareInterface
{
    use TaskRunnerTraits\ConfigurationTokensTrait;
    use TaskRunnerTraits\FilesystemAwareTrait;
    use TaskRunnerTasks\CollectionFactory\loadTasks;
    use TaskRunnerTasks\Drush\loadTasks;
    use NuvoleWebTasks\Config\Php\loadTasks;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__.'/../../config/commands/drupal.yml';
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
     */
    public function validateSiteInstall(CommandData $commandData)
    {
        $input = $commandData->input();
        $siteDirectory = implode('/', [
            getcwd(),
            $input->getOption('root'),
            'sites',
            $input->getOption('sites-subdir'),
        ]);

        // Check if required files/folders exist and they are writable.
        $requiredFiles = [$siteDirectory, $siteDirectory.'/settings.php'];
        foreach ($requiredFiles as $requiredFile) {
            if (file_exists($requiredFile) && !is_writable($requiredFile)) {
                throw new \Exception(sprintf('The file/folder %s must be writable for installation to continue.', $requiredFile));
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
     * @option root              Drupal root.
     * @option site-name         Site name.
     * @option site-mail         Site mail.
     * @option site-profile      Installation profile
     * @option site-update       Whereas to enable the update module or not.
     * @option site-locale       Default site locale.
     * @option account-name      Admin account name.
     * @option account-password  Admin account password.
     * @option account-mail      Admin email.
     * @option database-type     Deprecated, use "database-scheme"
     * @option database-scheme   Database scheme.
     * @option database-host     Database host.
     * @option database-port     Database port.
     * @option database-name     Database name.
     * @option database-user     Database username.
     * @option database-password Database password.
     * @option sites-subdir      Sites sub-directory.
     * @option config-dir        Config export directory.
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
    ])
    {
        if ($options['database-type']) {
            $this->io()->warning("Option 'database-type' is deprecated and it will be removed in 1.0.0. Use 'database-scheme' instead.");
            $options['database-scheme'] = $options['database-type'];
        }

        $drush = $this->getConfig()->get('runner.bin_dir').'/drush';
        $task  = $this->taskDrush($drush)
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

        if (!empty($options['config-dir'])) {
            $task->setConfigDir($options['config-dir']);
        }

        return $this->collectionBuilder()->addTaskList([
            $this->sitePreInstall(),
            $task->siteInstall(),
            $this->sitePostInstall(),
        ]);
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
     * @return \Robo\Contract\TaskInterface
     */
    public function sitePostInstall()
    {
        $tasks = $this->getConfig()->get('drupal.post_install', []);

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
     * @return \Robo\Contract\TaskInterface
     */
    public function sitePreInstall()
    {
        $tasks = $this->getConfig()->get('drupal.pre_install', []);

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
            $this->taskWriteConfiguration($options['root'].'/sites/default/drushrc.php', $config)->setConfigKey('drupal.drush'),
            $this->taskWriteToFile($options['config-dir'].'/drush.yml')->text($yaml),
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
    ])
    {
        $settings_default_path = $options['root'] . '/sites/' . $options['sites-subdir'] . '/default.settings.php';
        $settings_path = $options['root'] . '/sites/' . $options['sites-subdir'] . '/settings.php';
        $settings_override_path = $options['root'] . '/sites/' . $options['sites-subdir'] . '/' . $options['settings-override-file'];

        return $this->collectionBuilder()->addTaskList([
            $this->taskFilesystemStack()->copy($settings_default_path, $settings_path, ($options['force'] === true) ? $options['force'] : false),
            $this->taskWriteToFile($settings_path)->append()->lines([
                "if (file_exists(\$app_root . '/' . \$site_path . '/" . $options['settings-override-file'] . "')) {",
                "  include \$app_root . '/' . \$site_path . '/" . $options['settings-override-file'] . "';",
                "}"
            ]),
            $this->taskWriteConfiguration($settings_override_path, $this->getConfig())->setConfigKey('drupal.settings'),
        ]);
    }
}
