<?php

namespace OpenEuropa\TaskRunner\Commands;

use OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;
use OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;

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
     * @option database-type     Database type.
     * @option database-host     Database host.
     * @option database-port     Database port.
     * @option database-user     Database username.
     * @option database-password Database password.
     * @option database-name     Database name.
     * @option sites-subdir      Sites sub-directory.
     *
     * @aliases drupal:si,dsi
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function siteInstall(array $options = [
      'root'              => InputOption::VALUE_REQUIRED,
      'base-url'          => InputOption::VALUE_REQUIRED,
      'site-name'         => InputOption::VALUE_REQUIRED,
      'site-mail'         => InputOption::VALUE_REQUIRED,
      'site-profile'      => InputOption::VALUE_REQUIRED,
      'site-update'       => InputOption::VALUE_REQUIRED,
      'site-locale'       => InputOption::VALUE_REQUIRED,
      'account-name'      => InputOption::VALUE_REQUIRED,
      'account-password'  => InputOption::VALUE_REQUIRED,
      'account-mail'      => InputOption::VALUE_REQUIRED,
      'database-type'     => InputOption::VALUE_REQUIRED,
      'database-host'     => InputOption::VALUE_REQUIRED,
      'database-port'     => InputOption::VALUE_REQUIRED,
      'database-name'     => InputOption::VALUE_REQUIRED,
      'database-user'     => InputOption::VALUE_REQUIRED,
      'database-password' => InputOption::VALUE_REQUIRED,
      'sites-subdir'      => InputOption::VALUE_REQUIRED,
    ])
    {
        $drush = $this->getConfig()->get('runner.bin_dir').'/drush';
        $task = $this->taskDrush($drush)
          ->root($options['root'])
          ->siteName($options['site-name'])
          ->siteMail($options['site-mail'])
          ->locale($options['site-locale'])
          ->accountMail($options['account-mail'])
          ->accountName($options['account-name'])
          ->accountPassword($options['account-password'])
          ->databaseType($options['database-type'])
          ->databaseHost($options['database-host'])
          ->databasePort($options['database-port'])
          ->databaseUser($options['database-user'])
          ->databasePassword($options['database-password'])
          ->databaseName($options['database-name'])
          ->sitesSubdir($options['sites-subdir'])
          ->siteProfile($options['site-profile']);

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
     * Setup default Drupal settings file.
     *
     * This command will append settings specified at "drupal.settings" to the
     * current site's "default.settings.php" which, in turn, will be used
     * to generate the actual "settings.php" at installation time.
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
     * @command drupal:settings-setup
     *
     * @option root Drupal root.
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function settingsSetup(array $options = [
      'root' => InputOption::VALUE_REQUIRED,
    ])
    {
        return $this->collectionBuilder()->addTaskList([
            $this->taskAppendConfiguration($options['root'].'/sites/default/default.settings.php', $this->getConfig())->setConfigKey('drupal.settings'),
        ]);
    }
}
