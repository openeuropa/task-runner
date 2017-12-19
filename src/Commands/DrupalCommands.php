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
    /**
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
}
