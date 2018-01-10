<?php

namespace EC\OpenEuropa\TaskRunner\Commands;

use EC\OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use EC\OpenEuropa\TaskRunner\Traits\ComposerAwareTrait;
use EC\OpenEuropa\TaskRunner\Traits\ConfigurationTokensTrait;
use EC\OpenEuropa\TaskRunner\Traits\PathUtilitiesTrait;
use Robo\Exception\TaskException;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DrupalCommands.
 *
 * @package EC\OpenEuropa\TaskRunner\Commands
 */
class DrupalCommands extends BaseCommands implements ComposerAwareInterface
{
    use ComposerAwareTrait;
    use ConfigurationTokensTrait;
    use PathUtilitiesTrait;
    use \NuvoleWeb\Robo\Task\Config\Php\loadTasks;

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile()
    {
        return __DIR__.'/../../config/commands/drupal.yml';
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @hook init
     */
    public function init(InputInterface $input)
    {
        $this->getComposer()->setWorkingDir($input->getOption('working-dir'));
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
     *
     * @throws \Robo\Exception\TaskException
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
        $installTask = $this->taskExec($this->getBin('drush'))
          ->option('-y')
          ->rawArg("--root=$(pwd)/".$options['root'])
          ->options([
              'site-name' => $options['site-name'],
              'site-mail' => $options['site-mail'],
              'locale' => $options['site-locale'],
              'account-mail' => $options['account-mail'],
              'account-name' => $options['account-name'],
              'account-pass' => $options['account-password'],
              'db-url' => sprintf(
                  "mysql://%s:%s@%s:%s/%s",
                  $options['database-user'],
                  $options['database-password'],
                  $options['database-host'],
                  $options['database-port'],
                  $options['database-name']
              ),
          ], '=')
          ->arg('site-install')
          ->arg($options['site-profile']);

        return $this->collectionBuilder()->addTaskList([
            $installTask,
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
     * @aliases drupal:spi,dspi
     *
     * @return \Robo\Contract\TaskInterface
     */
    public function sitePostInstall()
    {
        $commands = $this->getConfig()->get('drupal.post_install');
        if (!empty($commands)) {
            $taskStack = $this->taskExecStack();

            foreach ($commands as $command) {
                $taskStack->exec($command);
            }

            return $taskStack;
        }

        return $this->taskExec('');
    }

    /**
     * Setup local Drupal site development copy.
     *
     * This command will create the necessary symlinks and scaffolding files
     * given that a fully built Drupal site is available at ${drupal.root}.
     *
     * Running this command will:
     *
     * - Make Drupal's "./${drupal.root}/sites/default" directory writable.
     * - Symlink custom modules and themes to the proper build location.
     * - Setup Drupal settings at ./web/sites/default/settings.local.php.
     * - Setup default Drush configuration files in "./${drupal.root}/sites/default/".
     *
     * Configuration content written or appended to setting files above can be
     * modified by tweaking configuration variable in your "runner.yml" file.
     *
     * @command drupal:site-setup
     *
     * @aliases drupal:site-scaffold,drupal:ss,dss
     *
     * @option root Drupal root.
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     *
     * @throws \Robo\Exception\TaskException
     */
    public function siteSetup(array $options = [
      'root' => InputOption::VALUE_REQUIRED,
    ])
    {
        return $this->setupSiteBuild($options['root'], $this->getConfig()->get('drupal.setup.symlink'));
    }

    /**
     * Scaffold Drupal component development.
     *
     * This command will create the necessary symlinks and scaffolding files for
     * developing Drupal modules and themes, assuming that a fully built Drupal
     * site is available at ${drupal.root}.
     *
     * Running this command will:
     *
     * - Prepare a custom project directory using composer.json project name.
     * - Make Drupal's "./${drupal.root}/sites/default" directory writable.
     * - Symlink the root of your project at "./${drupal.root}/modules|themes/custom/PROJECT_NAME (or its Drupal 7 variant).
     * - Setup default Drush configuration files in "./${drupal.root}/sites/default/".
     * - Exclude ${drupal.root} and "vendor" directories in "./${drupal.root}/sites/default/settings.default.php".
     * - Setup Drupal settings at ./web/sites/default/settings.local.php.
     * - Setup default Drush configuration files in "./${drupal.root}/sites/default/".
     *
     * Configuration content written or appended to setting files above can be
     * modified by tweaking configuration variable in your "runner.yml" file.
     *
     * @command drupal:component-setup
     *
     * @aliases drupal:component-scaffold,drupal:cs,dcs
     *
     * @return \Robo\Collection\CollectionBuilder
     *
     * @throws \Robo\Exception\TaskException
     */
    public function componentSetup()
    {
        $symlinks = [];
        $symlinks[] = ['from' => '.', 'to' => $this->getExtensionPath()];

        return $this->setupSiteBuild($this->getSiteRoot(), $symlinks);
    }

    /**
     * @return mixed
     */
    protected function getSiteRoot()
    {
        return $this->getConfig()->get('drupal.root', '.');
    }

    /**
     * @return string
     */
    protected function getProjectName()
    {
        return $this->getComposer()->getProject();
    }

    /**
     * @return string
     */
    protected function getProjectType()
    {
        return $this->getComposer()->getType();
    }

    /**
     * Returns extension root based on Drupal core.
     *
     * @return string
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function getExtensionPath()
    {
        $basePath = ($this->getConfig()->get('drupal.core') === "7") ? "sites/all/" : '';

        switch ($this->getProjectType()) {
            case 'drupal-module':
                return "{$basePath}modules/custom/".$this->getProjectName();
            case 'drupal-theme':
                return "{$basePath}themes/custom/".$this->getProjectName();
            default:
                throw new TaskException($this, "Component scaffolding only supports modules and themes.");
        }
    }

    /**
     * Write Drush configuration files to the specified directory.
     *
     * @param string $path
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    protected function writeDrushConfiguration($path)
    {
        $config = $this->getConfig();
        $yaml = Yaml::dump($config->get('drupal.drush'));

        return $this->collectionBuilder()->addTaskList([
            $this->taskWriteConfiguration($path.'/drushrc.php', $config)->setConfigKey('drupal.drush'),
            $this->taskWriteToFile($path.'/drush.yml')->text($yaml),
        ]);
    }

    /**
     * Write Drupal site configuration files to the specified directory.
     *
     * @param string $path
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    protected function writeSiteConfiguration($path)
    {
        return $this->collectionBuilder()->addTaskList([
            $this->taskAppendConfiguration($path.'/default.settings.php', $this->getConfig())->setConfigKey('drupal.settings'),
        ]);
    }

    /**
     * Setup local site build, given its relative root and a list of symlinks.
     *
     * @todo: Turn this into an actual task.
     *
     * @param string $root
     * @param array  $symlinks
     *
     * @return \Robo\Collection\CollectionBuilder
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function setupSiteBuild($root, $symlinks = [])
    {
        $collection = $this->collectionBuilder();

        foreach ($symlinks as $symlink) {
            if (is_dir($symlink['from']) || $this->isSimulating()) {
                $destination = $root.'/'.$symlink['to'];
                $source = $this->walkPath($destination, $symlink['from']);
                $collection->addTask($this->taskFilesystemStack()->symlink($source, $destination));
            }
        }

        $collection->addTaskList([
            $this->taskFilesystemStack()->chmod($this->getSiteRoot().'/sites', 0775, 0000, true),
            $this->writeDrushConfiguration($this->getSiteRoot().'/sites/default'),
            $this->writeSiteConfiguration($this->getSiteRoot().'/sites/default'),
        ]);

        if (file_exists('behat.yml.dist') || $this->isSimulating()) {
            $collection->addTask($this->taskExec($this->getBin('run'))->arg('setup:behat'));
        }

        if (file_exists('phpunit.xml.dist') || $this->isSimulating()) {
            $collection->addTask($this->taskExec($this->getBin('run'))->arg('setup:phpunit'));
        }

        return $collection;
    }
}
