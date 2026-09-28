<?php

declare(strict_types=1);

namespace OpenEuropa\TaskRunner\Tasks\Drush;

use League\Uri\UriString;
use Robo\Task\Base\Exec;

/**
 * Tasks to interact with Drush.
 *
 * @SuppressWarnings(PHPMD)
 */
class Drush extends Exec
{
    protected $root = '';
    protected $locale = '';
    protected $siteName = '';
    protected $siteMail = '';
    protected $siteLocale = '';
    protected $siteUpdate = false;
    protected $siteProfile = '';
    protected $accountMail = '';
    protected $accountName = '';
    protected $accountPassword = '';
    protected $databaseScheme = '';
    protected $databaseHost = '';
    protected $databasePort = '';
    protected $databaseUser = '';
    protected $databasePassword = '';
    protected $databaseName = '';
    protected $sitesSubdir = '';
    protected $existingConfig = false;
    protected $generateDbUrl = true;

    /**
     * Build Drush site install command.
     *
     * @return $this
     */
    public function siteInstall()
    {
        $this->option('-y')
            ->rawArg("--root=$(pwd)/" . $this->root)
            ->options([
                'site-name' => $this->siteName,
                'site-mail' => $this->siteMail,
                'locale' => $this->locale,
                'account-mail' => $this->accountMail,
                'account-name' => $this->accountName,
                'account-pass' => $this->accountPassword,
                'sites-subdir' => $this->sitesSubdir,
            ], '=');

        if ($this->generateDbUrl) {
            $hasUser = !empty($this->databaseUser);
            $dbArray = [
                'scheme' => $this->databaseScheme,
                'user' => $hasUser ? $this->databaseUser : null,
                'pass' => $hasUser ? $this->databasePassword : null,
                'host' => $this->databaseHost,
                'port' => (int) $this->databasePort ?: null,
                'path' => '/' . ltrim((string) $this->databaseName, '/'),
            ];

            // Drop empty components, and the password when there is no user.
            // Use callback to avoid dropping strings like "0".
            $dbArray = array_filter(
                $dbArray,
                static fn ($value): bool => (string) $value !== ''
            );

            $dbUrl = UriString::build($dbArray);

            $this->option('db-url', $dbUrl, '=');
        }

        if (!empty($this->existingConfig)) {
            $this->option('existing-config');
        }

        # Add arguments to Drush command.
        $this->arg('site-install');
        $this->arg($this->siteProfile);

        if ($this->siteUpdate === false) {
            $this->arg('install_configure_form.enable_update_status_module=NULL');
        }

        return $this;
    }

    /**
     * @param string $root
     *
     * @return $this
     */
    public function root($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function locale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @param string $siteName
     *
     * @return $this
     */
    public function siteName($siteName)
    {
        $this->siteName = $siteName;

        return $this;
    }

    /**
     * @param string $siteMail
     *
     * @return $this
     */
    public function siteMail($siteMail)
    {
        $this->siteMail = $siteMail;

        return $this;
    }

    /**
     * @param string $siteLocale
     *
     * @return $this
     */
    public function siteLocale($siteLocale)
    {
        $this->siteLocale = $siteLocale;

        return $this;
    }

    /**
     * @param string $siteUpdate
     *
     * @return $this
     */
    public function siteUpdate($siteUpdate)
    {
        $this->siteUpdate = $siteUpdate;

        return $this;
    }

    /**
     * @param string $siteProfile
     *
     * @return $this
     */
    public function siteProfile($siteProfile)
    {
        $this->siteProfile = $siteProfile;

        return $this;
    }

    /**
     * @param string $accountMail
     *
     * @return $this
     */
    public function accountMail($accountMail)
    {
        $this->accountMail = $accountMail;

        return $this;
    }

    /**
     * @param string $accountName
     *
     * @return $this
     */
    public function accountName($accountName)
    {
        $this->accountName = $accountName;

        return $this;
    }

    /**
     * @param string $accountPassword
     *
     * @return $this
     */
    public function accountPassword($accountPassword)
    {
        $this->accountPassword = $accountPassword;

        return $this;
    }

    /**
     * @param string $databaseScheme
     *
     * @return $this
     */
    public function databaseScheme($databaseScheme)
    {
        $this->databaseScheme = $databaseScheme;

        return $this;
    }

    /**
     * @param string $databaseUser
     *
     * @return $this
     */
    public function databaseUser($databaseUser)
    {
        $this->databaseUser = $databaseUser;

        return $this;
    }

    /**
     * @param string $databasePassword
     *
     * @return $this
     */
    public function databasePassword($databasePassword)
    {
        $this->databasePassword = $databasePassword;

        return $this;
    }

    /**
     * @param string $databaseHost
     *
     * @return $this
     */
    public function databaseHost($databaseHost)
    {
        $this->databaseHost = $databaseHost;

        return $this;
    }

    /**
     * @param string $databasePort
     *
     * @return $this
     */
    public function databasePort($databasePort)
    {
        $this->databasePort = $databasePort;

        return $this;
    }

    /**
     * @param string $databaseName
     *
     * @return $this
     */
    public function databaseName($databaseName)
    {
        $this->databaseName = $databaseName;

        return $this;
    }

    /**
     * @param string $sitesSubdir
     *
     * @return $this
     */
    public function sitesSubdir($sitesSubdir)
    {
        $this->sitesSubdir = $sitesSubdir;

        return $this;
    }

    /**
     * @param bool $existingConfig
     *
     * @return Drush
     */
    public function setExistingConfig($existingConfig)
    {
        $this->existingConfig = $existingConfig;

        return $this;
    }

    /**
     * @param bool $generateDbUrl
     *
     * @return Drush
     */
    public function setGenerateDbUrl($generateDbUrl)
    {
        $this->generateDbUrl = $generateDbUrl;

        return $this;
    }
}
