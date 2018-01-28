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
        $type = $package->getType();
        if ($this->supports($type)) {
            return "vendor/test/" . $package->getPrettyName();
        }
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
