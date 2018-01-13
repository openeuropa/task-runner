<?php

namespace EC\OpenEuropa\TaskRunner\Tasks\Drush;

use Robo\Common\ConfigAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Task\Base\Exec;

/**
 * Class Drush
 *
 * @SuppressWarnings(PHPMD)
 *
 * @package EC\OpenEuropa\TaskRunner\Tasks\Drush
 */
class Drush extends Exec implements ConfigAwareInterface
{
    use ConfigAwareTrait;

    protected $root = '';
    protected $locale = '';
    protected $siteName = '';
    protected $siteMail = '';
    protected $siteLocale = '';
    protected $siteProfile = '';
    protected $accountMail = '';
    protected $accountName = '';
    protected $accountPassword = '';
    protected $databaseUser = '';
    protected $databasePassword = '';
    protected $databaseHost = '';
    protected $databasePort = '';
    protected $databaseName = '';

    /**
     * Build Drush site install command.
     *
     * @return $this
     */
    public function siteInstall()
    {
        return $this
          ->option('-y')
          ->rawArg("--root=$(pwd)/".$this->root)
          ->options([
              'site-name' => $this->siteName,
              'site-mail' => $this->siteMail,
              'locale' => $this->locale,
              'account-mail' => $this->accountMail,
              'account-name' => $this->accountName,
              'account-pass' => $this->accountPassword,
              'db-url' => sprintf(
                  "mysql://%s:%s@%s:%s/%s",
                  $this->databaseUser,
                  $this->databasePassword,
                  $this->databaseHost,
                  $this->databasePort,
                  $this->databaseName
              ),
          ], '=')
          ->arg('site-install')
          ->arg($this->siteProfile);
    }

    /**
     * @param mixed $root
     *
     * @return Drush
     */
    public function root($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @param mixed $locale
     *
     * @return Drush
     */
    public function locale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @param mixed $siteName
     *
     * @return Drush
     */
    public function siteName($siteName)
    {
        $this->siteName = $siteName;

        return $this;
    }

    /**
     * @param mixed $siteMail
     *
     * @return Drush
     */
    public function siteMail($siteMail)
    {
        $this->siteMail = $siteMail;

        return $this;
    }

    /**
     * @param mixed $siteLocale
     *
     * @return Drush
     */
    public function siteLocale($siteLocale)
    {
        $this->siteLocale = $siteLocale;

        return $this;
    }

    /**
     * @param mixed $siteProfile
     *
     * @return Drush
     */
    public function siteProfile($siteProfile)
    {
        $this->siteProfile = $siteProfile;

        return $this;
    }

    /**
     * @param mixed $accountMail
     *
     * @return Drush
     */
    public function accountMail($accountMail)
    {
        $this->accountMail = $accountMail;

        return $this;
    }

    /**
     * @param mixed $accountName
     *
     * @return Drush
     */
    public function accountName($accountName)
    {
        $this->accountName = $accountName;

        return $this;
    }

    /**
     * @param mixed $accountPassword
     *
     * @return Drush
     */
    public function accountPassword($accountPassword)
    {
        $this->accountPassword = $accountPassword;

        return $this;
    }

    /**
     * @param mixed $databaseUser
     *
     * @return Drush
     */
    public function databaseUser($databaseUser)
    {
        $this->databaseUser = $databaseUser;

        return $this;
    }

    /**
     * @param mixed $databasePassword
     *
     * @return Drush
     */
    public function databasePassword($databasePassword)
    {
        $this->databasePassword = $databasePassword;

        return $this;
    }

    /**
     * @param mixed $databaseHost
     *
     * @return Drush
     */
    public function databaseHost($databaseHost)
    {
        $this->databaseHost = $databaseHost;

        return $this;
    }

    /**
     * @param mixed $databasePort
     *
     * @return Drush
     */
    public function databasePort($databasePort)
    {
        $this->databasePort = $databasePort;

        return $this;
    }

    /**
     * @param mixed $databaseName
     *
     * @return Drush
     */
    public function databaseName($databaseName)
    {
        $this->databaseName = $databaseName;

        return $this;
    }
}
