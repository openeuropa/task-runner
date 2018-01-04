<?php

namespace EC\OpenEuropa\TaskRunner\Commands;

use EC\OpenEuropa\TaskRunner\Contract\ComposerAwareInterface;
use EC\OpenEuropa\TaskRunner\Traits\ComposerAwareTrait;
use Robo\Exception\TaskException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class DrupalCommands.
 *
 * @package EC\OpenEuropa\TaskRunner\Commands
 */
class DrupalCommands extends BaseCommands implements ComposerAwareInterface
{
    use ComposerAwareTrait;
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
     * Install target site.
     *
     * This command will install the target site using configuration values
     * provided in runner.yml.dist (overridable by runner.yml).
     *
     * @command drupal:site-install
     *
     * @option root                 Drupal root.
     * @option site-name            Site name.
     * @option site-mail            Site mail.
     * @option site-profile         Installation profile
     * @option site-update          Whereas to enable the update module or not.
     * @option site-locale          Default site locale.
     * @option account-name         Admin account name.
     * @option account-password     Admin account password.
     * @option account-mail         Admin email.
     * @option database-host        Database host.
     * @option database-port        Database port.
     * @option database-name        Database name.
     * @option database-user        Database username.
     * @option database-password    Database password.
     *
     * @aliases drupal:si,dsi
     *
     * @param array $options
     *
     * @return \Robo\Task\Base\Exec
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
        return $this->taskExec($this->getBin('drush'))
            ->option('-y')
            ->options([
                'root' => $options['root'],
                'site-name' => $options['site-name'],
                'site-mail' => $options['site-mail'],
                'locale' => $options['site-locale'],
                'account-mail' => $options['account-mail'],
                'account-name' => $options['account-name'],
                'account-pass' => $options['account-password'],
                'exclude' => $options['root'],
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
     * - For Drupal 8: make sure that Twig cache is disabled on ./web/sites/development.services.yml.
     * - For Drupal 8: Setup local development settings at ./web/sites/default/settings.local.php.
     *
     * Configuration content written or appended to setting files above can be
     * modified by tweaking configuration variable in your "runner.yml" file.
     *
     * For more information check the default "config/runner.yml" file at:
     *
     * > drupal:
     * >   ...
     * >   drush:
     * >     ...
     * >   settings:
     * >     ...
     *
     * @command drupal:component-scaffold
     *
     * @aliases drupal:cs,dcs
     *
     * @return \Robo\Collection\CollectionBuilder
     *
     * @throws \Robo\Exception\TaskException
     */
    public function componentScaffold()
    {
        $collection = $this->collectionBuilder();

        $collection->addTaskList([
            $this->taskFilesystemStack()->chmod($this->getSiteRoot().'/sites', 0775, 0000, true),
            $this->taskFilesystemStack()->symlink('.', $this->getExtensionDirectory()),
            $this->taskWriteConfiguration($this->getSiteRoot().'/sites/default/drushrc.php', $this->getConfig())
              ->setConfigKey('drupal.drush'),
            $this->taskAppendConfiguration($this->getSiteRoot().'/sites/default/default.settings.php', $this->getConfig())
              ->setConfigKey('drupal.settings'),
        ]);

        if (file_exists('behat.yml.dist') || $this->isSimulating()) {
            $collection->addTask($this->taskExec($this->getBin('run'))->arg('setup:behat'));
        }

        if (file_exists('phpunit.xml.dist') || $this->isSimulating()) {
            $collection->addTask($this->taskExec($this->getBin('run'))->arg('setup:phpunit'));
        }

        return $collection;
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
     * Returns extension directory based on Drupal core.
     *
     * @return string
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function getExtensionDirectory()
    {
        $root = $this->getSiteRoot();
        $name = $this->getProjectName();

        switch ($this->getProjectType()) {
            case 'drupal-module':
                $directory = 'modules';
                break;
            case 'drupal-theme':
                $directory = 'themes';
                break;
            default:
                throw new TaskException($this, "Component scaffolding only supports modules and themes.");
        }

        if ($this->getConfig()->get('drupal.core') === "7") {
            return "{$root}/sites/all/{$directory}/custom/{$name}";
        }

        return "{$root}/{$directory}/custom/{$name}";
    }
}
