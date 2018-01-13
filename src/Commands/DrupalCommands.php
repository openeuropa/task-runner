<?php

namespace EC\OpenEuropa\TaskRunner\Commands;

use EC\OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use EC\OpenEuropa\TaskRunner\Contract\FilesystemAwareInterface;
use Robo\Exception\TaskException;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;
use EC\OpenEuropa\TaskRunner\Tasks as TaskRunnerTasks;
use EC\OpenEuropa\TaskRunner\Traits as TaskRunnerTraits;
use NuvoleWeb\Robo\Task as NuvoleWebTasks;

/**
 * Class DrupalCommands.
 *
 * @package EC\OpenEuropa\TaskRunner\Commands
 */
class DrupalCommands extends BaseCommands implements ComposerAwareInterface, FilesystemAwareInterface
{
    use TaskRunnerTraits\ComposerAwareTrait;
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
     * Command initialization.
     *
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     *
     * @hook command-event *
     */
    public function initializeDrupalRuntimeConfiguration(ConsoleCommandEvent $event)
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
     * This command will install the target site using configuration values
     * provided in runner.yml.dist (overridable by runner.yml).
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
     * @option database-host     Database host.
     * @option database-port     Database port.
     * @option database-name     Database name.
     * @option database-user     Database username.
     * @option database-password Database password.
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
      'database-user'     => InputOption::VALUE_REQUIRED,
      'database-password' => InputOption::VALUE_REQUIRED,
      'database-host'     => InputOption::VALUE_REQUIRED,
      'database-port'     => InputOption::VALUE_REQUIRED,
      'database-name'     => InputOption::VALUE_REQUIRED,
    ])
    {
        $command = $this->getConfig()->get('runner.bin_dir').'/drush';
        $task = $this->taskDrush($command)
          ->root($options['root'])
          ->siteName($options['site-name'])
          ->siteMail($options['site-mail'])
          ->locale($options['site-locale'])
          ->accountMail($options['account-mail'])
          ->accountName($options['account-name'])
          ->accountPassword($options['account-password'])
          ->databaseUser($options['database-user'])
          ->databasePassword($options['database-password'])
          ->databaseHost($options['database-host'])
          ->databasePort($options['database-port'])
          ->databaseName($options['database-name'])
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
     * Add post install commands in your runner.yaml file under "drupal.post_install"
     * as shown below:
     *
     * > drupal:
     * >   ...
     * >   post_install:
     * >     - ./vendor/bin/drush en views -y
     * >     - ./vendor/bin/drush cr
     *
     * Post install commands will be automatically executed after installing the site.
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
     * Add post install commands in your runner.yaml file under "drupal.pre_install"
     * as shown below:
     *
     * > drupal:
     * >   ...
     * >   pre_install:
     * >     - ./vendor/bin/drush en views -y
     * >     - ./vendor/bin/drush cr
     *
     * Pre-install commands will be automatically executed after installing the site.
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
     * Write Drush configuration files to the specified directory.
     *
     * @command drupal:setup-drush
     *
     * @option root Drupal root.
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function setupDrush(array $options = [
      'root' => InputOption::VALUE_REQUIRED,
    ])
    {
        $config = $this->getConfig();
        $yaml = Yaml::dump($config->get('drupal.drush'));

        return $this->collectionBuilder()->addTaskList([
            $this->taskWriteConfiguration($options['root'].'/sites/default/drushrc.php', $config)->setConfigKey('drupal.drush'),
            $this->taskWriteToFile($options['root'].'/sites/default/drush.yml')->text($yaml),
        ]);
    }

    /**
     * Write Drupal site configuration files to the specified directory.
     *
     * @command drupal:setup-settings
     *
     * @option root Drupal root.
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function setupSettings(array $options = [
      'root' => InputOption::VALUE_REQUIRED,
    ])
    {
        return $this->collectionBuilder()->addTaskList([
            $this->taskAppendConfiguration($options['root'].'/sites/default/default.settings.php', $this->getConfig())->setConfigKey('drupal.settings'),
        ]);
    }
}
