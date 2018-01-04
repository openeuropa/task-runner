<?php

namespace EC\OpenEuropa\TaskRunner\Commands;

use Symfony\Component\Console\Input\InputOption;

/**
 * Class DrupalCommands.
 *
 * @package EC\OpenEuropa\TaskRunner\Commands
 */
class DrupalCommands extends BaseCommands
{
    use \NuvoleWeb\Robo\Task\Config\Php\loadTasks;

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
                'root' => './'.$options['root'],
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
     * Setup project.
     *
     * This command will create the necessary symlinks and scaffolding files.
     *
     * @command drupal:scaffold
     * @aliases ds
     *
     * @return \Robo\Collection\CollectionBuilder
     * @throws \Robo\Exception\TaskException
     */
    public function drupalScaffold()
    {
        $collection = $this->collectionBuilder();
//        $collection = $this->collectionBuilder()->addTaskList([
//          $this->taskFilesystemStack()->chmod($this->getSiteRoot() . '/sites', 0775, 0000, TRUE),
//          $this->taskFilesystemStack()->symlink($this->getProjectRoot(), $this->getSiteRoot() . '/sites/all/modules/' . $this->getProjectName()),
//          $this->taskWriteConfiguration($this->getSiteRoot() . '/sites/default/drushrc.php', $this->getConfig())->setConfigKey('drush'),
//          $this->taskAppendConfiguration($this->getSiteRoot() . '/sites/default/default.settings.php', $this->getConfig())->setConfigKey('settings'),
//        ]);

        if (file_exists('behat.yml.dist') || $this->isSimulating()) {
            $collection->addTask($this->taskExec($this->getBin('run'))->arg('setup:behat'));
        }

        if (file_exists('phpunit.xml.dist') || $this->isSimulating()) {
            $collection->addTask($this->taskExec($this->getBin('run'))->arg('setup:phpunit'));
        }

        return $collection;
    }
}
