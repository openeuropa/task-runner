<?php

namespace EC\OpenEuropa\TaskRunner\Commands\Drupal;

use Consolidation\AnnotatedCommand\AnnotatedCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class SiteInstall.
 *
 * @package EC\OpenEuropa\TaskRunner\Commands\Drupal
 */
class SiteInstallCommand extends AnnotatedCommand
{
    /**
     * @command drupal:site-install
     *
     * @option site-name            Site name.
     * @option site-mail            Site mail.
     * @option site-profile         Installation profile
     * @option site-update          Whereas to enable the update module or not.
     * @option site-locale          Default site locale.
     * @option admin-name           Admin account name.
     * @option admin-password       Admin account password.
     * @option admin-mail           Admin email.
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
     */
    public function siteInstall(array $options = [
        'root'              => InputOption::VALUE_REQUIRED,
        'base-url'          => InputOption::VALUE_REQUIRED,
        'site-name'         => InputOption::VALUE_REQUIRED,
        'site-mail'         => InputOption::VALUE_REQUIRED,
        'site-profile'      => InputOption::VALUE_REQUIRED,
        'site-update'       => InputOption::VALUE_REQUIRED,
        'site-locale'       => InputOption::VALUE_REQUIRED,
        'admin-name'        => InputOption::VALUE_REQUIRED,
        'admin-password'    => InputOption::VALUE_REQUIRED,
        'admin-mail'        => InputOption::VALUE_REQUIRED,
        'database-user'     => InputOption::VALUE_REQUIRED,
        'database-password' => InputOption::VALUE_REQUIRED,
        'database-host'     => InputOption::VALUE_REQUIRED,
        'database-port'     => InputOption::VALUE_REQUIRED,
        'database-name'     => InputOption::VALUE_REQUIRED,
    ])
    {
        return print_r($options, true);
    }
}
