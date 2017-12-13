<?php

namespace EC\OpenEuropa\TaskRunner\Commands;

use Robo\Common\ConfigAwareTrait;
use Robo\Tasks;

/**
 * Class DrupalCommands.
 *
 * @package EC\OpenEuropa\TaskRunner\Commands
 */
class DrupalCommands extends Tasks
{
    use ConfigAwareTrait;

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
        'root' => null,
        'base-url' => null,
        'site-name' => null,
        'site-mail' => null,
        'site-profile' => null,
        'site-update' => null,
        'site-locale' => null,
        'admin-name' => null,
        'admin-password' => null,
        'admin-mail' => null,
        'database-user' => null,
        'database-password' => null,
        'database-host' => null,
        'database-port' => null,
        'database-name' => null,
    ])
    {
        return $this->getDrush()
            ->arg('site-install')
            ->options([
                'site-name' => $options['site-name'],
                'site-mail' => $options['site-mail'],
                'locale' => $options['site-locale'],
                'account-mail' => $options['account-mail'],
                'account-name' => $options['account-name'],
                'account-pass' => $options['account-password'],
                'db-prefix' => $options['database-prefix'],
                'exclude' => $options['site-root'],
                'db-url' => sprintf(
                    "mysql://%s:%s@%s:%s/%s",
                    $options['database-user'],
                    $options['database-password'],
                    $options['database-host'],
                    $options['database-port'],
                    $options['database-name']
                ),
            ], '=');
    }

    /**
     * Get configured Drush task.
     *
     * @return \Robo\Task\Base\Exec
     *   Exec command.
     */
    protected function getDrush()
    {
        return $this->taskExec($this->getConfigValue('bin-drush'))
            ->option('-y')
            ->option('root', $this->getSiteRoot(), '=');
    }
}
