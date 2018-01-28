<?php
namespace EC\OpenEuropa\TaskRunner\Composer;

use Composer\IO\IOInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

class Installer extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $prefix = substr($package->getPrettyName(), 0, 23);
        if ('phpdocumentor/template-' !== $prefix) {
            throw new \InvalidArgumentException(
                'Unable to install template, phpdocumentor templates '.'should always start their package name with '.'"phpdocumentor/template-"'
            );
        }

        return 'data/templates/'.substr($package->getPrettyName(), 23);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        $drupalTypes = array(
          "drupal-core",
          "drupal-module",
          "drupal-theme",
          "drupal-library",
          "drupal-profile",
          "drupal-drush",
          "drupal-custom-module",
          "drupal-custom-theme",
        );
        return in_array($packageType, $drupalTypes);
    }
}
