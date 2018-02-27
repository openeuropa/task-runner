<?php

namespace OpenEuropa\TaskRunner\Tasks\Drush;

use Robo\Common\ConfigAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Task\Base\Exec;

/**
 * Class Drush
 *
 * @SuppressWarnings(PHPMD)
 *
 * @package OpenEuropa\TaskRunner\Tasks\Drush
 */
class Drush extends Exec
{
    protected $root = '';
    protected $locale = '';
    protected $siteName = '';
    protected $siteMail = '';
    protected $siteLocale = '';
    protected $siteProfile = '';
    protected $accountMail = '';
    protected $accountName = '';
    protected $accountPassword = '';
    protected $databaseType = '';
    protected $databaseHost = '';
    protected $databasePort = '';
    protected $databaseUser = '';
    protected $databasePassword = '';
    protected $databaseName = '';
    protected $sitesSubdir = '';

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
              'sites-subdir' => $this->sitesSubdir,
              'db-url' => sprintf(
                  '%s://%s:%s@%s:%s/%s',
                  $this->databaseType,
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
     * @param string $root
     *
     * @return Drush
     */
    public function root($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return Drush
     */
    public function locale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @param string $siteName
     *
     * @return Drush
     */
    public function siteName($siteName)
    {
        $this->siteName = $siteName;

        return $this;
    }

    /**
     * @param string $siteMail
     *
     * @return Drush
     */
    public function siteMail($siteMail)
    {
        $this->siteMail = $siteMail;

        return $this;
    }

    /**
     * @param string $siteLocale
     *
     * @return Drush
     */
    public function siteLocale($siteLocale)
    {
        $this->siteLocale = $siteLocale;

        return $this;
    }

    /**
     * @param string $siteProfile
     *
     * @return Drush
     */
    public function siteProfile($siteProfile)
    {
        $this->siteProfile = $siteProfile;

        return $this;
    }

    /**
     * @param string $accountMail
     *
     * @return Drush
     */
    public function accountMail($accountMail)
    {
        $this->accountMail = $accountMail;

        return $this;
    }

    /**
     * @param string $accountName
     *
     * @return Drush
     */
    public function accountName($accountName)
    {
        $this->accountName = $accountName;

        return $this;
    }

    /**
     * @param string $accountPassword
     *
     * @return Drush
     */
    public function accountPassword($accountPassword)
    {
        $this->accountPassword = $accountPassword;

        return $this;
    }

    /**
     * @param string $databaseType
     *
     * @return Drush
     */
    public function databaseType($databaseType)
    {
        $this->databaseType = $databaseType;

        return $this;
    }

    /**
     * @param string $databaseUser
     *
     * @return Drush
     */
    public function databaseUser($databaseUser)
    {
        $this->databaseUser = $databaseUser;

        return $this;
    }

    /**
     * @param string $databasePassword
     *
     * @return Drush
     */
    public function databasePassword($databasePassword)
    {
        $this->databasePassword = $databasePassword;

        return $this;
    }

    /**
     * @param string $databaseHost
     *
     * @return Drush
     */
    public function databaseHost($databaseHost)
    {
        $this->databaseHost = $databaseHost;

        return $this;
    }

    /**
     * @param string $databasePort
     *
     * @return Drush
     */
    public function databasePort($databasePort)
    {
        $this->databasePort = $databasePort;

        return $this;
    }

    /**
     * @param string $databaseName
     *
     * @return Drush
     */
    public function databaseName($databaseName)
    {
        $this->databaseName = $databaseName;

        return $this;
    }

    /**
     * @param string $sitesSubdir
     *
     * @return Drush
     */
    public function sitesSubdir($sitesSubdir)
    {
        $this->sitesSubdir = $sitesSubdir;

        return $this;
    }
}
